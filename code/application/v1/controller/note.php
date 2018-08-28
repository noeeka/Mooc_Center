<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/7/30
 * Time: 10:23
 */

namespace app\v1\controller;

use app\v1\model\MoocUser;

class Note extends Base
{
    /**
     * 笔记列表
     * @param center_token 场馆令牌
     * @param
     * @param
     * @param
     * @param
     * @return array|bool
     */
    public function index()
    {

    }

    /**
     * 添加笔记
     * @param center_token 场馆令牌
     * @param course_id 课程id
     * @param section_id 节id
     * @param content 笔记内容
     * @param
     * @return array|bool
     */
    public function create()
    {
        $user_token = input('param.user_token', '', 'trim');
        $course_id = input('param.course_id', 0, 'intval');
        $section_id = input('param.section_id', 0, 'intval');
        $content = input('param.content', '', 'trim');

        //用户校验
        $tokenRes = checkUserToken($user_token);
        if ($tokenRes !== true) {
            return $tokenRes;
        }

        //数据校验
        if (empty($course_id)) {
            return $this->fail(20020, '课程id必须');
        }

        if (empty($section_id)){
            return $this->fail(20020, '节id必须');
        }

        if (empty($content)){
            return $this->fail(20020, '笔记内容必须');
        }

        $userModel = new MoocUser();
        $user = $userModel->where(['user_token' => $user_token])->find();
        $user_id = $user->id;
        $center_id = $user->center_id;


    }
}