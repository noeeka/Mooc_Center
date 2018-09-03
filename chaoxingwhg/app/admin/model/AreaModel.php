<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use app\admin\model\RouteModel;
use think\Model;
use tree\Tree;

class AreaModel extends Model
{
    private $leafs;

    public function getLevels()
    {
        $select = $this->field(['id', 'path'])->select();
        $levels = [];
        foreach ($select as $val) {
            $levels[$val['id']] = count(explode('-', $val['path'])) - 1;
        }
        return $levels;
    }

    public function getLeafs($id){
        $this->getLeaf($id);
        return $this->leafs;
    }

    public function getLeafsOption($id, $selectedId = 0){
        $lid = $this->getLeafs($id);
        $list = $this->where(['id'=>['in', $lid]])->column('id,name');
        $html = '';
        foreach ($list as $id=>$name){
            $selected = $selectedId == $id ? ' selected' : '';
            $html .= "<option value='".$id."' ".$selected.">".$name."</option>";
        }
        return $html;
    }

    private function getLeaf($parentid){
        $childIds = $this->where('parent_id', $parentid)->column('id');
        if(empty($childIds)){
            $this->leafs[] = $parentid;
            return $parentid;
        }else{
            foreach ($childIds as $v){
                $this->getLeaf($v);
            }
        }
    }

    /**
     * 生成分类 select树形结构
     * @param int $selectId 需要选中的分类 id
     * @param int $currentCid 需要隐藏的分类 id
     * @return string
     */
    public function adminAreaTree($selectId = 0, $currentCid = 0, $parent_id = 0, $whe=[])
    {
        //$where = ['delete_time' => 0];
        if(!empty($whe)){
            $where = $whe;
        }
        $where['status'] =1;

        if (!empty($currentCid)) {
            $where['id'] = ['neq', $currentCid];
        }
        $areas = $this->where($where)->select()->toArray();

        $tree = new Tree();
        $tree->icon = ['&nbsp;&nbsp;│', '&nbsp;&nbsp;├─', '&nbsp;&nbsp;└─'];
        $tree->nbsp = '&nbsp;&nbsp;';

        $newAreas = [];
        foreach ($areas as $item) {
            $item['selected'] = $selectId == $item['id'] ? "selected" : "";
            array_push($newAreas, $item);
        }

        $tree->init($newAreas);
        $str = '<option value=\"{$id}\" {$selected}>{$spacer}{$name}</option>';
        $treeStr = $tree->getTree($parent_id, $str);

        return $treeStr;
    }

    /**
     * 生成区域树形结构
     */
    public function adminAreaTableTree($currentIds = 0, $tpl = '')
    {
        // $where = ['delete_time' => 0];
//        if (!empty($currentCid)) {
//            $where['id'] = ['neq', $currentCid];
//        }
        $areas = $this->select()->toArray();

        $tree = new Tree();
        $tree->icon = ['&nbsp;&nbsp;│', '&nbsp;&nbsp;├─', '&nbsp;&nbsp;└─'];
        $tree->nbsp = '&nbsp;&nbsp;';

        if (!is_array($currentIds)) {
            $currentIds = [$currentIds];
        }

        $newAreas = [];
        foreach ($areas as $item) {
            $level = $this->getLevel($item['id']);
            $item['checked'] = in_array($item['id'], $currentIds) ? "checked" : "";
            //$item['url'] = cmf_url('portal/List/index', ['id' => $item['id']]);
            $item['status'] = $item['status'] == 1 ? '启用' : '禁用';
           /* if ($level >= 4) {
                $item['str_action'] = '<a href="' . url("Area/edit", ["id" => $item['id']]) . '">' . lang('EDIT') . '</a>  <a class="js-ajax-delete" href="' . url("Area/delete", ["id" => $item['id']]) . '">' . lang('DELETE') . '</a> ';
            } else {*/
                $item['str_action'] = '<a href="' . url("Area/add", ["parent" => $item['id']]) . '">添加子区域</a>  <a href="' . url("Area/edit", ["id" => $item['id']]) . '">' . lang('EDIT') . '</a>  <a class="js-ajax-delete" href="' . url("Area/delete", ["id" => $item['id']]) . '">' . lang('DELETE') . '</a> ';
           /* }*/
            array_push($newAreas, $item);
        }

        $tree->init($newAreas);
        if (empty($tpl)) {
            $tpl = "<tr>
                        <td>\$id</td>
                        <td>\$spacer \$name</a></td>
                        <td>\$status</td>
                        <td>\$str_action </td>
                    </tr>";
        }
        $treeStr = $tree->getTree(0, $tpl);

        return $treeStr;
    }

    /**
     * 添加区域
     * @param $data
     * @return bool
     */
    public function addArea($data)
    {
        $result = true;
        self::startTrans();
        try {
            $this->allowField(true)->save($data);
            $id = $this->id;
            if (empty($data['parent_id'])) {
                $this->where(['id' => $id])->update(['path' => '0-' . $id]);
            } else {
                $parentPath = $this->where('id', intval($data['parent_id']))->value('path');
                $this->where(['id' => $id])->update(['path' => "$parentPath-$id"]);
            }
            self::commit();
        } catch (\Exception $e) {
            self::rollback();
            $result = false;
        }
        return $result;
    }

    function getTreeIds($parentid){
        $tree = new Tree();
        $data = $this->where(['status'=>1])->select()->toArray();
        $tree->init($data);
        return array_filter(explode(',', $tree->getTree($parentid, "\$id,")));
    }

    /**
     * 编辑区域
     * @param $data
     * @return bool
     */
    public function editArea($data)
    {
        $result = true;
        $id = intval($data['id']);
        $parentId = intval($data['parent_id']);
        $oldArea = $this->where('id', $id)->find();
        if (empty($parentId)) {
            $newPath = '0-' . $id;
        } else {
            $parentPath = $this->where('id', intval($data['parent_id']))->value('path');
            if ($parentPath === false) {
                $newPath = false;
            } else {
                $newPath = "$parentPath-$id";
            }
        }
        if (empty($oldArea) || empty($newPath)) {
            $result = false;
        } else {

            $data['path'] = $newPath;
            $this->isUpdate(true)->allowField(true)->save($data, ['id' => $id]);
            $children = $this->field('id,path')->where('path', 'like', "%-$id-%")->select();
            if (!empty($children)) {
                foreach ($children as $child) {
                    $childPath = str_replace($oldArea['path'] . '-', $newPath . '-', $child['path']);
                    $this->isUpdate(true)->save(['path' => $childPath], ['id' => $child['id']]);
                }
            }
            $routeModel = new RouteModel();
            $routeModel->getRoutes(true);
        }
        return $result;
    }

    /**获取当前区域层级
     */
    public function getLevel($id)
    {
        $areaModel = new AreaModel();
        $path = $areaModel->where('id', $id)->value('path');
        if (!empty($path)) {
            $count = count(explode('-', $path));
            return $count;
        }
    }
}