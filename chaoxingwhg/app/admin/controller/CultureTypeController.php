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

use app\admin\model\PerformTypeModel;
use cmf\controller\AdminBaseController;

class CultureTypeController extends AdminBaseController
{
    const PERFORM_TYPE = 1;
    /**
     * 活动类型列表
     */
    public function index()
    {
        $typeModel = new PerformTypeModel();
        $type_list    = $typeModel->where('type', self::PERFORM_TYPE)->order('id DESC')->paginate(20);

        $this->assign('list', $type_list);

        return $this->fetch();
    }

    /**
     * 添加活动类型
     */
    public function add()
    {
        return $this->fetch();
    }

    /**
     * 添加活动类型提交保存
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            $data   = $this->request->param();
            $data['type'] = self::PERFORM_TYPE;
            $typeModel = new PerformTypeModel();
            $data['update_time'] = time();
            $data['create_time'] = time();
            $result    = $typeModel->validate('ActivityType')->allowField(true)->save($data);
            if ($result === false) {
                $this->error($typeModel->getError());
            }
            $this->success('添加成功!', url('CultureType/index'));
        }
    }

    /**
     * 编辑活动类型
     */
    public function edit()
    {
        $id        = $this->request->param('id', 0, 'intval');
        $activity_type = PerformTypeModel::get($id);
        $this->assign('type', $activity_type);
        return $this->fetch();
    }

    /**
     * 编辑活动类型提交保存
     */
    public function editPost()
    {
        $data      = $this->request->param();
        $data['type'] = self::PERFORM_TYPE;
        $typeModel = new PerformTypeModel();
        $result    = $typeModel->validate('ActivityType')->allowField(true)->isUpdate(true)->save($data);
        if ($result === false) {
            $this->error($typeModel->getError());
        }

        $this->success("保存成功！", url("CultureType/index"));
    }

    /**
     * 活动类型删除
     */
    public function delete()
    {
        $id = $this->request->param('id', 0, 'intval');
        PerformTypeModel::destroy($id);

        $this->success("删除成功！", url("CultureType/index"));
    }
}