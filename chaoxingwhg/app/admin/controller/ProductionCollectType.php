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

            $typeModel = new ActivityTypeModel();
            $result    = $typeModel->validate(true)->allowField(true)->save($data);
            if ($result === false) {
                $this->error($typeModel->getError());
            }
            $this->success('添加成功!', url('ActivityType/index'));
        }
    }

    /**
     * 编辑活动类型
     */
    public function edit()
    {
        $id        = $this->request->param('id', 0, 'intval');
        $activity_type = ActivityTypeModel::get($id);
        $this->assign('type', $activity_type);
        return $this->fetch();
    }

    /**
     * 编辑活动类型提交保存
     */
    public function editPost()
    {
        $data      = $this->request->param();
        $typeModel = new ActivityTypeModel();
        $result    = $typeModel->validate(true)->allowField(true)->isUpdate(true)->save($data);
        if ($result === false) {
            $this->error($typeModel->getError());
        }

        $this->success("保存成功！", url("ActivityType/index"));
    }

    /**
     * 活动类型删除
     */
    public function delete()
    {
        $id = $this->request->param('id', 0, 'intval');
        ActivityTypeModel::destroy($id);

        $this->success("删除成功！", url("ActivityType/index"));
    }
}