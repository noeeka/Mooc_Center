<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class ProductionCollectTypeController extends AdminBaseController
{

    /**
     * 征集类型列表
     */
    public function index()
    {
        $type_list    = Db::name('production_collect_type')->where(['status'=>1])->order('id DESC')->paginate(20);
        $this->assign('list', $type_list);

        return $this->fetch();
    }

    /**
     * 添加征集类型
     */
    public function add()
    {
        return $this->fetch();
    }

    /**
     * 添加征集类型提交保存
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            $data   = $this->request->param();
            if(empty($data['name'])){
                $this->error('名称不能为空');
            }
            Db::name('production_collect_type')->insert($data);

            $this->success('添加成功!', url('production_collect_type/index'));
        }
    }

    /**
     * 编辑征集类型
     */
    public function edit()
    {
        $id        = $this->request->param('id', 0, 'intval');
        $production_collect_type=Db::name('production_collect_type')->where('id',$id)->find();
        $this->assign('type', $production_collect_type);
        return $this->fetch();
    }

    /**
     * 编辑征集类型提交保存
     */
    public function editPost()
    {
        $id      = $this->request->param('id',0,'intval');
        $data['name']=$this->request->param('name','','string');
        $data['status']=$this->request->param('status');
        if(empty($id)){
            $this->error('类型不存在');
        }
        if(empty($data['name'])){
            $this->error('名称不能为空');
        }
        Db::name('production_collect_type')->where('id',$id)->update($data);

        $this->success("保存成功！", url("production_collect_type/index"));
    }

    /**
     * 征集类型删除
     */
    public function delete()
    {
        $id = $this->request->param('id', 0, 'intval');
        if(empty($id)){
            $this->error('类型不存在');
        }
        Db::name('production_collect_type')->where(['id'=>$id])->delete();

        $this->success("删除成功！", url("production_collect_type/index"));
    }
}