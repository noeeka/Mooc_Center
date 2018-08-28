<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/8/8
 * Time: 11:25
 */

namespace app\v1\controller;

use app\v1\model\Answer;
use app\v1\model\Collect;
use app\v1\model\MoocUser;
use app\v1\model\Baoming;
use app\v1\model\Chapter;
use app\v1\model\Question;
use app\v1\model\Section;
use app\v1\model\Course;
use app\v1\model\SectionNote;

class My extends Base {
	/**
	 * 个人中心获取个人信息
	 * @param user_token 用户token
	 */
	public function index()
	{
		$_GET['user_token'] = 'b616164e6ce937e8debb6345783a9746ebcd1e5c';
		$_GET['timestamp']  = strval(time());
		$salt               = MoocUser::where('user_token', $_GET['user_token'])->value('salt');
		$_GET['sign']       = encrypt_key(['v1/my/index', $_GET['timestamp'], $_GET['user_token'], $salt], '');
		$user_token         = input('param.user_token', '', 'trim');

		//令牌校验
		$tokenRes = checkUserToken($user_token);
		if ($tokenRes !== TRUE)
		{
			return $tokenRes;
		}

		//获取个人信息
		$user               = (new MoocUser())
			->alias('u')
			->join('__FOLLOW__ f', 'f.user_id = u.id', 'left')
			->where('user_token', $user_token)
			->field('u.id,u.nick_name,u.sex,u.avatar,u.teacher_title,u.area,u.email,u.mobile,u.profile,u.type,count(f.follow_id) as fans_num')
			->group('f.user_id')
			->find();
		$user['follow_num'] = (new \app\v1\model\Follow())->where(['follow_id' => $user['id']])->count(1);

		return $this->ok($user, 12222, '获取用户个人信息成功');
	}

	/**
	 * 个人中心修改个人信息   /v1/user/edit
	 */
	public function edit()
	{
		$Db            = new \think\Db;
		$nick_name = input('param.nick_name', '', 'trim');
		$sex = input('param.sex', 0, 'trim');
		$area = input('param.area', "101010100", 'trim');
		$email = input('param.email', "", 'trim');
		$mobile = input('param.mobile', "", 'trim');
		$avatar = input('param.avatar', "", 'trim');
		$ret = $Db::query("UPDATE mooc_user SET nick_name='{$nick_name}',sex={$sex},area='{$area}',email='{$email}',mobile='{$mobile}',avatar='{$avatar}' WHERE id=".input('param.id', 0, 'trim'));
		if ($ret)
		{
			return $this->ok('', 21101, '成功', 1);
		}

	}


	/**
	 * 我的课程-课程表
	 * @param user_token 用户token
	 */
	public function myCourse()
	{
		$_GET['user_token'] = 'b616164e6ce937e8debb6345783a9746ebcd1e5c';
		$_GET['timestamp']  = time();
		$salt               = MoocUser::where('user_token', $_GET['user_token'])->value('salt');
		$_GET['sign']       = encrypt_key(['v1/my/mycourse', $_GET['timestamp'], $_GET['user_token'], $salt], '');

		$user_token = input('param.user_token', '' . 'trim');
		$list_order = input('param.order', -1, 'intval');  //获取最新学习课程需要传参

		//令牌校验
		$tokenRes = checkUserToken($user_token);
		if ($tokenRes !== TRUE)
		{
			return $tokenRes;
		}

		if ($list_order != -1)
		{
			$order = ['sd.update_time' => 'desc'];
		}
		else
		{
			$order = ['b.create_time' => 'desc'];
		}

		//获取学生的课程和进度数据
		$user        = (new MoocUser())->where(['user_token' => $user_token])->find();
		$user_course = (new Baoming())
			->alias('b')
			->join('__COURSE__ c', 'c.id=b.course_id')
			->join('__SCHEDULE__ sd', 'sd.user_id=b.user_id and sd.course_id = b.course_id', 'left')
			->join('__SECTION__ s', 's.id=sd.section_id', 'left')
			->join('__CHAPTER__ ct', 'ct.id=s.chapter_id', 'left')
			->where(['b.user_id' => $user['id']])
			->order($order)
			->field('c.*,b.user_id,sd.section_id,sd.current_time,ct.id as chapter_id')
			->select();
		//print_r((new Baoming())->getLastSql());
		if ($user_course != NULL)
		{
			$user_course = \collection($user_course)->toArray();
			//数据整理
			foreach ($user_course as $k => $item)
			{
				$user_course[$k] = $this->_handleData($item);
			}
		}

		return $this->ok($user_course, 123456, '获取我的课程成功');

	}

	/**
	 * 我的课程-课程表
	 * 数据整理
	 */
	private function _handleData($data)
	{
		$chapterModel = new Chapter();
		$sectionModel = new Section();

		//获取此节对应的 课程进行时长 课程id  章id
//        $studyInfo = $sectionModel
//            ->alias('s')
//            ->join('__CHAPTER__ c','c.id = s.chapter_id')
//            ->join('__SCHEDULE__ sd','sd.section_id=s.id')
//            ->where(['s.id'=>$data['section_id'],'sd.user_id'=>$data['user_id']])
//            ->field('sd.current_time,c.id,c.course_id')
//            ->find();

		$course_id        = $data['id'];
		$chapter_id       = $data['chapter_id'];
		$sec_current_time = $data['current_time'];

		//获取此课程此节对应章上面所有的章ids
		$chapter_ids = $chapterModel->where(['course_id' => $course_id, 'id' => ['<', $chapter_id]])->column('id');
		if ($chapter_ids)
		{  //此章不是第一章
			//获取此章上所有章的总时长
			$chapter_time = $sectionModel->where(['charpter_id' => ['in', $chapter_ids]])->value('sum(video_time)');
		}
		else
		{  //是第一章
			//此章节之上时常
			$chapter_time = 0;
		}

		//获取此章节的总时长
		//获取此节之前的时长
		$section_time = $sectionModel->where(['chapter_id' => $chapter_id, 'id' => ['<', $data['section_id']]])->value('sum(video_time)');

		//已学习总时长
		$stu_total_time = $chapter_time + $section_time + $sec_current_time;

		//学习进度
		$speed = $stu_total_time / $data['total_time'];

		$data['stu_total_time'] = $stu_total_time;
		$data['speed']          = $speed;

		return $data;
	}


	/**
	 * 我的课程-笔记
	 * @param user_token 用户token
	 */
	public function myNote()
	{
//		$_GET['user_token'] = '5da0f5fbadc013ca572d8558d237d1a829dab11e';
//		$_GET['timestamp']  = time();
//		$salt               = MoocUser::where('user_token', $_GET['user_token'])->value('salt');
//		$_GET['sign']       = encrypt_key(['v1/my/myNote', $_GET['timestamp'], $_GET['user_token'], $salt], '');
//
//		$user_token = input('param.user_token', '' . 'trim');
//		$list_order = input('param.order', -1, 'intval');  //获取最新学习课程需要传参
//
//		//令牌校验
//		$tokenRes = checkUserToken($user_token);
//		if ($tokenRes !== TRUE)
//		{
//			return $tokenRes;
//		}

		$where = [];
		if ( ! empty($search))
		{
			$where['content'] = ['like', "%$search%"];
		}

		$user_token="4800970a86649ca1ce6215864cc3df8cec0319a9";
		//获取个人笔记数据
		$userModel            = new MoocUser();
		$noteModel            = new SectionNote();
		$user                 = $userModel->where(['user_token' => $user_token])->find();

		$where['n.center_id'] = $user['center_id'];
		$where['n.user_id']   = $user['id'];
		$noteList             = $noteModel->alias('n')
			->join('__MOOC_USER__ mu', 'mu.id=n.user_id')
			->where($where)
			->field('n.*,mu.nick_name,mu.avatar')
			->select();

		foreach ($noteList as $key => $item)
		{
			if ($item['type'] == 2)
			{
				$noteList[$key]['collected_user_nickname'] = (new MoocUser())->where(['id' => $item['collect_from']])->value('nick_name');
			}
		}

		return $this->ok($noteList, 20111, '获取笔记成功');

	}

	/**
	 * 我的课程-提问
	 * @param user_token 用户token
	 */
	public function myQuestion()
	{
//		$_GET['user_token'] = '4800970a86649ca1ce6215864cc3df8cec0319a9';
//		$_GET['timestamp']  = time();
//		$salt               = 'Fkg7e';
//		$_GET['sign']       = encrypt_key(['v1/my/myquestion', $_GET['timestamp'], $_GET['user_token'], $salt], '');
//
//		$user_token = input('param.user_token', '', 'trim');
//
//		$tokenRes = checkUserToken($user_token);
//		if ($tokenRes !== TRUE)
//		{
//			return $tokenRes;
//		}

		//获取个人提问数据
		$user_token="4800970a86649ca1ce6215864cc3df8cec0319a9";
		$userModel            = new MoocUser();
		$questionModel        = new Question();
		$user                 = $userModel->where(['user_token' => $user_token])->find();
		$where['q.center_id'] = $user['center_id'];
		$where['q.user_id']   = $user['id'];
		$questionList         = $questionModel->alias('q')
			->join('__SECTION__ s', 's.id=q.section_id')
			->join('__CHAPTER__ c', 'c.id=s.chapter_id')
			->where($where)
			->field('q.content,q.create_time,c.chapter_title,s.section_title')
			->select();


		return $this->ok($questionList, 20111, '获取提问成功');

	}

	/**
	 * 我的课程-我的回答
	 * @param user_token 用户token
	 */
	public function myAnswer()
	{
//		$_GET['user_token'] = '4800970a86649ca1ce6215864cc3df8cec0319a9';
//		$_GET['timestamp']  = time();
//		$salt               = 'Fkg7e';
//		$_GET['sign']       = encrypt_key(['v1/my/myanswer', $_GET['timestamp'], $_GET['user_token'], $salt], '');
//
//		$user_token = input('param.user_token', '', 'trim');
//
//		$tokenRes = checkUserToken($user_token);
//		if ($tokenRes !== TRUE)
//		{
//			return $tokenRes;
//		}
		$user_token="4800970a86649ca1ce6215864cc3df8cec0319a9";
		//获取个人提问数据
		$userModel          = new MoocUser();
		$answerModel        = new Answer();
		$user               = $userModel->where(['user_token' => $user_token])->find();
		$where['a.user_id'] = $user['id'];
		$answerList         = $answerModel->alias('a')
			->join('__QUESTION__ q', 'q.id=a.question_id')
			->join('__SECTION__ s', 's.id=q.section_id')
			->join('__CHAPTER__ c', 'c.id=s.chapter_id')
			->where($where)
			->field('a.content,a.create_time,q.content as question_content,q.id as qustion_id,s.section_title,c.chapter_title')
			->select();


		return $this->ok($answerList, 20111, '获取回答成功');

	}

	/**
	 * 我的收藏
	 * @param user_token 用户token
	 */
	public function myCollect()
	{
//		$_GET['user_token'] = '4800970a86649ca1ce6215864cc3df8cec0319a9';
//		$_GET['timestamp']  = time();
//		$salt               = 'Fkg7e';
//		$_GET['sign']       = encrypt_key(['v1/my/mycollect', $_GET['timestamp'], $_GET['user_token'], $salt], '');
//
//		$user_token = input('param.user_token', '', 'trim');
//
//		//令牌校验
//		$tokenRes = checkUserToken($user_token);
//		if ($tokenRes !== TRUE)
//		{
//			return $tokenRes;
//		}
		$user_token="4800970a86649ca1ce6215864cc3df8cec0319a9";
		//获取个人提问数据
		$userModel             = new MoocUser();
		$collectModel          = new Collect();
		$user                  = $userModel->where(['user_token' => $user_token])->find();
		$where['cl.user_id']   = $user['id'];
		$where['cl.center_id'] = $user['center_id'];
		$collectList           = $collectModel->alias('cl')
			->join('__COURSE__ c', 'cl.course_id=c.id')
			->join('__BAOMING__ b', 'b.user_id=cl.user_id and b.course_id=cl.course_id', 'left')
			->where($where)
			->field('cl.course_id,c.course_title,c.cover_img,c.start_time,c.end_time,count(b.id) as yibaoming')
			->group('cl.course_id')
			->select();


		return $this->ok($collectList, 20111, '获取收藏成功');

	}

	/**老师
	 * 我的课堂-课程管理
	 * @param user_token 用户token
	 */
	public function myClass()
	{
//		$_GET['user_token'] = '83d68fd896ce59784f3fe70174c286be2904cca2';
//		$_GET['timestamp']  = time();
//		$salt               = 'ovOqW';
//		$_GET['sign']       = encrypt_key(['v1/my/myclass', $_GET['timestamp'], $_GET['user_token'], $salt], '');
//
//		$user_token = input('param.user_token', '', 'trim');
		$user_token="4800970a86649ca1ce6215864cc3df8cec0319a9";
		$filter     = input('param.filter', 0, 'intval');  //1 最近三天    2最近一周   3最近一个月
		$order      = input('param.order', 0, 'intval');

		//令牌校验
//		$tokenRes = checkUserToken($user_token);
//		if ($tokenRes !== TRUE)
//		{
//			return $tokenRes;
//		}

		$where = [];
		if ( ! empty($filter))
		{
			if ($filter == 1)
			{
				//最近三天
				$time_array = [time() - 259200, time() + 259200];
			}
			else if ($filter == 2)
			{
				//最近一周
				$time_array = [time() - 604800, time() + 604800];
			}
			else
			{
				//最近一个月
				$time_array = [time() - 2592000, time() + 2592000];
			}
			$where['c.create_time'] = ['between', $time_array];
		}

		if ( ! empty($order))
		{
			$order = ['c.create_time' => 'desc'];
		}
		else
		{
			$order = ['c.id' => 'asc'];
		}

		$courseModel          = new Course();
		$user                 = (new MoocUser())->where(['user_token' => $user_token])->find();
		$where['cr.other_id'] = $user['id'];
		$where['cr.type']     = 3;
		$classes              = $courseModel
			->alias('c')
			->join('__COURSE_RELA__ cr', 'cr.course_id = c.id')
			->join('__BAOMING__ b', 'b.course_id = c.id', 'left')
			->where($where)
			->order($order)
			->field('c.*,count(b.id) as baoming_num')
			->group('c.id')
			->select();

		return $this->ok($classes, 20111, '获取课程成功');
	}

}