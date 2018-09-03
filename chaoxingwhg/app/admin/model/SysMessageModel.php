<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use app\admin\model\RouteModel;
use think\Model;
use tree\Tree;

class SysMessageModel extends Model
{
    public function publish($id, $type, $to=0 , $from=0, $content='', $title='系统消息', $url=''){
        switch ($type){
            case 1:
                //文章评论回复
                $commentModel = new CommentsModel();
                $comment = $commentModel
                    ->alias('a')
                    ->join('__PORTAL_POST__ b', 'b.id=a.articleid')
                    ->field('b.id as article_id,b.post_title,a.*')
                    ->where('a.id', $id)
                    ->find()->toArray();
                if(empty($comment)){
                    return false;
                }else{
                    $parent = $commentModel->where('id', $comment['parentid'])->find();
                    $title = '管理员在《'.$comment['post_title'].'》中回复了您的评论:“'.$parent['comment'].'”';
                    $content = json_encode(['my'=>$parent['comment'], 'reply'=>$comment['comment']]);
                    $from_id = cmf_get_current_admin_id();
                    $rid = $id;
                    $to_id = $parent['userid'];
                    $url =  '/portal/category/read/?id='.$comment['article_id'];
                }
                break;
            case 2:
                //意见反馈回复
//                $feedbackModel = new FeedbackModel();
//                $feedback = $feedbackModel->where('id', $id)->find();
//                if(empty($feedback)){
//                    return false;
//                }else{
//                    if($title == ''){
//                        $title = '管理员回复了您的意见“'.$feedback['content'].'”';
//                    }
//                    if($content = ''){
//                        $content = $feedback['reply_content'];
//                    }
//                    $from_id = $from == 0 ? 0 : $from;
//                    $rid = $id;
//                    $to_id = $feedback['user_id'];
//                    $url =  $url == '' ? '/wb/site/read/id/'.$feedback['article_id'];
//                }
                break;
            default:
                break;
        }
        $this->save([
            'to_id'=>$to_id,
            'from_id'=>$from_id,
            'rid'=>$rid,
            'content'=>$content,
            'type'=>$type,
            'title'=>$title,
            'url'=>$url,
            'is_read'=>0,
            'create_time'=>time(),
        ]);
        return true;
    }
}