<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/8/1
 * Time: 15:57
 */

namespace app\v1\controller;

use app\v1\model\CenterCourse;
use app\v1\model\Course;
use app\v1\model\MoocUser;
use app\v1\model\Baoming as Baomings;
use app\v1\model\Section;
use app\v1\model\Schedule;

class Baoming extends Base
{
    /**
     * @return array|bool
     * @throws user_token 用户令牌
     * @throws course_id 课程id
     */
    public function create(){
        $_GET['user_token'] = 'cd69089bea5af15192e10ce17be7d4939eb02bb7';
        $_GET['timestamp'] = time();
        $salt = 'ttVm5';
        $_GET['sign'] = encrypt_key(['v1/baoming/create', $_GET['timestamp'], $_GET['user_token'], $salt], '');
        $_GET['course_id'] = 17;

        $user_token = input('param.user_token','','trim');
        $course_id = input('param.course_id',0,'intval');

        //令牌校验
        $tokenRes = checkUserToken($user_token);
        if($tokenRes !== true){
            return $tokenRes;
        }

        //数据校验
        $cenCourModel = new CenterCourse();
        $user = (new MoocUser())->where(['user_token'=>$user_token])->find();
        //是否是学生
        if($user['type'] == 2){
            return $this->fail(11011,'老师不能报名');
        }
        if(!empty($course_id)){
            $course = $cenCourModel->where(['course_id'=>$course_id,'center_id'=>$user['center_id']])->find();
            if(!$course){
                return $this->fail(10010,'课程不存在或课程不属于此文化馆');
            }
        }else{
            return $this->fail(10012,'课程id必须');
        }

        //是否已报名
        $scheduleModel = new Schedule();
        $baomingModel = new Baomings();
        $chapterModel = new \app\v1\model\Chapter();
        $sectionModel = new Section();
        $yibaoming = $baomingModel->where(['user_id'=>$user['id'],'course_id'=>$course_id])->find();
        if($yibaoming){
            //已报名 确认看到第几节
            $speed = $scheduleModel
                ->alias('sd')
                ->join('__SECTION__ s','s.id=sd.section_id')
                ->where(['sd.user_id'=>$user['id'],'sd.course_id'=>$course_id])
                ->field('sd.section_id,s.video_main,sd.current_time')
                ->find();
            //添加播放量
            $cenCourModel->where(['center_id'=>$user['center_id'],'course_id'=>$course_id])->setInc('play_num');
            return $this->ok($speed,10006,'已报名');
        }

        //整理数据
        $data = [
            'center_id'=>$user['center_id'],
            'user_id'=>$user['id'],
            'course_id'=>$course_id,
            'create_time'=>time()
        ];

        if($baomingModel->allowField(true)->save($data) > 0){
            //获取此课程第一章第一节的节id
            $chapter_id = $chapterModel->where(['course_id'=>$course_id])->order(['id'=>'asc'])->value('id');
            $section = $sectionModel->where(['chapter_id'=>$chapter_id])->order(['id'=>'asc'])->field('id as section_id,video_main')->find();
            $section['current_time'] = 0;
            //进度表添加数据
            $scheduleModel->save(['center_id'=>$user['center_id'],'user_id'=>$user['id'],'course_id'=>$course_id,'section_id'=>$section['section_id'],'current_time'=>0,'more'=>json_encode([[$section['section_id']=>0]])]);

            //添加播放量
            $cenCourModel->where(['center_id'=>$user['center_id'],'course_id'=>$course_id])->setInc('play_num');
            return $this->ok($section,10111,'报名成功');
        }else{
            return $this->fail(10002,'报名失败');
        }



    }
}