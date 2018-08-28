<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/8/23
 * Time: 17:13
 */

namespace app\v1\controller;

use app\v1\model\Course;

class Search extends Base{
    public function index(){
        $title = input('param.title','','trim');
        $center_id = input('param.center_id',0,'intval');

        if(empty($title)){
            return $this->fail(10001,'搜索内容不能为空');
        }

        $where = [];
        $where['cc.center_id'] = $center_id;
        $where['c.course_title'] = ['like',"%$title%"];

        $course = (new Course())->alias('c')
            ->join('__CENTER_COURSE__ cc', 'c.id=cc.course_id', 'left')
            ->join('__COURSE_RELA__ ct', 'c.id=ct.course_id  and ct.center_id = cc.center_id and ct.type=1', 'left')
            ->join('__COMMENT__ cm', 'c.id=cm.course_id and cm.center_id = cc.center_id', 'left')
            ->field('c.*,cc.id as real_id,cc.recommend,cc.create_time as add_time,cc.center_id,cc.play_num,cc.status, ct.other_id as type_id,avg(cm.practical_score+cm.concise_score+cm.clear_score)/3 as score')
            ->where($where)
            ->group('c.id')
            ->select();

        return $this->ok($course,61111,'获取课程成功');
    }
}