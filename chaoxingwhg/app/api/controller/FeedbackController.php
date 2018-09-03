<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/1
 * Time: 16:54
 */

namespace app\api\controller;


use app\admin\model\FeedbackModel;
use app\admin\validate\FeedbackValidate;

class FeedbackController extends Base
{
    public function index($param = [])
    {
        $feedModel = new FeedbackModel();
      
        $res=$feedModel->select();
        if($res){
            return $this->output_success(11001, $res, '获取意见列表成功');
        }else{
            return $this->output_error(11002, '无意见');
        }
    }
    public function save(){
        $name = input('name','','trim');
        $mobile = input('mobile','','trim');
        $qq = input('qq','','trim');
        $sex = input('sex',0,'intval');
        $content = input('content','','trim');
        $created_at = date('Y-m-d H:i:s');
        $data = array('name'=>$name,'mobile'=>$mobile,'content'=>$content,'created_at'=>$created_at, 'qq'=>$qq, 'sex'=>$sex);
        $feedback = new FeedbackModel();
        $result = $feedback->validate(true)->save($data);

        if($result){
            return $this->output_success(17101,'', '意见发送成功');
        }else{
            return $this->output_error(17001, $feedback->getError());
        }



    }

    public function read(){
        $id = input('id','','intval');
        $feedback = new FeedbackModel();
        $result = $feedback->where('id',$id)->select();
        if($result){
            return $this->output_success('16101',$result,'意见读取成功');
        }else{
            return $this->output_error('16102','','意见读取失败');
        }
    }
}