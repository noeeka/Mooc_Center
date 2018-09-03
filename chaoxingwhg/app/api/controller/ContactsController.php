<?php

namespace app\api\controller;

use app\admin\model\ContactsModel;
use think\Controller;
use think\Request;

class ContactsController extends Base
{
     public function addcontacts() {

         $this->check_sign();

         $token = input('param.token');
         $token_info = db('token')->where('token', $token)->field('user_id')->find();

         $user_id = 0;
         if (isset($token_info['user_id']) && !empty($token_info['user_id']))
             $user_id = $token_info['user_id'];
         else
             return $this->output_error(13006, '获取用户信息失败');

         $param = array();
         $param['type'] = input('type', 0);
         $param['name'] = urldecode(input('name', ''));
         $param['id_card'] = input('id_card', '');
         $param['mobile'] = input('mobile', '');
         $param['guardian'] = urldecode(input('guardian', ''));
         $param['user_id'] = $user_id;
         $param['create_time'] = time();
         $param['status'] = 1;

         $contactsValidate = validate('contacts');
         if(!$contactsValidate->check($param)) {
             return $this->output_error(0 , $contactsValidate->getError());
         }

         $contactsModel = new ContactsModel();
         $contactsResult = $contactsModel->where([
             "user_id" => $user_id ,
             "id_card" => $param['id_card'],
             "status" => 1
         ])->find();

         if ($contactsResult) {
             return $this->output_error(13006, '联系人已经添加');
         }

         $contactsResult = $contactsModel->insert($param);

         if ($contactsResult)
             return $this->output_success(200, '创建联系人成功');
         else
             return $this->output_error(13006, '创建联系人失败');
     }

     public function getContacts() {

         $token = input('param.token');
         $token_info = db('token')->where('token', $token)->field('user_id')->find();

         $user_id = 0;
         if (isset($token_info['user_id']) && !empty($token_info['user_id']))
             $user_id = $token_info['user_id'];
         else
             return $this->output_error(13006, '获取用户信息失败');

         $param = array();
         $param['page'] = input('page', 1);
         $param['size'] = min( input('size', '10') , 30);

         $contactsModel = new ContactsModel();
         $contactsResult = $contactsModel->where([
             "user_id" => $user_id ,
             "status" => 1
         ])->select();

         return  $this->output_success( 200 , $contactsResult );
     }

     public function contactsinfo() {
         $this->check_sign();

         $token = input('param.token');
         $token_info = db('token')->where('token', $token)->field('user_id')->find();

         $user_id = 0;
         if (isset($token_info['user_id']) && !empty($token_info['user_id']))
             $user_id = $token_info['user_id'];
         else
             return $this->output_error(13006, '获取用户信息失败');

         $param = array();
         $param['id'] = input('id', 1);
         $contactsModel = new ContactsModel();
         $contactsResult = $contactsModel->where([
             "user_id" => $user_id ,
             "id" => $param['id'],
             "status" => 1
         ])->find();

         if ($contactsResult)
             return $this->output_success(200, $contactsResult);
         else
             return $this->output_error(13006, '查询失败');

     }

     public function modifycontacts()
     {
         $this->check_sign();
         $token = input('param.token');
         $token_info = db('token')->where('token', $token)->field('user_id')->find();
         $user_id = 0;
         if (isset($token_info['user_id']) && !empty($token_info['user_id']))
             $user_id = $token_info['user_id'];
         else
             return $this->output_error(13006, '获取用户信息失败');

         $param = array();
         $where = array();
         $where['id'] = input('id', 0);
         $param['type'] = input('type', 0);
         $param['name'] = urldecode(input('name', ''));
         $param['id_card'] = input('id_card', '');
         $param['mobile'] = input('mobile', '');
         $param['guardian'] = urldecode(input('guardian', ''));
         $where['user_id'] = $user_id;

         $contactsValidate = validate('contacts');
         if(!$contactsValidate->check($param)) {
             return $this->output_error(0 , $contactsValidate->getError());
         }

         $contactsModel = new ContactsModel();
         $contactsResult = $contactsModel->where($where)->update($param);

         if ($contactsResult)
             return $this->output_success(200, '修改联系人成功');
         else
             return $this->output_error(13006, '未修改联系人信息');
     }

     public function delcontacts() {
         $this->check_sign();
         $token = input('param.token');
         $token_info = db('token')->where('token', $token)->field('user_id')->find();
         $user_id = 0;
         if (isset($token_info['user_id']) && !empty($token_info['user_id']))
             $user_id = $token_info['user_id'];
         else
             return $this->output_error(13006, '获取用户信息失败');

         $param = array();
         $where = array();
         $where['id'] = input('id', 0);
         $param['status'] = 0;
         $where['user_id'] = $user_id;
         
         $contactsModel = new ContactsModel();
         $contactsResult = $contactsModel->where($where)->update($param);

         if ($contactsResult)
             return $this->output_success(200, '删除联系人成功');
         else
             return $this->output_error(13006, '删除联系人失败');
     }
}
