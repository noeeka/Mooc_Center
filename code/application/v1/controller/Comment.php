<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/7/31
 * Time: 15:10
 */

namespace app\v1\controller;

use app\v1\model\CenterCourse;
use app\v1\model\Comment as Comments;
use app\v1\model\MoocCenter;
use app\v1\model\Like;
use app\v1\model\MoocUser;

class Comment extends Base
{
    /**
     * 评价列表
     * @param center_id 场馆id
     * @param course_id 课程id
     * @return array|bool
     */
    public function index()
    {
        $center_id = input('param.center_id', 14, 'intval');
        $course_id = input('param.course_id',9,'intval');

        $centerModel = new MoocCenter();
        $center = $centerModel->where('id', $center_id)->find();
        if ($center === null) {
            return $this->fail(21000, '场馆不存在');
        }

        //数据获取
        $commentModel = new Comments();
        $commentList = $commentModel
            ->alias('c')
            ->join('mooc_user mu','mu.id=c.user_id')
            ->where(['c.center_id' => $center_id, 'c.course_id'=>$course_id,'c.status' => 1])
            ->field('c.id,c.content,c.like_num,c.create_time,mu.nick_name,mu.avatar,avg(c.practical_score+c.concise_score+c.clear_score)/3 as comment_score')
            ->group('c.id')
            ->select();

        return $this->ok($commentList,121001,'获取评论列表成功');

    }

    /**
     * 添加评价
     * @param user_token 用户令牌
     * @param course_id 课程id
     * @param content 评价内容
     * @param practical_score 实用分数
     * @param concise_score 简洁分数
     * @param concise_score 清晰分数
     * @return array|bool
     */
    public function create()
    {
        $_GET['user_token'] = 'cd69089bea5af15192e10ce17be7d4939eb02bb7';
        $_GET['timestamp'] = time();
        $salt = 'ttVm5';
        $_GET['sign'] = encrypt_key(['v1/comment/create', $_GET['timestamp'], $_GET['user_token'], $salt], '');
        $_GET['course_id'] = 2;
        $_GET['content'] = '课程内容丰富';

        $user_token = input('param.user_token', '', 'trim');
        $course_id = input('param.course_id', 0, 'intval');
        $content = input('param.content', '', 'trim');
        $practical_score = input('param.practical_score', 0,'intval');
        $concise_score = input('param.concise_score', 0,'intval');
        $clear_score = input('param.clear_score', 0,'intval');

        //令牌校验
        $tokenRes = checkUserToken($user_token);
        if ($tokenRes !== true) {
            return $tokenRes;
        }

        if (empty($course_id)) {
            return $this->fail(20001, '课程id必须');
        }

        if (empty($content)) {
            return $this->fail(12002, '评论内容不能为空');
        }

        // 课程进度不足30%不允许评价
        // ====================================================================


        //数据整理
        $userModel = new MoocUser();
        $user = $userModel->where(['user_token' => $user_token])->find();
        $data = [
            'user_id' => $user['id'],
            'center_id' => $user['center_id'],
            'course_id' => $course_id,
            'content' => $content,
            'create_time' => time(),
            'practical_score' => $practical_score,
            'concise_score' => $concise_score,
            'clear_score' => $clear_score,
        ];

        if ((new Comments())->save($data) > 0) {
            return $this->ok('', 12103, '添加评论成功');
        } else {
            return $this->fail(12004, '添加评论失败');
        }

    }

    /**
     * 删除评论
     * @param user_token 用户令牌
     * @param $comment_id 笔记id
     * @return array|bool
     */
    public function delete()
    {
        $_GET['user_token'] = '54743ce1e84beb71df2f65b357912ed6a3726341';
        $_GET['timestamp'] = time();
        $salt = '0U07J';
        $_GET['sign'] = encrypt_key(['v1/comment/delete', $_GET['timestamp'], $_GET['user_token'], $salt], '');

        $user_token = input('param.user_token', '', 'trim');
        $comment_id = input('param.id', 7, 'intval');

        //令牌校验
        $tokenRes = checkUserToken($user_token);
        if ($tokenRes !== true) {
            return $tokenRes;
        }

        //校验笔记是否属于此用户
        $userModel = new MoocUser();
        $commentModel = new Comments();
        $user = $userModel->where(['user_token' => $user_token])->find();
        $comment = $commentModel->where(['id' => $comment_id])->find();
        if ($comment === null) {
            return $this->fail(20040, '笔记不存在');
        }
        if ($comment['user_id'] != $user['id']) {
            return $this->fail(20041, '笔记不属于此用户，没有权限删除');
        }

        if ($commentModel->where('id', $comment_id)->update(['delete_time'=>time()]) !== false) {
            return $this->ok('', 20142, '删除成功');
        } else {
            return $this->fail(20043, '删除失败');
        }
    }


    /**
     * 点赞评价
     * @param user_token 用户令牌
     * @param comment_id 评论id
     * @param type 点赞类型 1 评论点赞  2 笔记点赞
     * @return array|bool
     */
    public function like()
    {
        $_GET['user_token'] = 'b0ce7a263c01dfa88c681b3eba8d99a0b0554c0c';
        $_GET['timestamp'] = time();
        $salt = 'UM9Cg';
        $_GET['sign'] = encrypt_key(['v1/comment/like', $_GET['timestamp'], $_GET['user_token'], $salt], '');
        $_GET['comment_id'] = 7;

        $user_token = input('param.user_token', '', 'trim');
        $comment_id = input('param.comment_id', 0, 'intval');
        $type = input('param.type', 1, 'intval');

        //令牌校验
        $tokenRes = checkUserToken($user_token);
        if ($tokenRes !== true) {
            return $tokenRes;
        }

        if (empty($comment_id)) {
            return $this->fail(12010, '评价id必须');
        }

        //数据校验
        $userModel = new MoocUser();
        $likeModel = new Like();
        $commentModel = new Comments();
        $user = $userModel->where(['user_token' => $user_token])->find();
        $comment = $commentModel->where(['id' => $comment_id])->find();
        if ($comment === null) {
            return $this->fail(20051, '笔记不存在');
        }

        //校验是否已点赞
        $is_like = $likeModel->where(['user_id'=>$user['id'],'resource_id'=>$comment_id,'type'=>$type])->find();
        if($is_like){
            return $this->fail(20052,'已点赞');
        }

        //数据整理
        $data = [
            'user_id' => $user['id'],
            'resource_id' => $comment_id,
            'type' => $type,
            'create_time' => time()
        ];
        if ($likeModel->save($data) > 0) {
            $commentModel->where(['id'=>$comment_id])->setInc('like_num');
            return $this->ok('', 20152, '评论点赞成功');
        } else {
            return $this->fail(230053, '评论点赞失败');
        }
    }

    /**
     * 综合评分
     * @param course_id 课程id
     * @param center_id 场馆id
     * @return array|bool
     */
    public function score(){
        $course_id = input('param.course_id',9,'intval');
        $center_id = input('param.center_id',14,'intval');

        //数据校验
        if(empty($center_id)){
            return $this->fail(23009,'场馆id必须');
        }else{
            if(!((new MoocCenter())->where('id',$center_id)->find())){
                return $this->fail(23007,'场馆不存在');
            }
        }
        if(!empty($course_id)){
             $cenCourseModel = new CenterCourse();
             $is_exist = $cenCourseModel->where(['center_id'=>$center_id,'course_id'=>$course_id])->find();
             if(!$is_exist){
                 return $this->fail(23008,'课程不存在或者场馆下无此课程');
             }
        }else{
            return $this->fail(23010,'课程id必须');
        }

        //数据获取
        $commentModel = new Comments();
        $score = $commentModel
            ->where(['course_id'=>$course_id,'center_id'=>$center_id])
            ->field("avg(practical_score) as practical_score,avg(concise_score) as concise_score,avg(clear_score) as clear_score,avg(practical_score+concise_score+clear_score)/3 as globalScore")
            ->group('course_id')
            ->find();

        return $this->ok($score,23111,'获取综合评分成功');
    }

}