<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/7
 * Time: 15:44
 */

namespace app\api\controller;


use app\admin\model\CommentsModel;
use app\admin\model\PortalPostModel;

class CommentsController extends Base
{
    public function index()
    {
        $page = input('param.page', 1);
        $len = input('param.len', 10);
        $articleid = input('param.articleid', 0, 'intval');
        $comments = new CommentsModel();
        $parent = $comments->alias('a')->where(['a.parentid' => 0, 'a.status' => 1, 'a.articleid' => $articleid])
            ->page($page, $len)
            ->join('user u', 'u.id =a.userid')
            ->field('a.*,u.user_nickname,u.avatar')
            ->order(['a.create_time' => 'desc'])
            ->select()
            ->toArray();
        $num = $comments->alias('a')->where(['a.parentid' => 0, 'a.status' => 1, 'a.articleid' => $articleid])
            ->join('user u', 'u.id =a.userid')
            ->field('a.*,u.user_nickname')->count(1);
        $res = array();
        foreach ($parent as $value) {
            $id = $value['id'];
            $value['avatar_url'] = cmf_get_image_preview_url($value['avatar']);
            $value['list'] = $comments->alias('c')->where(['c.level_pid' => $id])
                ->where(['c.status' => 1])
                ->join('user d', 'd.id = c.userid')
                ->field('c.*,d.user_nickname,d.avatar')
                ->order(['c.create_time' => 'ASC'])
                ->select();
            $res[] = $value;
        }
        return $this->output_success(11101, ['list' => $res, 'num' => $num], '获取列表成功');
    }

    public function comment($param = [])
    {
        $uid = $this->getuid();
        $id = input('parentid', 0);
        $articleid = input('articleid', 0);
        $content = input('content', '');
        if (empty($content)) {
            return $this->output_error(13006, '评论内容不能为空');
        }
        $data = ['articleid' => $articleid, 'userid' => $uid, 'comment' => $content, 'updatetime' => time(), 'create_time' => time(), 'status' => 0];
        $comment = new CommentsModel();
        if ($id == 0) {
            $data['parentid'] = 0;
            $data['level_pid'] = 0;
        } else {
            $data['parentid'] = $id;
            $com = $comment->where('id', $id)->find();
            if (empty($com)) {
                return $this->output_error(12000, '评论失败');
            }
            $com = $com->toArray();
            $data['level_pid'] = $com['parentid'] == 0 ? $com['id'] : $com['level_pid'];
        }
        $comment->save($data);
        PortalPostModel::where(['id' => $articleid])->setInc('comment_count');
        return $this->output_success(12101, [], '评论成功');
    }

    public function delete($param = [])
    {
        $uid = $this->getuid();
        $parentid = input('parentid', 0);
        $comment = new CommentsModel();
        $res = $comment->where(function ($query) use ($parentid, $uid) {
            $query->where(['id' => $parentid, 'userid' => $uid]);
        })->whereor(function ($query) use ($parentid) {
            $query->where(['parentid' => $parentid]);
        })->delete();

        if ($res) {
            return $this->output_success(11101, $res, '删除成功');
        } else {
            return $this->output_error(11100, $res, '删除失败');
        }


    }

}