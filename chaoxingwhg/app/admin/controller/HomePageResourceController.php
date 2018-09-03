<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/5/22
 * Time: 15:07
 */

namespace app\admin\controller;

use app\admin\model\HomePageResourceModel;
use cmf\controller\AdminBaseController;

class HomePageResourceController  extends AdminBaseController
{
    //资源列表
    public function index(){
        $homePageResModel = new HomePageResourceModel();
        $resource_list = $homePageResModel->paginate(10);
        $page = $resource_list->render();

        $this->assign('resource_list',$resource_list->items());
        $this->assign('page',$page);

        return $this->fetch();
    }

    //
    public function add(){
        return $this->fetch();
    }

    //添加资源
    public function addPost(){
        $data = $this->request->param();

        $resourceModel = new HomePageResourceModel();
        $result = $resourceModel->validate(true)->save($data);

        if($result === false){
            $this->error($resourceModel->getError());
        }

        $this->success('添加成功','home_page_resource/index');
    }

    public function edit(){
        $id = $this->request->param('id');

        $resource = HomePageResourceModel::get($id);

        $this->assign('resource',$resource);
        return $this->fetch();

    }

    public function editPost(){
        $data = $this->request->param();

        $resModel = new HomePageResourceModel();
        $result = $resModel->validate(true)->allowField(true)->isUpdate(true)->save($data);

        if($result === false){
            $this->error($resModel->getError());
        }

        $this->success('修改成功','home_page_resource/index');

    }

    public function delete(){
        $id = $this->request->param('id',0,'intval');
        HomePageResourceModel::destroy($id);

        $this->success("删除成功！", url("home_page_resource/index"));
    }
}
