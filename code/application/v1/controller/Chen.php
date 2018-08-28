<?php

namespace app\v1\controller;

use app\v1\model\Answer;
use app\v1\model\CenterCourse;
use app\v1\model\Comment;
use app\v1\model\MoocUser;
use app\v1\model\Question;
use app\v1\model\SectionNote;
use app\v1\model\Baoming;
use app\v1\model\Course;
use app\v1\model\SectionNoteReply;
use app\v1\model\Terminal;
use \app\v1\model\Statistics as tongji;

class Chen extends Base {
	/*
	*获取活跃度
	*
	*/
	public function getActivate()
	{
		//echo phpinfo();die;
		set_time_limit(0);
		$Db            = new \think\Db;
		$temp          = array();
		$temp          = getDateTimeArray(input('param.date_range', 1));
		$logins        = array();
		$comments      = array();
		$courses       = array();
		$sections      = array();
		$collects      = array();
		$baomings      = array();
		$date_interval = array();

		//获取X轴坐标服务
		if (input('param.date_range', 1) == 1)
		{
			$now   = time();
			$start = strtotime('-1 days');
			for ($i = $start; $i < $now; $i += 7200)  //3600秒是按每小时生成一条，如果按天或者月份换算成秒即可
			{
				$date            = date('Y-m-d H', $i);
				$date_interval[] = date("m-d H:i", strtotime($date . ":00"));
			}
		}
		elseif (input('param.date_range', 1) == 2)
		{
			$now   = time();
			$start = strtotime('-7 days');
			for ($i = $start; $i <= $now; $i += 3600 * 24)
			{
				$date            = date('Y-m-d H', $i);
				$date_interval[] = date("Y-m-d", strtotime($date . ":00"));
			}
		}
		elseif (input('param.date_range', 1) == 3)
		{
			$now   = time();
			$start = strtotime('-30 days');
			for ($i = $start; $i <= $now; $i += 3600 * 24 * 3)
			{
				$date            = date('Y-m-d H', $i);
				$date_interval[] = date("Y-m-d", strtotime($date . ":00"));
			}
		}
		elseif (input('param.date_range', 1) == 4)
		{
			$now   = time();
			$start = strtotime('-90 days');
			for ($i = $start; $i <= $now; $i += 3600 * 24 * 9)
			{
				$date            = date('Y-m-d H', $i);
				$date_interval[] = date("Y-m-d", strtotime($date . ":00"));
			}

		}
		elseif (input('param.date_range', 1) == "select")
		{

		}
		foreach ($temp as $k => $v)
		{
			$start_time = $v;
			$end_time   = next($temp);

			if ( ! empty($end_time))
			{
				$login_sets = $Db::query("select count(*) as cnt from mooc_user where access_time>{$start_time} and access_time<{$end_time}");

				$comment_sets = $Db::query("select count(*) as cnt from comment where create_time>{$start_time} and create_time<{$end_time}");

				$course_sets = $Db::query("select * from center_course where create_time>{$start_time} and create_time<{$end_time}");

				//获取讨论数量
				$section_note = $Db::query("select count(*) as cnt from section_note where create_time>{$start_time} and create_time<{$end_time}");

				$answer   = $Db::query("select count(*) as cnt from answer where create_time>{$start_time} and create_time<{$end_time}");
				$question = $Db::query("select count(*) as cnt from question where create_time>{$start_time} and create_time<{$end_time}");

				$course_sets = $Db::query("select count(*) as cnt from center_course where create_time>{$start_time} and create_time<{$end_time}");

				$collect_sets = $Db::query("select count(*) as cnt from collect where create_time>{$start_time} and create_time<{$end_time}");

				$baoming_sets = $Db::query("select count(*) as cnt from baoming where create_time>{$start_time} and create_time<{$end_time}");

				array_push($logins, $login_sets[0]['cnt']);
				array_push($comments, $comment_sets[0]['cnt']);
				array_push($courses, $course_sets[0]['cnt']);
				array_push($sections, ($section_note[0]['cnt'] + $answer[0]['cnt'] + $question[0]['cnt']));
				array_push($collects, $collect_sets[0]['cnt']);
				array_push($baomings, $baoming_sets[0]['cnt']);
				//array_push($date_interval, $k);
			}


		}
		array(
			array("name" => "登录", "type" => 'bar', "data" => $logins),
			array("name" => "评价", "type" => 'bar', "data" => $comments),
			array("name" => "课程访问", "type" => 'bar', "data" => $courses),
			array("name" => "讨论", "type" => 'bar', "data" => $sections),
			array("name" => "课程新增", "type" => 'bar', "data" => $courses),
			array("name" => "课程收藏", "type" => 'bar', "data" => $collects),
			array("name" => "加入课程", "type" => 'bar', "data" => $baomings),
		);
		return $this->ok(['seriers' => array(
			array("name" => "登录", "type" => 'bar', "data" => $logins),
			array("name" => "评价", "type" => 'bar', "data" => $comments),
			array("name" => "课程访问", "type" => 'bar', "data" => $courses),
			array("name" => "讨论", "type" => 'bar', "data" => $sections),
			array("name" => "课程新增", "type" => 'bar', "data" => $courses),
			array("name" => "课程收藏", "type" => 'bar', "data" => $collects),
			array("name" => "加入课程", "type" => 'bar', "data" => $baomings),
		), 'axis' => $date_interval], 12011, '获取活跃度成功');


	}


	public function getActivateByRange()
	{
		set_time_limit(0);
		$Db   = new \think\Db;
		$temp = array();


		//------------------------------

		//获取X轴坐标服务
		if (input('param.date_range', 1) == 1)
		{
			$now   = time();
			$start = strtotime('-1 days');
			for ($i = $start; $i < $now; $i += 7200)  //3600秒是按每小时生成一条，如果按天或者月份换算成秒即可
			{
				$date            = date('Y-m-d H', $i);
				$date_interval[] = date("m-d H:i", strtotime($date . ":00"));
			}
		}
		elseif (input('param.date_range', 1) == 2)
		{
			$now   = time();
			$start = strtotime('-7 days');
			for ($i = $start; $i <= $now; $i += 3600 * 24)
			{
				$date            = date('Y-m-d H', $i);
				$date_interval[] = date("Y-m-d", strtotime($date . ":00"));
			}
		}
		elseif (input('param.date_range', 1) == 3)
		{
			$now   = time();
			$start = strtotime('-30 days');
			for ($i = $start; $i <= $now; $i += 3600 * 24 * 3)
			{
				$date            = date('Y-m-d H', $i);
				$date_interval[] = date("Y-m-d", strtotime($date . ":00"));
			}
		}
		elseif (input('param.date_range', 1) == 4)
		{
			$now   = time();
			$start = strtotime('-90 days');
			for ($i = $start; $i <= $now; $i += 3600 * 24 * 9)
			{
				$date            = date('Y-m-d H', $i);
				$date_interval[] = date("Y-m-d", strtotime($date . ":00"));
			}

		}


		//-------------------------------


		$startTime = strtotime(input('param.start', 1) . " 00:00:00");
		$endTime   = strtotime(input('param.end', 1) . " 23:59:59");
		if (($endTime - $startTime) < 24 * 60 * 60)
		{
			$temp  = getDateTimeArray(1);
			$now   = time();
			$start = strtotime('-1 days');
			for ($i = $start; $i < $now; $i += 7200)  //3600秒是按每小时生成一条，如果按天或者月份换算成秒即可
			{
				$date            = date('Y-m-d H', $i);
				$date_interval[] = date("m-d H:i", strtotime($date . ":00"));
			}
		}
		elseif (($endTime - $startTime) < 7 * 24 * 60 * 60 && ($endTime - $startTime) > 24 * 60 * 60)
		{
			$temp  = getDateTimeArray(2);
			$now   = time();
			$start = strtotime('-7 days');
			for ($i = $start; $i <= $now; $i += 3600 * 24)
			{
				$date            = date('Y-m-d H', $i);
				$date_interval[] = date("Y-m-d", strtotime($date . ":00"));
			}
		}
		elseif (($endTime - $startTime) < 30 * 24 * 60 * 60 && ($endTime - $startTime) > 7 * 24 * 60 * 60)
		{
			$temp  = getDateTimeArray(3);
			$now   = time();
			$start = strtotime('-30 days');
			for ($i = $start; $i <= $now; $i += 3600 * 24 * 3)
			{
				$date            = date('Y-m-d H', $i);
				$date_interval[] = date("Y-m-d", strtotime($date . ":00"));
			}
		}
		elseif (($endTime - $startTime) < 90 * 24 * 60 * 60 && ($endTime - $startTime) > 30 * 24 * 60 * 60)
		{
			$temp  = getDateTimeArray(4);
			$now   = time();
			$start = strtotime('-90 days');
			for ($i = $start; $i <= $now; $i += 3600 * 24 * 9)
			{
				$date            = date('Y-m-d H', $i);
				$date_interval[] = date("Y-m-d", strtotime($date . ":00"));
			}
		}
		else
		{
			$now   = time();
			$start = strtotime('-90 days');
			for ($i = $start; $i <= $now; $i += 3600 * 24 * 30)  //3600秒是按每小时生成一条，如果按天或者月份换算成秒即可
			{
				$date            = date('Y-m-d H', $i);
				$date_interval[] = date("Y-m", strtotime($date . ":00"));
				$result[]        = strtotime($date . ":00");
			}

		}

		$logins        = array();
		$comments      = array();
		$courses       = array();
		$sections      = array();
		$collects      = array();
		$baomings      = array();
		$date_interval = array();
		foreach ($temp as $k => $v)
		{
			$start_time = $v;
			$end_time   = next($temp);

			if ( ! empty($end_time))
			{
				$login_sets = $Db::query("select count(*) as cnt from mooc_user where access_time>{$start_time} and access_time<{$end_time}");

				$comment_sets = $Db::query("select count(*) as cnt from comment where create_time>{$start_time} and create_time<{$end_time}");

				$course_sets = $Db::query("select * from center_course where create_time>{$start_time} and create_time<{$end_time}");

				//获取讨论数量
				$section_note = $Db::query("select count(*) as cnt from section_note where create_time>{$start_time} and create_time<{$end_time}");

				$answer   = $Db::query("select count(*) as cnt from answer where create_time>{$start_time} and create_time<{$end_time}");
				$question = $Db::query("select count(*) as cnt from question where create_time>{$start_time} and create_time<{$end_time}");

				$course_sets = $Db::query("select count(*) as cnt from center_course where create_time>{$start_time} and create_time<{$end_time}");

				$collect_sets = $Db::query("select count(*) as cnt from collect where create_time>{$start_time} and create_time<{$end_time}");

				$baoming_sets = $Db::query("select count(*) as cnt from baoming where create_time>{$start_time} and create_time<{$end_time}");

				array_push($logins, $login_sets[0]['cnt']);
				array_push($comments, $comment_sets[0]['cnt']);
				array_push($courses, $course_sets[0]['cnt']);
				array_push($sections, ($section_note[0]['cnt'] + $answer[0]['cnt'] + $question[0]['cnt']));
				array_push($collects, $collect_sets[0]['cnt']);
				array_push($baomings, $baoming_sets[0]['cnt']);
				array_push($date_interval, $k);
			}


		}
		array(
			array("name" => "登录", "type" => 'bar', "data" => $logins),
			array("name" => "评价", "type" => 'bar', "data" => $comments),
			array("name" => "课程访问", "type" => 'bar', "data" => $courses),
			array("name" => "讨论", "type" => 'bar', "data" => $sections),
			array("name" => "课程新增", "type" => 'bar', "data" => $courses),
			array("name" => "课程收藏", "type" => 'bar', "data" => $collects),
			array("name" => "加入课程", "type" => 'bar', "data" => $baomings),
		);
		return $this->ok(['seriers' => array(
			array("name" => "登录", "type" => 'bar', "data" => $logins),
			array("name" => "评价", "type" => 'bar', "data" => $comments),
			array("name" => "课程访问", "type" => 'bar', "data" => $courses),
			array("name" => "讨论", "type" => 'bar', "data" => $sections),
			array("name" => "课程新增", "type" => 'bar', "data" => $courses),
			array("name" => "课程收藏", "type" => 'bar', "data" => $collects),
			array("name" => "加入课程", "type" => 'bar', "data" => $baomings),
		), 'axis' => $date_interval], 12011, '获取活跃度成功');


	}

	public function getAnalysisResult()
	{
		set_time_limit(0);
		$Db          = new \think\Db;
		$course_sets = array();
		$data_sets   = array();
		foreach ($Db::query("select * from `course`") as $k => $v)
		{
			array_push($course_sets, $v['course_title']);
		}
		if (input('param.type', 1) == 1)
		{
			foreach ($Db::query("select * from `course`") as $k => $v)
			{
				array_push($data_sets, $Db::query("select count(*) as cnt from `baoming` where `course_id`={$v['id']}")[0]['cnt']);
			}
		}
		elseif (input('param.type', 1) == 2)
		{
			foreach ($Db::query("select * from `course`") as $k => $v)
			{
				array_push($data_sets, $Db::query("select count(*) as cnt from `comment` where `course_id`={$v['id']}")[0]['cnt']);
			}
		}
		elseif (input('param.type', 1) == 3)
		{
			foreach ($Db::query("select * from `course`") as $k => $v)
			{
				array_push($data_sets, $Db::query("select count(*) as cnt from section_note where `course_id`={$v['id']}")[0]['cnt']);
			}
		}
		elseif (input('param.type', 1) == 6)
		{
			foreach ($Db::query("select * from `course`") as $k => $v)
			{
				array_push($data_sets, $Db::query("select count(*) as cnt from section_note where `course_id`={$v['id']}")[0]['cnt']);
			}
		}

		return $this->ok(['xaxis' => $data_sets, 'yaxis' => $course_sets], 12011, '获取活跃度成功');
	}

	//根据老师统计课程
	public function getCourseByteacher()
	{
		set_time_limit(0);
		$Db          = new \think\Db;
		$course_sets = array();
		$data_sets   = array();

		//接受传递参数

		$where_center_id = empty(input('param.center_id')) ? " " : " and mooc_user.center_id=" . input('param.center_id', 1);
		$where_nick_name = empty(input('param.nick_name')) ? " " : " and mooc_user.nick_name like '%" . trim(input('param.nick_name', 1)) . "%'";
		$page            = empty(input('param.page')) ? 1 : input('param.page', 1);
		$len             = empty(input('param.len')) ? 5 : input('param.len', 1);
		$start           = ($page - 1) * $len;
		//exit("select  mooc_user.* from mooc_user where mooc_user.type=2".$where_nick_name.$where_center_id." limit {$start},{$len}");
		foreach ($Db::query("select  mooc_user.* from mooc_user where mooc_user.type=2" . $where_nick_name . $where_center_id . " limit {$start},{$len}") as $key_teacher => $value_teacher)
		{
			$data_sets[$key_teacher]['id']        = $value_teacher['id'];
			$data_sets[$key_teacher]['nick_name'] = $value_teacher['nick_name'];
			//获取文化馆对应名称
			$sql_count_venue                  = "select center_name from mooc_center where id=" . $value_teacher['center_id'];
			$data_sets[$key_teacher]['venue'] = $Db::query($sql_count_venue)[0]['center_name'];

			//获取关注数
			$sql_count_follows                  = "select count(*) as cnt from follow where user_id=" . $value_teacher['id'];
			$data_sets[$key_teacher]['follows'] = $Db::query($sql_count_follows)[0]['cnt'];

			//获取课程数
			$sql_count_courses                  = "select count(distinct(course_id)) as cnt from course_rela where type=3 and other_id=" . $value_teacher['id'];
			$data_sets[$key_teacher]['courses'] = $Db::query($sql_count_courses)[0]['cnt'];

			//获取章节数
			//先遍历课程集合
			$chapter_num = 0;
			foreach ($Db::query("select course_id from course_rela where type=3 and other_id=" . $value_teacher['id']) as $key_course => $value_courses)
			{
				//便利每个课程下有多少章节
				$chapter_num = $chapter_num + $Db::query("select count(*) as cnt from chapter where course_id=" . $value_courses['course_id'])[0]['cnt'];
			}

			$data_sets[$key_teacher]['chapters'] = $chapter_num;

			//获取学生数
			$student_num = 0;
			if ( ! empty($Db::query("select * from course_rela where type=3 and other_id=" . $value_teacher['id'])))
			{
				foreach ($Db::query("select * from course_rela where type=3 and other_id=" . $value_teacher['id']) as $k => $v)
				{
					//再获取该门课下有多少学生报名
					$student_num = $student_num + $Db::query("select count(*) as cnt from baoming where course_id=" . $v['course_id'])[0]['cnt'];
				}
			}
			$data_sets[$key_teacher]['students'] = $student_num;

			//获取问答数
			$qa_num                               = 0;
			$question_num                         = $Db::query("select count(*) as cnt from question where user_id=" . $value_teacher['center_user_id'])[0]['cnt'];
			$answer_num                           = $Db::query("select count(*) as cnt from answer where user_id=" . $value_teacher['center_user_id'])[0]['cnt'];
			$data_sets[$key_teacher]['questions'] = $question_num + $answer_num;


			//获取笔记数
			$sql_count_notes                  = "select count(*) as cnt from section_note where user_id=" . $value_teacher['center_user_id'];
			$data_sets[$key_teacher]['notes'] = $Db::query($sql_count_notes)[0]['cnt'];

			//获取评论数
			$sql_count_comments                  = "select count(*) as cnt from comment where user_id=" . $value_teacher['center_user_id'];
			$data_sets[$key_teacher]['comments'] = $Db::query($sql_count_comments)[0]['cnt'];
		}
		return $this->ok(['num' => $Db::query("select count(*) as cnt from mooc_user where mooc_user.type=2" . $where_nick_name . $where_center_id)[0]['cnt'], 'list' => $data_sets], 12111, '获取慕课统计成功');

	}

	//获取老师详情信息
	public function getTeacherByID()
	{
		set_time_limit(0);
		$Db            = new \think\Db;
		$followers     = array();
		$courses       = array();
		$questions     = array();
		$section_notes = array();
		$comments      = array();
		//获取该老师关注的所有用户
		if ( ! empty($Db::query("select * from follow where user_id=" . input('param.id', 1))))
		{
			foreach ($Db::query("select * from follow where user_id=" . input('param.id', 1)) as $k => $v)
			{
				$followers[$k] = $Db::query("select * from mooc_user where id=" . $v['follow_id']);
			}
		}

		//获取该老师下的所有课程
		if ( ! empty($Db::query("select * from course_rela where type=3 and other_id=" . input('param.id', 1))))
		{
			foreach ($Db::query("select * from course_rela where type=3 and other_id=" . input('param.id', 1)) as $k => $v)
			{
				$courses[$k] = $Db::query("select * from course where id=" . $v['course_id']);
			}
		}

		########获取该老师下所有章节########
		########三层循环，逻辑较复杂########
		//先获取老师下的所有课程
		if ( ! empty($Db::query("select * from course_rela where type=3 and other_id=" . input('param.id', 1))))
		{
			$courses_temp = array();
			foreach ($Db::query("select * from course_rela where type=3 and other_id=" . input('param.id', 1)) as $course_k => $course_v)
			{
				//获取课下的所有章
				if ( ! empty($Db::query("select * from chapter where course_id=" . $course_v['course_id'])))
				{
					foreach ($Db::query("select * from chapter where course_id=" . $course_v['course_id']) as $chapter_k => $chapter_v)
					{
						//获取章下的所有节
						if ( ! empty($Db::query("select * from section where chapter_id=" . $chapter_v['id'])))
						{
							foreach ($Db::query("select * from section where chapter_id=" . $chapter_v['id']) as $section_k => $section_v)
							{


								$courses_temp[$Db::query("select * from course where id=" . $course_v['course_id'])[0]['course_title']][$Db::query("select * from chapter where id=" . $chapter_v['id'])[0]['chapter_title']][$section_v['id']] = $section_v;
							}
						}
					}
				}
			}
		}
		$arr1 = array();
		$i    = 0;
		foreach ($courses_temp as $k1 => $v1)
		{
			$arr1[$i]['title'] = $k1;
			if ( ! empty($v1))
			{
				$arr1[$i]['expand'] = "true";
				$arr2               = array();
				$j                  = 0;
				foreach ($v1 as $k2 => $v2)
				{
					$arr3              = array();
					$m                 = 0;
					$arr2[$j]['title'] = $k2;
					if ( ! empty($v2))
					{
						$arr2[$j]['expand'] = "true";
						foreach ($v2 as $k3 => $v3)
						{
							$arr3[$m]['title'] = $v3['section_title'];
							$m++;
						}
						$arr2[$j]['children'] = $arr3;
					}
					else
					{
						$arr2[$j]['expand'] = "false";
					}
					$j++;
				}
				$arr1[$i]['children'] = $arr2;
			}
			else
			{
				$arr1[$i]['expand'] = "false";
			}
			$i++;
		}

		//获取所有学生
//		if ( ! empty($Db::query("select * from mooc_user where center_user_id=" . input('param.id', 1) . " and type=1")))
//		{
//			foreach ($Db::query("select * from mooc_user where center_user_id=" . input('param.id', 1) . " and type=1") as $k => $v)
//			{
//				$students[$k] = $Db::query("select * from course where id=" . $v['course_id']);
//			}
//		}

		//获取所有问答
		if ( ! empty($Db::query("select * from question where user_id=" . input('param.id', 1))))
		{
			foreach ($Db::query("select * from question where user_id=" . input('param.id', 1)) as $k => $v)
			{
				$questions[$k] = $v['content'];
			}
		}

		//获取笔记
		if ( ! empty($Db::query("select * from section_note where user_id=" . input('param.id', 1))))
		{
			foreach ($Db::query("select * from section_note where user_id=" . input('param.id', 1)) as $k => $v)
			{
				$section_notes[$k] = $v['content'];
			}
		}
//获取所有评论
		if ( ! empty("select * from comment where user_id=" . input('param.id', 1)))
		{
			foreach ($Db::query("select * from comment where user_id=" . input('param.id', 1)) as $k => $v)
			{
				$comments[$k] = $v['content'];
			}
		}

		return $this->ok(array("info" => current($Db::query("select * from mooc_user where type=2 and id=" . input('param.id', 1))), "follow" => $followers, "courses" => $courses, "chapter" => $arr1, "questions" => $questions, "section_notes" => $section_notes, "comments" => $comments, "center_name" => current($Db::query("select * from mooc_center where id=" . current($Db::query("select * from mooc_user where type=2 and id=" . input('param.id', 1)))['center_id']))['center_name']), 12111, '获取慕课统计成功');
	}

	//根据老师ID获取该老师下统计数据服务
	function getStatisticsDataByID()
	{
		$Db         = new \think\Db;
		$user_token = "6cc6c00edab41244b13e3f71acab82439ae99ada";
		$user       = (new MoocUser())->where(['user_token' => $user_token])->find();

		//获取课程数服务
		$course_num = 0;
		$course_num = $Db::query("select count(*) as cnt from course_rela where type=3 and other_id=" . $user['id'])[0]['cnt'];

		//获取学生数服务
		//先获取该老师下的所有课
		$student_num = 0;
		if ( ! empty($Db::query("select * from course_rela where type=3 and other_id=" . $user['id'])))
		{
			foreach ($Db::query("select * from course_rela where type=3 and other_id=" . $user['id']) as $k => $v)
			{
				//再获取该门课下有多少学生报名
				$student_num = $student_num + $Db::query("select count(*) as cnt from baoming where course_id=" . $v['course_id'])[0]['cnt'];
			}
		}
		//获取问答数服务
		$qa_num       = 0;
		$question_num = $Db::query("select count(*) as cnt from question where user_id=" . $user['id'])[0]['cnt'];
		$answer_num   = $Db::query("select count(*) as cnt from answer where user_id=" . $user['id'])[0]['cnt'];
		$qa_num       = $question_num + $answer_num;

		//获取笔记数服务
		$note_num = 0;
		$note_num = $Db::query("select count(*) as cnt from section_note where user_id=" . $user['id'])[0]['cnt'];

		//获取收藏数服务
		$collect_num = 0;
		$collect_num = $Db::query("select count(*) as cnt from collect where user_id=" . $user['id'])[0]['cnt'];

		//课程访问统计服务
		/*******************************************************************************************************/
		$dateSets  = array();
		$startDate = strtotime(input('param.startDate', 1) . " 00:00:00");
		$endDate   = strtotime(input('param.endDate', 1) . " 23:59:59");
		if (empty($startDate))
		{
			$startDate = 0;
		}
		if (empty($endDate))
		{
			$endDate = time();
		}
		for ($i = $startDate; $i < $endDate; $i += 24 * 3600)
		{
			$dateSets[] = $i;
		}

		//如果只是一天的时间范围
		if (count($dateSets) == 1)
		{
			$xaxis = array();
			for ($i = $startDate; $i < $endDate; $i += 3600)
			{
				$xaxis[] = $i;
			}
		}
		//如果时间范围在1天到5天之内
		elseif (count($dateSets) > 1 && count($dateSets) < 5)
		{
			$xaxis = array();
			for ($i = $startDate; $i < $endDate; $i += 12 * 3600)
			{
				$xaxis[] = $i;
			}
		}
		//如果时间范围在5天到10天之内
		elseif (count($dateSets) > 5 && count($dateSets) < 10)
		{
			$xaxis = array();
			for ($i = $startDate; $i < $endDate; $i += 24 * 3600)
			{
				$xaxis[] = $i;
			}
		}
		//如果时间范围在10天以上，则按固定十个时间点取时间集合
		elseif (count($dateSets) > 10)
		{
			$xaxis = array();
			for ($m = 0; $m < 10; $m++)
			{
				$xaxis[] = $startDate + ceil(count($dateSets) / 10) * 24 * 3600 * $m;
			}
			array_push($xaxis, end($dateSets));
			unset($xaxis[count($xaxis) - 2]);
		}

		//遍历所有课程服务
		$fangwenliang_temp_course = array();
		if ( ! empty($Db::query("select * from course_rela where type=3 and other_id=" . $user['id'])))
		{
			foreach ($Db::query("select * from course_rela where type=3 and other_id=" . $user['id']) as $k_course => $v_course)
			{
				$course_title    = $Db::query("select * from course where id=" . $v_course['course_id'])[0]['course_title'];
				$baoming_num_set = array();
				foreach ($xaxis as $k => $v)
				{
					//统计报名数服务
					$baoming_num       = $Db::query("select count(*) as cnt from baoming where course_id=" . $v_course['course_id'] . " and create_time>" . strtotime(date("Y-m-d 00:00:00", $v)) . " and create_time<" . strtotime(date("Y-m-d 23:59:59", $v)))[0]['cnt'];
					$baoming_num_set[] = $baoming_num;
				}
				$fangwenliang_temp_course[$k_course]['name'] = $course_title;
				$fangwenliang_temp_course[$k_course]['type'] = "line";
				$fangwenliang_temp_course[$k_course]['data'] = $baoming_num_set;
			}
		}

		$fangwenliang = array($xaxis, $fangwenliang_temp_course);
		/*******************************************************************************************************/

		//获取课程下学生分布服务
		//遍历所有课程服务
		$pie_student_course = array();
		if ( ! empty($Db::query("select * from course_rela where type=3 and other_id=" . $user['id'])))
		{
			foreach ($Db::query("select * from course_rela where type=3 and other_id=" . $user['id']) as $k_course => $v_course)
			{
				$course_title = $Db::query("select * from course where id=" . $v_course['course_id'])[0]['course_title'];
				$baoming_num  = $Db::query("select count(*) as cnt from baoming where course_id=" . $v_course['course_id'])[0]['cnt'];
				array_push($pie_student_course, array("value" => $baoming_num, "name" => $course_title));
			}
		}

		//获取学生性别比例服务
		$sex_0_num = 0;//保密
		$sex_1_num = 0;//帅哥
		$sex_2_num = 0;//美女
		if ( ! empty($Db::query("select * from course_rela where type=3 and other_id=" . $user['id'])))
		{
			foreach ($Db::query("select * from course_rela where type=3 and other_id=" . $user['id']) as $k_course => $v_course)
			{
				$sex_0_num_temp = $Db::query("select count(*) as cnt from baoming left join mooc_user on baoming.user_id=mooc_user.id where baoming.course_id=" . $v_course['course_id'] . " and mooc_user.sex=0")[0]['cnt'];
				$sex_0_num      = $sex_0_num + $sex_0_num_temp;
				$sex_1_num_temp = $Db::query("select count(*) as cnt from baoming left join mooc_user on baoming.user_id=mooc_user.id where baoming.course_id=" . $v_course['course_id'] . " and mooc_user.sex=1")[0]['cnt'];
				$sex_1_num      = $sex_1_num + $sex_1_num_temp;
				$sex_2_num_temp = $Db::query("select count(*) as cnt from baoming left join mooc_user on baoming.user_id=mooc_user.id where baoming.course_id=" . $v_course['course_id'] . " and mooc_user.sex=1")[0]['cnt'];
				$sex_2_num      = $sex_2_num + $sex_2_num_temp;
			}
		}

		//根据地区获取学生分布服务
		//先获取所有地区服务
		$students_temp = array();
		if ( ! empty($Db::query("select * from course_rela where type=3 and other_id=" . $user['id'])))
		{
			foreach ($Db::query("select * from course_rela where type=3 and other_id=" . $user['id']) as $k_course => $v_course)
			{
				array_push($students_temp, $Db::query("select * from baoming left join mooc_user on baoming.user_id=mooc_user.id where baoming.course_id=" . $v_course['course_id']));


			}
		}
		$pie_student_area = array();
		$new_arr          = array();
		foreach ($students_temp[0] as $k => $v)
		{
			$new_arr[$v['area']][] = $v;
		}
		foreach ($new_arr as $index => $item)
		{
			array_push($pie_student_area, array("value" => count($item), "name" => $index));
		}

		$result = array("course_num" => $course_num, "student_num" => $student_num, "qa_num" => $qa_num, "note_num" => $note_num, "collect_num" => $collect_num, "course_visit_num" => $fangwenliang, "student_course_pie" => $pie_student_course, "student_sex_none" => $sex_0_num, "student_sex_boy" => $sex_1_num, "student_sex_girl" => $sex_2_num, "pie_student_area" => $pie_student_area);

		return $this->ok($result, 200, "获取信息成功");
	}

	//获取学习进度服务
	public function getLearnSchedule()
	{
		$Db         = new \think\Db;
		$user_token = "6cc6c00edab41244b13e3f71acab82439ae99ada";
		$user       = (new MoocUser())->where(['user_token' => $user_token])->find();

		$xasix = array(0, 0.2, 0.4, 0.6, 0.8, 1);
//遍历所有课程服务
		$fangwenliang_temp_course = array();
		if ( ! empty($Db::query("select * from course_rela where type=3 and other_id=" . $user['id'])))
		{
			foreach ($Db::query("select * from course_rela where type=3 and other_id=" . $user['id']) as $k_course => $v_course)
			{
				$yasix        = array("0" => 0, "0.2" => 0, "0.4" => 0, "0.6" => 0, "0.8" => 0, "1" => 0);
				$course_title = $Db::query("select * from course where id=" . $v_course['course_id'])[0]['course_title'];
				//获取该课程的进度服务
				if ( ! empty($Db::query("select * from schedule where course_id=" . $v_course['course_id'])))
				{
					foreach ($Db::query("select * from schedule where course_id=" . $v_course['course_id']) as $k_schedule => $v_schedule)
					{
						$x                                            = array_sum(json_decode($v_schedule['more'], TRUE)) / $Db::query("select * from course where id=" . $v_course['course_id'])[0]['total_time'];
						$yasix[$this->__getVarianceByMat($xasix, $x)] = $yasix[$this->__getVarianceByMat($xasix, $x)] + 1;
					}
				}

				$fangwenliang_temp_course[$k_course]['name'] = $course_title;
				$fangwenliang_temp_course[$k_course]['type'] = "line";
				$fangwenliang_temp_course[$k_course]['data'] = array_values($yasix);
			}
		}

		$fangwenliang = array($xasix, $fangwenliang_temp_course);
		return $this->ok($fangwenliang, 200, "获取信息成功");
	}

	//PHP求方差服务
	function __getVarianceByMat($arr1, $x)
	{
		//$arr1=array(0,0.1,0.2,0.4,0.5,0.6,0.7,0.8);
		$count = count($arr1);
		for ($i = 0; $i < $count; $i++)
		{
			$arr2[] = abs($x - $arr1[$i]);
		}
		$min = min($arr2);
		for ($i = 0; $i < $count; $i++)
		{
			if ($min == $arr2[$i])
			{
				$result = $arr1[$i];
			}
		}
		return $result;
	}
}