<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/8/2
 * Time: 14:19
 */

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
use think\Db;

class Statistics extends Base
{
    /**
     * 获取课程总览数据
     * @return array|bool
     * @param center_token 场馆令牌
     */
    public function pandect()
    {
        set_time_limit(0);
        //令牌校验
        $tokenRes = verify();
        if ($tokenRes['status'] == 0) {
            return $tokenRes;
        } else {
            $center_id = $tokenRes['data']['center_id'];
        }

        //获取场馆中所有课程评论数量
        $comment_num = (new Comment())->where(['center_id' => $center_id])->count(1);

        //获取场馆下的课程数量
        $course_num = (new CenterCourse())->where(['center_id' => $center_id])->group('course_id')->count(1);

        //获取场馆下的学生数
        $student_num = (new MoocUser())->where(['center_id' => $center_id, 'type' => 2])->count(1);

        //获取场馆下的老师数
        $teacher_num = (new MoocUser())->where(['center_id' => $center_id, 'type' => 1])->count(1);

        //问答数量
        $question_num = (new Question())->where(['center_id' => $center_id])->count(1);
        $question_ids = (new Question())->where(['center_id' => $center_id])->column('id');
        $answer_num = (new Answer())->where(['question_id' => ['in', $question_ids]])->count(1);
        $que_ans_num = $question_num + $answer_num;

        //笔记
        $note_num = (new SectionNote())->where(['center_id' => $center_id])->count(1);

        //数据整理
        $data = [
            'comment_num' => $comment_num,
            'course_num' => $course_num,
            'student_num' => $student_num,
            'teacher_num' => $teacher_num,
            'que_ans_num' => $que_ans_num,
            'note_num' => $note_num
        ];

        return $this->ok($data, 30111, '获取课程总览数据成功');
    }

    /**
     * 获取场馆下所有的慕课
     * @return array|bool
     * @param center_token 场馆令牌
     */
    public function courses($center_id)
    {
        set_time_limit(0);
        $courses = (new CenterCourse)
            ->alias('cc')
            ->join('__COURSE__ c', 'c.id=cc.course_id')
            ->where(['center_id' => $center_id])
            ->field('c.id,c.course_title')
            ->column('course_title');

        if ($courses == null) {
            return $this->fail(32001, '场馆下无课程');
        } else {
            return $courses = \collection($courses)->toArray();
        }
    }

    /**
     * 获取受欢迎课程程度，按报名人数
     * @return array|bool
     * @param center_token 场馆令牌
     */
    public function Popularity()
    {
        set_time_limit(0);
        //令牌校验
        $tokenRes = verify();
        if ($tokenRes['status'] == 0) {
            return $tokenRes;
        } else {
            $center_id = $tokenRes['data']['center_id'];
        }

        //获取场馆内所有课程
        $courses = $this->courses($center_id);
        if (array_key_exists('status', $courses)) {
            return $courses;
        }


        //获取所有课程的报名人数
        $baoming_num = (new CenterCourse())
            ->alias('cc')
            ->join('__BAOMING__ b', 'b.course_id=cc.course_id', 'left')
            ->where(['cc.center_id' => $center_id])
            ->group('b.course_id')
            ->order('cc.course_id')
            ->column('count(b.id) as baoming_num');

        return $this->ok(['baoming_num' => $baoming_num, 'course_titles' => $courses], 30112, '获取课程受欢迎程度成功');
    }

    /**
     * 获取活跃度最高的课程，按笔记问答总数
     * @return array|bool
     * @param center_token 场馆令牌
     */
    public function activity()
    {
        set_time_limit(0);
        //令牌校验
        $tokenRes = verify();
        if ($tokenRes['status'] == 0) {
            return $tokenRes;
        } else {
            $center_id = $tokenRes['data']['center_id'];
        }

        //获取所有场馆下课程
        $courses = $this->courses($center_id);
        if (array_key_exists('status', $courses)) {
            return $courses;
        }


        //获取课程所有的问答加笔记数量
        $numArr = (new Course())->alias('c')
            ->join('__CENTER_COURSE__ cc', 'cc.course_id=c.id')
            ->join('__SECTION_NOTE__ sn', 'sn.course_id=c.id', 'left')
            ->where(['cc.center_id' => $center_id])
            ->group('c.id')
            ->column('count(sn.id)');


        $queNum = (new Course())->alias('c')
            ->join('__CENTER_COURSE__ cc', 'cc.course_id=c.id')
            ->join('__QUESTION__ q', 'q.course_id = c.id', 'left')
            ->where(['cc.center_id' => $center_id])
            ->group('c.id')
            ->column('count(q.id)');


        $ansNum = (new Course())->alias('c')
            ->join('__CENTER_COURSE__ cc', 'cc.course_id=c.id')
            ->join('__QUESTION__ q', 'q.course_id = c.id', 'left')
            ->join('__ANSWER__ a', 'a.question_id = q.id', 'left')
            ->where(['cc.center_id' => $center_id])
            ->group('c.id')
            ->column('count(a.id)');


        $total = [];
        foreach ($numArr as $key => $item) {
            $total[] = $item + $queNum[$key] + $ansNum[$key];
        }

        return $this->ok(['courses' => $courses, 'total' => $total], 12011, '获取活跃度成功');

    }

    /**
     * 获取最受欢迎的老师，按照学生数
     * @return array|bool
     * @param center_token 场馆令牌
     */
    public function teacherPop()
    {
        set_time_limit(0);
        //令牌校验
        $tokenRes = verify();
        if ($tokenRes['status'] == 0) {
            return $tokenRes;
        } else {
            $center_id = $tokenRes['data']['center_id'];
        }

        //获取所有的老师
        $userModel = new MoocUser();
        $teachers = $userModel->where(['center_id' => $center_id, 'type' => 2])->order('id')->column('nick_name');
        $teacherIds = $userModel
            ->where(['center_id' => $center_id, 'type' => 2])
            ->order('id')
            ->field('id')
            ->select();
//        var_dump(\collection($teacherIds)->toArray());

        //获取场馆下每个老师对应的课程
        $teacher_course = $userModel
            ->alias('u')
            ->join('__COURSE_RELA__ cr', 'cr.other_id = u.id  and cr.type = 3', 'left')
            ->field('u.id,cr.course_id')
            ->where(['u.center_id' => $center_id, 'u.type' => 2])
            ->select();
        $teacher_course = \collection($teacher_course)->toArray();

        //获取每个课程对应的学生数
        $student_num = (new Course())
            ->alias('c')
            ->join('__CENTER_COURSE__ cc', 'cc.course_id=c.id', 'left')
            ->join('__BAOMING__ b', 'b.course_id=c.id and b.center_id=cc.center_id', 'left')
            ->where(['cc.center_id' => 14])
            ->field('c.id,count(b.user_id) as student_num')
            ->group('c.id')
            ->select();

        $newTeaCour = [];
        foreach ($teacherIds as $key => $item) {
            $courses = [];
            foreach ($teacher_course as $val) {
                if ($val['course_id'] == null) {
                    unset($val);
                } else {
                    if ($item['id'] == $val['id']) {
                        $courses[] = $val['course_id'];
                    }
                }
                $newTeaCour[$key] = [$item['id'], 'course_id' => $courses];
            }
        }

//课程对应学生数
        /*
         *array(11) {
          [0]=>
          array(2) {
            ["id"]=>
            int(2)
            ["student_num"]=>
            int(0)
          }
          [1]=>
          array(2) {
            ["id"]=>
            int(5)
            ["student_num"]=>
            int(0)
          }
          [2]=>
          array(2) {
            ["id"]=>
            int(6)
            ["student_num"]=>
            int(1)
          }
          [3]=>
          array(2) {
            ["id"]=>
            int(7)
            ["student_num"]=>
            int(0)
          }
          [4]=>
          array(2) {
            ["id"]=>
            int(9)
            ["student_num"]=>
            int(0)
          }
          [5]=>
          array(2) {
            ["id"]=>
            int(11)
            ["student_num"]=>
            int(0)
          }
          [6]=>
          array(2) {
            ["id"]=>
            int(12)
            ["student_num"]=>
            int(2)
          }
          [7]=>
          array(2) {
            ["id"]=>
            int(13)
            ["student_num"]=>
            int(2)
          }
          [8]=>
          array(2) {
            ["id"]=>
            int(20)
            ["student_num"]=>
            int(0)
          }
          [9]=>
          array(2) {
            ["id"]=>
            int(21)
            ["student_num"]=>
            int(0)
          }
          [10]=>
          array(2) {
            ["id"]=>
            int(22)
            ["student_num"]=>
            int(0)
          }
        } */

//老师写的课程
        /*array(4) {
          [0]=>
          array(2) {
            [0]=>
            int(13)
            ["course_id"]=>
            array(0) {
            }
          }
          [1]=>
          array(2) {
            [0]=>
            int(17)
            ["course_id"]=>
            array(0) {
            }
          }
          [2]=>
          array(2) {
            [0]=>
            int(18)
            ["course_id"]=>
            array(1) {
              [0]=>
              int(20)
            }
          }
          [3]=>
          array(2) {
            [0]=>
            int(19)
            ["course_id"]=>
            array(2) {
              [0]=>
              int(20)
              [1]=>
              int(22)
            }
          }
        }
        */
//        var_dump(\collection($newTeaCour)->toArray());die ;
//
// var_dump(\collection($student_num)->toArray());die ;
        $newTeaStu = [];
        $num = 0;

        foreach ($newTeaCour as $key => $item) {
            if (count($item['course_id']) == 0) {
                $newTeaStu[$key] = 0;
            } else {
                foreach ($item['course_id'] as $val) {
                    foreach ($student_num as $value) {
                        if ($val == $value['id']) {
                            ;
                            $num += $value['student_num'];
                        }
                    }
                }
                $newTeaStu[$key] = $num;
                $num = 0;
            }
        }

        return $this->ok(['teachers' => $teachers, 'num' => $newTeaStu], 12111, '获取老师欢迎程度成功');

    }

    /**
     * 获取学生活跃度  笔记问答总数
     * @return array|bool
     * @param center_token 场馆令牌
     */
    public function getStuActivity()
    {
        set_time_limit(0);
        $time_slot = input('param.slot', 1, 'intval');  //1 近24小时  2 近一周  3 近一个月   4 近三个月

        //令牌校验
        $tokenRes = verify();
        if ($tokenRes['status'] == 0) {
            return $tokenRes;
        } else {
            $center_id = $tokenRes['data']['center_id'];
        }


        $time_interval = getDateTimeArray($time_slot);

        $noteModel = new SectionNote();
        $questionModel = new Question();
        $noteReplyModel = new SectionNoteReply();
        $ansewerModel = new Answer();
        $count = count($time_interval);
        $data = [];
        foreach ($time_interval as $key => $item) {
            if($key != $count-1) {
                //笔记数量
                $note_num = $noteModel->where(['delete_time' => 0, 'center_id' => $center_id, 'create_time' => ['between', [$item, $time_interval[$key + 1]]]])->count(1);
                //回复笔记数量
                $reply_num = $noteReplyModel->where(['delete_time' => 0, 'create_time' => ['between', [$item, $time_interval[$key + 1]]]])->count(1);
                //问题数量
                $question_num = $questionModel->where(['delete_time' => 0, 'center_id' => $center_id, 'create_time' => ['between', [$item, $time_interval[$key + 1]]]])->count(1);
                //回答数量
                $answer_num = $ansewerModel->where(['delete_time' => 0, 'create_time' => ['between', [$item, $time_interval[$key + 1]]]])->count(1);
                $num = $note_num + $reply_num + $question_num + $answer_num;
                $data[] = $num;
            }
        }

        unset($time_interval[0]);
        $time_interval = array_values($time_interval);
        $time_interval = array_map(function ($v) {
            return date('m-d H', $v);
        }, $time_interval);

        return $this->ok(['time_interval' => $time_interval, 'data' => $data], 11101, '获取学生活跃度成功');

    }


    /**
     * 好评课程占比例
     * @return array|bool
     * @param center_token 场馆令牌
     */
    public function praise()
    {
        set_time_limit(0);
        //令牌校验
        $tokenRes = verify();
        if ($tokenRes['status'] == 0) {
            return $tokenRes;
        } else {
            $center_id = $tokenRes['data']['center_id'];
        }



        //获取场馆下的课程数量
//        $centerCourse = new CenterCourse();
//        $count = $centerCourse
//            ->alias('cc')
//            ->join('__COURSE__ c', 'c.id=cc.course_id')
//            ->where(['cc.center_id' => $center_id, 'cc.status' => 1, 'c.open_status' => 2])
//            ->count(1);

        //好评的数量
        $commentModel = new Comment();
        $subQuery = $commentModel->where(['center_id'=>$center_id])->fieldRaw('id,(practical_score+concise_score+clear_score)/3 as score')->buildSql();
        $praise_count = Db::table($subQuery.'s')->where(['s.score'=>['>',5]])->count(1);
        //差评的数量
        $negative_count = Db::table($subQuery.'s')->where(['s.score'=>['<',5]])->count(1);

        return $this->ok(['praise' => $praise_count, 'nagative' => $negative_count], 22222, '获取好评课程占比成功');

    }

    /**
     * 参与讨论的学生占比
     * @return array|bool
     * @param center_token 场馆令牌
     */
    public function discuss()
    {
        set_time_limit(0);
        //令牌校验
//        $tokenRes = verify();
//        if ($tokenRes['status'] == 0) {
//            return $tokenRes;
//        } else {
//            $center_id = $tokenRes['data']['center_id'];
//        }
        $center_id = 1;

        //获取场馆下所有报名的学生id
        $baomingModel = new Baoming();
        $baoMing_count = $baomingModel->where(['center_id'=>$center_id])->group('user_id')->column('user_id');
//        var_dump($baoMing_count);die;
        //学生数
        $stu_num = count($baoMing_count);
        /*获取参与讨论的学生数量*/
        //获取写笔记的学生id
        $noteModel = new SectionNote();
        $note_user = $noteModel->where(['center_id'=>$center_id])->group('user_id')->column('user_id');
        //获取回复笔记的学生id
        $noteReplyModel = new SectionNoteReply();
        $reply_user = $noteReplyModel->alias('nr')->join('__SECTION_NOTE__ n','n.id=nr.note_id')->where(['n.center_id'=>$center_id,'nr.user_id'=>['not in',$note_user]])->column('nr.user_id');
        $user = array_merge($note_user,$reply_user);

        //获取问题表中的学生
        $questionModle = new Question();
        $que_user = $questionModle->where(['center_id'=>$center_id,'user_id'=>['not in',$user]])->column('user_id');
        $user = array_merge($user,$que_user);


        //获取回复问题表中的学生id
        $answerModel = new Answer();
        $ans_user = $answerModel->alias('a')->join('__QUESTION__ q','q.id=a.question_id')->where(['q.center_id'=>$center_id,'a.user_id'=>['not in',$user]])->column('a.user_id');
        $user = array_merge($user,$ans_user);
        //所有参与讨论的学生数
        $discuss_num = count($user);

        //未参与讨论的学生数
        $nodiscuss_num = $stu_num-$discuss_num;

        return $this->ok(['discuss'=>$discuss_num,'nodiscuss'=>$nodiscuss_num],66166,'获取参与讨论的学生数');

    }

    /**
     * 登陆设备比
     * @return array|bool
     * @param center_token 场馆令牌
     */
    public function equipment(){
        set_time_limit(0);
        //令牌校验
        $tokenRes = verify();
        if ($tokenRes['status'] == 0) {
            return $tokenRes;
        } else {
            $center_id = $tokenRes['data']['center_id'];
        }

        //获取登录pc端与微信端登陆数量
        $terminalModel = new Terminal();
        $terminal = $terminalModel->where(['center_id'=>$center_id])->find();

        if($terminal == null){
            $terminal['pc_num'] = 0;
            $terminal['wx_num'] = 0;
        }

        return $this->ok(['pc_num'=>$terminal['pc_num'],'wx_num'=>$terminal['wx_num']],32111,'获取登录设备比成功');
    }

    /**
     * 获取慕课统计列表
     */
    public function getCourses(){
        set_time_limit(0);

        $tokenRes = verify();
        if ($tokenRes['status'] == 0) {
            return $tokenRes;
        } else {
            $centerId = $tokenRes['data']['center_id'];
        }


        $statisModel = new tongji();
        $list = $statisModel->getCourseList($centerId);

        $num = $statisModel->getCourseList($centerId,true);

        foreach($list as $k=>$v){
            $list[$k] = $statisModel->prepareData($v);
        }


        return $this->ok(['num'=>$num,'list'=>$list],12111,'获取慕课统计成功');
    }

    /**
     * 获取慕课统计详情
     */
    public function readCourse(){
        $statisModel = new tongji();
        $course = $statisModel->read();

        if ($course) {
            $course = $statisModel->prepareData($course,true);
            return $this->ok($course, 21102, '获取成功', 1);
        } else {
            return $this->fail(21003, '获取失败');
        }

    }

    /**
     * 获取教学统计
     */
    public function teacherList(){

    }

}