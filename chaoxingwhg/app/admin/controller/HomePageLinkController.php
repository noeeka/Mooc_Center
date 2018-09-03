<?php
/**
 * Created by PhpStorm.
 * User: 12563
 * Date: 2018/6/11
 * Time: 14:47
 */

namespace app\admin\controller;

use app\admin\model\HomePageLinkModel;
use app\admin\model\HomePageLinkTypeModel;
use cmf\controller\AdminBaseController;

class HomePageLinkController extends AdminBaseController
{

    public function index()
    {
        $homePageFeiyi = (new HomePageLinkModel())->order('list_order asc')->select();
        $this->assign('home_page_link', $homePageFeiyi);
        return $this->fetch();
    }

    public function add()
    {
        $types = HomePageLinkTypeModel::option();
        $this->assign('types', $types);
        return $this->fetch();
    }

    public function addPost()
    {
        $data = $this->request->param();
        $HomePageLinkModel = new HomePageLinkModel();
        $data['url'] = htmlspecialchars_decode($data['url']);
        $data['create_time'] = time();
        $ret = $HomePageLinkModel->validate(true)->allowField(true)->save($data);
        if (!$ret) {
            $this->error($HomePageLinkModel->getError());
        } else {
            $this->success('添加成功', url('HomePageLink/index'));
        }

    }

    public function edit()
    {
        $id = input('param.id', 0, 'intval');
        $feiyi = HomePageLinkModel::get($id);
        if ($feiyi == null) {
            $this->error('内容不存在');
        } else {
            $types = HomePageLinkTypeModel::option($feiyi->type);
            $this->assign($feiyi->toArray());
            $this->assign('types', $types);
            return $this->fetch();
        }
    }

    public function editPost()
    {
        $data = $this->request->param();
        $data['url'] = htmlspecialchars_decode($data['url']);
        $HomePageLinkModel = new HomePageLinkModel();
        $ret = $HomePageLinkModel->validate(true)->allowField(true)->save($data, ['id'=>$data['id']]);
        if ($ret === false) {
            $this->error($HomePageLinkModel->getError());
        } else {
            $this->success('编辑成功', url('HomePageLink/index'));
        }
    }

    public function delete()
    {
        $ids = input('ids/a');
        $id = input('id', 0, 'intval');
        if (!empty($ids)) {
            HomePageLinkModel::where(['id' => ['in', $ids]])->delete();
            $this->success('删除成功');
        } elseif ($id != 0) {
            HomePageLinkModel::where(['id' => $id])->delete();
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    public function listOrder()
    {
        $model = new HomePageLinkModel();
        $ret = parent::listOrders($model);
        if ($ret) {
            $this->success('排序成功');
        } else {
            $this->error('排序失败');
        }
    }
}