<?php
/**
 * Created by PhpStorm.
 * User: 12563
 * Date: 2018/6/11
 * Time: 14:47
 */

namespace app\admin\controller;

use app\admin\model\HomePageLinkTypeModel;
use cmf\controller\AdminBaseController;

class HomePageLinkTypeController extends AdminBaseController
{

    public function index()
    {
        $homePageFeiyi = (new HomePageLinkTypeModel())->select()->toArray();
        $this->assign('home_page_link', $homePageFeiyi);
        return $this->fetch();
    }

    public function add()
    {
        return $this->fetch();
    }

    public function addPost()
    {
        $name = $this->request->param('name', '', 'trim');
        if(empty($name)){
            $this->error('名称不能为空');
        }
        $homePageFeiyiModel = new HomePageLinkTypeModel();
        $homePageFeiyiModel->insert(['name'=>$name]);
        $this->success('添加成功', url('HomePageLinkType/index'));
    }

    public function edit()
    {
        $id = input('param.id', 0, 'intval');
        $feiyi = HomePageLinkTypeModel::get($id);
        if ($feiyi == null) {
            $this->error('内容不存在');
        } else {
            $this->assign($feiyi->toArray());
            return $this->fetch();
        }
    }

    public function editPost()
    {
        $name = $this->request->param('name', '', 'trim');
        $id = $this->request->param('id', 0, 'intval');
        if(empty($name)){
            $this->error('名称不能为空');
        }
        $homePageFeiyiModel = new HomePageLinkTypeModel();
        $ret = $homePageFeiyiModel->where('id', $id)->update(['name'=>$name]);
        if ($ret === false) {
            $this->error('编辑失败');
        } else {
            $this->success('编辑成功', url('HomePageLinkType/index'));
        }
    }

    public function delete()
    {
        $ids = input('ids/a');
        $id = input('id', 0, 'intval');
        if(!empty($ids)){
            HomePageLinkTypeModel::where(['id' => ['in', $ids]])->delete();
            $this->success('删除成功');
        }elseif($id != 0){
            HomePageLinkTypeModel::where(['id' => $id])->delete();
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }

    }

    public function listOrder()
    {
        $model = new HomePageLinkTypeModel();
        $ret = parent::listOrders($model);
        if ($ret) {
            $this->success('排序成功');
        } else {
            $this->error('排序失败');
        }
    }
}