<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/8/7
 * Time: 16:14
 */

namespace app\v1\controller;

use app\v1\model\MoocUser;
use \app\v1\model\Schedule as Schedules;

class Schedule extends Base
{
    /**
     * 获取当前进度
     * @param user_token 用户令牌
     * @param course_id 课程id  section_id
     * @param section_id  章节id
     * @param current_time 当前观看的时间
     * @return array|bool
     */
    public function index()
    {
//        $_GET['user_token']  = 'cd69089bea5af15192e10ce17be7d4939eb02bb7';
//        $_GET['timestamp'] = $timestamp = time();
//        $salt = 'ttVm5';
//        $url = 'v1/schedule/update';
//        $_GET['sign'] = encrypt_key([$_GET['user_token'], $url, $timestamp, $salt], '');

        $user_token = input('param.user_token', '', 'trim');
        $course_id = input('param.course_id', 0, 'intval');

        //令牌校验
        $tokenRes = checkUserToken($user_token);
        if ($tokenRes != true) {
            return $tokenRes;
        }

        if (empty($course_id)) {
            return $this->fail(10001, '课程id必须');
        }

        $scheModel = new Schedules();
        $user = (new MoocUser())->where(['user_token' => $user_token])->find();
        $speed = $scheModel->where(['user_id' => $user['id'], 'course_id' => $course_id])->find();

        return $this->ok($speed, 10100, '进度获取成功');

    }

    /**
     * 更新当前进度
     * @param user_token 用户令牌
     * @param course_id 课程id  section_id
     * @param section_id  章节id
     * @param current_time 当前观看的时间
     * @return array|bool
     */
    public function update()
    {
        //        //测试用例
//        $_GET['user_token']  = 'cd69089bea5af15192e10ce17be7d4939eb02bb7';
//        $_GET['timestamp'] = $timestamp = time();
//        $salt = 'ttVm5';
//        $url = 'v1/schedule/update';
//        $_GET['sign'] = encrypt_key([$_GET['user_token'], $url, $timestamp, $salt], '');

        $user_token = input('param.user_token', '', 'trim');
        $course_id = input('param.course_id', 0, 'intval');
        $section_id = input('param.section_id', 0, 'intval');
        $current_time = input('param.current_time', 0, 'intval');

        //令牌校验
        $tokenRes = checkUserToken($user_token);
        if ($tokenRes != true) {
            return $tokenRes;
        }

        if (empty($course_id)) {
            return $this->fail(10001, '课程id必须');
        }

        if (empty($section_id)) {
            return $this->fail(10002, '节id必须');
        }

        if (empty($current_time)) {
            return $this->fail(10003, '当前观看时间必须');
        }

        $scheModel = new Schedules();
        $user = (new MoocUser())->where(['user_token' => $user_token])->find();
        $speed = $scheModel->where(['user_id' => $user['id'], 'course_id' => $course_id])->find();

        $data = [
            'section_id' => $section_id,
            'current_time' => $current_time
        ];
        if ($speed == null) {
            //之前未观看过此课程  添加进度
            $data['course_id'] = $course_id;
            $data['user_id'] = $user['id'];
            $data['center_id'] = $user['center_id'];
            $data['more'] = json_encode([$section_id=>0]);
            if($scheModel->save($data) > 0 ){
                return $this->ok('',11111,'添加进度成功');
            }else{
                return $this->fail(11011,'添加进度失败');
            }

        } else {
            //之前观看过此课程   更新进度
            $more = json_decode($speed['more'],true);
            if(array_key_exists($section_id,$more)){
                $more[$section_id] = $current_time;
            }else{
                array_push($more,[$section_id=>$current_time]);
            }
            $data['more'] = json_encode($more);

            if ($scheModel->where(['id' => $speed['id']])->update($data) !== false) {
                return $this->ok('',12111,'更新进度成功');
            } else {
                return $this->fail(12011,'更新进度失败');
            }
        }

    }
}