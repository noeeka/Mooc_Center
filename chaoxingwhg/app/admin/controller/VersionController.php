<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/21
 * Time: 14:20
 */

namespace app\admin\controller;


use app\admin\model\VersionModel;
use cmf\controller\AdminBaseController;

class VersionController extends AdminBaseController
{
    public function index()
    {
//        $param = $this->request->param();
        $where = '';
        $keyword = $this->request->param('keyword');
        if(!empty($keyword)){
            $where['key']= ['like', "%$keyword%"];
        }
        $version = new VersionModel();
        $versions   = $version->where($where)->select();
        $this->assign('version', $versions);

        return $this->fetch();
    }


    public function add()
    {
        return $this->fetch();
    }


    public function addPost()
    {
        $data      = $this->request->param();
        $version = new VersionModel();
        $result    = $version->validate(true)->allowField(true)->save($data);
        if ($result === false) {
            $this->error($version->getError());
        }

        $this->success("添加成功！", url("version/index"));
    }


    public function edit()
    {
        $id        = $this->request->param('id', 0, 'intval');
        $version = VersionModel::get($id);
        $this->assign('version', $version);
        return $this->fetch();
    }


    public function editPost()
    {
        $data      = $this->request->param();
        $version = new VersionModel();
        $result    = $version->validate(true)->allowField(true)->isUpdate(true)->save($data);
        if ($result === false) {
            $this->error($version->getError());
        }

        $this->success("保存成功！", url("version/index"));
    }

    public function delete()
    {
        $id = $this->request->param('id', 0, 'intval');
        VersionModel::destroy($id);

        $this->success("删除成功！", url("version/index"));
    }


}