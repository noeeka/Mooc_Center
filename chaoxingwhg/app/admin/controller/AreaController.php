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
use app\admin\model\AreaModel;
use app\admin\model\UserModel;
use tree\Tree;

class AreaController extends AdminBaseController
{

    /**
     * 区域列表
     */
    public function index()
    {
        $areaModel = new AreaModel();
        $areaTree = $areaModel->adminAreaTableTree();
        $this->assign('areaTree', $areaTree);

        return $this->fetch();
    }

    /**
     * 新增区域
     */
    public function add()
    {
        $parentId = $this->request->param('parent', 0, 'intval');
        $areaModel = new AreaModel();
        $areasTree = $areaModel->adminAreaTree($parentId);

        $this->assign('areas_tree', $areasTree);
        return $this->fetch();
    }
    /**
     * 提交新增区域
     */
    public function addPost()
    {
        $areaModel = new AreaModel();
        $data = $this->request->param();
        $result = $this->validate($data,'Area');
        if ($result !== true) {
            $this->error($result);
        }
        $result = $areaModel->addArea($data);
        if ($result === false) {
            $this->error('添加失败!');
        }

        $this->success('添加成功!', url('Area/index'));

    }

    /**
     * 编辑区域
     */
    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');
        if ($id > 0) {
            $area = AreaModel::get($id)->toArray();
            $areaModel = new AreaModel();
            $areasTree = $areaModel->adminAreaTree($area['parent_id'], $id);
            $this->assign($area);
            $this->assign('areas_tree',$areasTree);
            return $this->fetch();
        }else{
            $this->error('操作错误!');
        }
    }

    /**
     * 编辑区域提交保存
     */
    public function editPost()
    {
        $data = $this->request->param();
        
        $result = $this->validate($data, 'area');
        if ($result !== true) {
            $this->error($result);
        }

        $areaModel = new AreaModel();
        $result = $areaModel->editArea($data);
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
        AreaModel::destroy($id);

        $this->success("删除成功！", url("area/index"));
    }

    function getTree(){
        $parent_id = input('parent_id', 0);
        $area = AreaModel::where(['status'=>1])->select()->toArray();
        $tree = new Tree();
        $tree->init($area);
        return json($tree->getTreeArray($parent_id));
    }

    function getPathIds(){
        $id = input('id', 0);
        $path = AreaModel::where('id', $id)->value('path');
        $path = explode('-', $path);
        array_shift($path);
        return json($path);
    }

    /**
     * 文章分类选择对话框
     * @adminMenu(
     *     'name'   => '文章分类选择对话框',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章分类选择对话框',
     *     'param'  => ''
     * )
     */
    public function select()
    {
        $ids = $this->request->param('ids');
        $parent_id = $this->request->param('parent_id',null);
        $selectedIds = explode(',', $ids);
        $parentIds = explode(',', $parent_id);
        $areaModel = new AreaModel();
        $tpl = <<<tpl
<tr class='data-item-tr'>
    <td>
        <input type='checkbox' class='js-check' data-yid='js-check-y' data-xid='js-check-x' name='ids[]'
               value='\$id' data-name='\$name' \$checked>
    </td>
    <td>\$id</td>
    <td>\$spacer \$name</td>
</tr>
tpl;

        $categoryTree = $areaModel->adminAreaTableTree($selectedIds, $tpl, $parent_id);

        $where = ['status' => 1];
        $categories = $areaModel->where($where)->select();
        $this->assign('categories', $categories);
        $this->assign('selectedIds', $selectedIds);
        $this->assign('categories_tree', $categoryTree);
        return $this->fetch();
    }


    /**
     * 区域显示隐藏
     */
   /* public function toggle()
    {
        $data      = $this->request->param();
        $linkModel = new LinkModel();

        if (isset($data['ids']) && !empty($data["display"])) {
            $ids = $this->request->param('ids/a');
            $linkModel->where(['id' => ['in', $ids]])->update(['status' => 1]);
            $this->success("更新成功！");
        }

        if (isset($data['ids']) && !empty($data["hide"])) {
            $ids = $this->request->param('ids/a');
            $linkModel->where(['id' => ['in', $ids]])->update(['status' => 0]);
            $this->success("更新成功！");
        }
    }*/
    /**
     * 获取当前地区下的场馆
     */
    public function area_venues(){
        $area_id=$this->request->param('area_id',0,'intval');
        $userModel= new UserModel();
        $venues=$userModel->area_venues($area_id);
        if(!empty($venues)){
            echo json_encode(['status'=>1,'data'=>$venues,'message'=>'场馆获取成功']);exit;
        }else{
            echo json_encode(['status'=>1,'data'=>'','message'=>'无场馆']);exit;
        }
    }
}