<?php

namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use app\admin\model\VenueTypeModel;

class VenueTypeController extends AdminBaseController
{

    /**
     * 总分馆类型列表
     */
    public function index()
    {
        $VenueTypeModel = new VenueTypeModel();
        $list = $VenueTypeModel->select();
        $this->assign('list', $list);

        return $this->fetch();
    }

    /**
     * 新增总分管类型
     */
    public function add()
    {
        return $this->fetch();
    }

    /**
     * 提交总分管类型
     */
    public function addPost()
    {
        $name = $this->request->param('name');
        if(empty($name)){
            $this->error('名称不能为空!');
        }else{
            $VenueTypeModel = new VenueTypeModel();

            $result = $VenueTypeModel->save(['name'=>$name]);

            if ($result === false) {
                $this->error('添加失败!');
            }

            $this->success('添加成功!', url('venue_type/index'));
        }
    }

    /**
     * 编辑区域
     */
    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');
        if ($id > 0) {
            $venue_type = VenueTypeModel::get($id)->toArray();
            $this->assign('venue_type', $venue_type);
            $this->assign('id', $id);
            return $this->fetch();
        } else {
            $this->error('操作错误!');
        }
    }

    /**
     * 编辑区域提交保存
     */
    public function editPost()
    {
        $data = $this->request->param();

        $VenueTypeModel = new VenueTypeModel();
        $result = $VenueTypeModel->where(['id' => $data['id']])->update(['name' => $data['name']]);
        if ($result === false) {
            $this->error('保存失败!');
        }

        $this->success('保存成功!');
    }


    /**
     * 删除区域
     */
    public function delete()
    {
        $id = $this->request->param('id', 0, 'intval');
        VenueTypeModel::destroy($id);

        $this->success("删除成功！", url("venue_type/index"));
    }


}