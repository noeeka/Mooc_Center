<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <zxxjjforever@163.com>
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\admin\model\SysMessageModel;
use cmf\controller\AdminBaseController;
use app\admin\model\CommentsModel;
use think\DB;
use app\admin\model\UserModel;


class CommentsController extends AdminBaseController
{
    /*
     * 分类列表
     */
    public function index()
    {
        //获取分类列表
        //1.获取父级评论(集合对象）
        $commentsModel = new CommentsModel;
        $parentlist = Db::name('comments')->alias('c')->where('level_pid', '=', 0)
            ->order('create_time DESC')->join('portal_post p', 'c.articleid=p.id', 'left')
            ->join('user u', 'c.userid=u.id')
            ->field('c.*,p.post_title as title,u.user_nickname')->paginate(10);




        //将父类转化为二维数组
        $parentArr = $parentlist->toArray();
        $parentArr = $parentArr['data'];

        //获取分页显示
        $parentlist->appends($parentArr);
        $page = $parentlist->render();

        $endArr = [];
        //获取下一级分类
        foreach ($parentArr as $key => $value) {
            $sonlist = Db::name('comments')->alias('c')->where('level_pid', '=', $value['id'])
                ->order('id asc')->join('portal_post p', 'c.articleid=p.id', 'left')
                ->join('user u', 'c.userid=u.id')
                ->field('c.*,p.post_title as title,u.user_nickname')->select();

            $sonArr = [];
            $sonArr = $sonlist->toArray();

            $num = count($sonArr);
            for ($i = 0; $i < $num; $i++) {
                $sonArr[$i]['is_admin'] = $sonArr[$i]['userid'] != $value['userid'] ? 1 : 0;
                if ($i == ($num - 1)) {
                    $sonArr[$i]['comment'] = '&nbsp;&nbsp;&nbsp;&nbsp;└─ ' . $sonArr[$i]['comment'];
                } else {
                    $sonArr[$i]['comment'] = '&nbsp;&nbsp;&nbsp;&nbsp;├─' . $sonArr[$i]['comment'];
                }
            }
            $parentArr[$key]['son'] = $sonArr;

        }

        $endArr = $parentArr;

        $this->assign('endArr', $endArr);
        $this->assign("page",$page);
        return $this->fetch();
    }

    public function reply()
    {
        $id = $this->request->param('id');
        $comment_info = Db::name('comments')->alias('c')->where('c.id', '=', $id)
            ->order('updatetime DESC')->join('portal_post p', 'c.articleid=p.id', 'left')
            ->join('user u', 'c.userid=u.id')
            ->field('c.*,p.post_title as title,u.user_nickname')
            ->find();
        $this->assign('array', $comment_info);
        return $this->fetch();
    }

    public function replyDo()
    {
        //接收数据
        $id = input('param.id1');
        $com = input('param.comment','','trim');
        $content = input('param.content', '', 'trim');
        if($com == ''){
            $this->error('评论不能为空');
        }
        if($content == ''){
            $this->error('回复不能为空');
        }
        $commentModel = new CommentsModel;
        $comment = $commentModel->where('id', $id)->find();

        $data['articleid'] = $comment['articleid'];
        $data['userid'] = cmf_get_current_admin_id();
        $data['replyid'] = $comment['userid'];
        $data['updatetime'] = time();
        $data['create_time'] = time();
        $data['parentid'] = $id;
        $data['comment'] = $content;
        $pid = $comment['level_pid'];
        if ($pid == 0) {
            $data['level_pid'] = $comment['id'];
        } else {
            $data['level_pid'] = $pid;
        }


        //将数据存到数据库
        $result = $commentModel->validate(true)->allowField(true)->save($data);
        $commentModel->where('id',$id )->update(['status' => 1,'comment'=>$com]);
        if ($result) {
            $sys = new SysMessageModel();
            $id = $commentModel->id;
            $sys->publish($id, 1);
            $this->success('保存回复成功', 'comments/index');
        } else {
            $this->error($commentModel->getError());
        }
    }

    public function toggle()
    {
        $data = $this->request->param();
        $commentsModel = new CommentsModel;
        if (isset($data['ids']) && !empty($data["display"])) {
            $ids = $this->request->param('ids/a');
            $commentsModel->where(['id' => ['in', $ids]])->update(['status' => 1]);
            $this->success("更新成功！");
        }

        if (isset($data['ids']) && !empty($data["hide"])) {
            $ids = $this->request->param('ids/a');
            $commentsModel->where(['id' => ['in', $ids]])->update(['status' => 0]);
            $this->success("更新成功！");
        }
    }

    //管理员回复内容的修改
    public function edit()
    {
        //接收评论id
        $id = $this->request->param('id');
        $array = Db::name('comments')->alias('c')->where('c.id', '=', $id)
            ->order('updatetime DESC')->join('portal_post p', 'c.articleid=p.id', 'left')
            ->join('user u', 'c.userid=u.id')
            ->field('c.*,p.post_title as title,u.user_nickname')
            ->find();
        //print_r($array);die;
        $parentid = $array['parentid'];
        //echo $replyid;die;
        $commer = Db::name('comments')->alias('c')->where('c.id', '=', $parentid)
            ->order('updatetime DESC')->join('portal_post p', 'c.articleid=p.id', 'left')
            ->join('user u', 'c.userid=u.id')
            ->field('c.*,p.post_title as title,u.user_nickname')
            ->find();
        //print_r($commer);die;
        $this->assign('commer', $commer);
        $this->assign('array', $array);
        return $this->fetch();


    }

    public function editDo()
    {
        //接受数据
        $data = $this->request->param();
        $com = input('param.comment', '', 'trim');
        $comment = input('param.reply', '', 'trim');
        $status = input('param.status', 0, 'intval');
        $id = input('param.id', 0, 'intval');
        $status = $status != 0 ? 1 : 0;
        if($com == ''){
            $this->error('评论不能为空');
        }
        if($comment == ''){
            $this->error('回复不能为空');
        }

        $commentsModel = new CommentsModel;
        $commentpid = $commentsModel->where('id',$id)->find();
        $level_pid = $commentpid['level_pid'];
        //将数据更新到数据库
        $commentresult = $commentsModel->where('id',$level_pid)->update(['comment'=>$com]);
        $replyresult = $commentsModel->where('id', $id)->update(['comment' => $comment, 'status' => $status, 'updatetime' => time()]);
        if ($replyresult && $commentresult) {
            $this->success('修改成功', 'comments/index');
        } else {
            $this->error($commentsModel->getError());
        }

    }

    public function update()
    {
        //接收id
        $id = $this->request->param('id');
        $comment_info = Db::name('comments')->alias('c')->where('c.id', '=', $id)
            ->order('updatetime DESC')->join('portal_post p', 'c.articleid=p.id', 'left')
            ->join('user u', 'c.userid=u.id')
            ->field('c.*,p.post_title as title,u.user_nickname')
            ->find();
        $this->assign('comment_info', $comment_info);
        return $this->fetch();

    }

    public function updateDo()
    {
        $data = $this->request->param();
        $id = input('param.id', 0, 'intval');
        $status = input('param.status', 0, 'intval');
        $status = $status != 0 ? 1 : 0;
        $comment = input('param.comment', 0, 'trim');
        if($comment == ''){
            $this->error('评论不能为空');
        }
        $commentsModel = new CommentsModel;
        $result = $commentsModel->where('id', '=', $id)->update(['comment' => $comment, 'status' =>$status]);
        if ($result !== false) {
            $this->success('修改评论成功', 'comments/index');
        } else {
            $this->error($commentsModel->getError());
        }

    }

    public function delete()
    {
        //接受id
        $id = $this->request->param('id');
        $commentsModel = new CommentsModel;
        $result = $commentsModel->where('id', '=', $id)->delete();

        if ($result) {
            $this->success('回复删除成功', '');
        } else {
            $this->error($commentsModel->getError());
        }
    }


}