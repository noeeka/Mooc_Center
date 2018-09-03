<?php

namespace app\admin\model;

use think\Model;
use tree\Tree;

class HomePageContentModel extends Model
{
    protected $insert = ['create_time', 'update_time'];
    protected $update = ['update_time'];
    public $arr = [];

    protected function setCreateTimeAttr()
    {
        return time();
    }

    protected function setUpdateTimeAttr()
    {
        return time();
    }

    /**
     * 生成分类 select树形结构
     * @param int $selectId 需要选中的分类 id
     * @param int $currentCid 需要隐藏的分类 id
     * @return string
     */
    public function contentTree($selectId = 0, $currentCid = 0, $where = [])
    {
        $categories = $this->where($where)->order("list_order ASC")->select()->toArray();

        $tree       = new Tree();
        $tree->icon = ['&nbsp;&nbsp;│', '&nbsp;&nbsp;├─', '&nbsp;&nbsp;└─'];
        $tree->nbsp = '&nbsp;&nbsp;';

        $newCategories = [];
        foreach ($categories as $item) {
            $item['selected'] = $selectId == $item['id'] ? "selected" : "";

            array_push($newCategories, $item);
        }

        $tree->init($newCategories);
        $str     = '<option value=\"{$id}\" {$selected}>{$spacer}{$title}</option>';
        $treeStr = $tree->getTree(0, $str);

        return $treeStr;
    }

    public function contentTableTree($currentIds = 0, $tpl = '')
    {
        $null = '<span style="color:red;font-style: italic">（null）</span>';
        $categories = $this->where(['type'=>0])->order("list_order ASC")->select()->toArray();

        $tree = new Tree();
        $tree->icon = ['&nbsp;&nbsp;│', '&nbsp;&nbsp;├─', '&nbsp;&nbsp;└─'];
        $tree->nbsp = '&nbsp;&nbsp;';

        if (!is_array($currentIds)) {
            $currentIds = [$currentIds];
        }

        $newCategories = [];
        foreach ($categories as $item) {
            $item['checked'] = in_array($item['id'], $currentIds) ? "checked" : "";
            if ($item['parent_id'] == 0) {
                $item['tpl_name'] = '';
                $item['resource_name'] = '';
            } else {
                $tplObj = HomePageTplModel::get($item['tpl_id']);
                $item['tpl_name'] = $tplObj == null ?  $null : $tplObj->getData('name');
                $resourceObj = HomePageResourceModel::get($item['resource_id']);
                $item['resource_name'] = $resourceObj == null ? $null : $resourceObj->getData('name');
            }
            $item['create_time'] = $item['create_time'] == 0 ? $null : date('Y-m-d H:i', $item['create_time']);
            $item['update_time'] = $item['update_time'] == 0 ? $null : date('Y-m-d H:i', $item['update_time']);
            $item['status'] = $item['status'] == 0 ? '隐藏' : '显示';
            $item['str_action'] = '<a href="' . url("HomePageContent/edit", ["id" => $item['id']]) . '">编辑</a>  <a class="js-ajax-delete" href="' . url("HomePageContent/delete", ["id" => $item['id']]) . '">删除</a> ';
            if ($item['parent_id'] == 0) {
                $item['str_action'] = '<a href="' . url("HomePageContent/add", ["parent_id" => $item['id']]) . '">添加子模块</a>  ' . $item['str_action'];
            }
            array_push($newCategories, $item);
        }

        $tree->init($newCategories);
        if (empty($tpl)) {
            $tpl = "<tr>
                        <td><input type='checkbox' class='js-check' data-yid='js-check-y' data-xid='js-check-x' name='ids[]' value='\$id' title='ID:\$id'></td>
                        <td><input name='list_orders[\$id]' type='text' size='3' value='\$list_order' class='input-order'></td>
                        <td>\$id</td>
                        <td>\$spacer \$title</td>
                        <td>\$tpl_name</td>
                        <td>\$resource_name</td>
                        <td>\$status</td>
                        <td>\$create_time</td>
                        <td>\$update_time</td>
                        <td>\$str_action</td>
                    </tr>";
        }
        $treeStr = $tree->getTree(0, $tpl);

        return $treeStr;
    }

    /**
     * 生成树型结构数组
     * @param int myID，表示获得这个ID下的所有子级
     * @param int $maxLevel 最大获取层级,默认不限制
     * @param int $level 当前层级,只在递归调用时使用,真实使用时不传入此参数
     * @return array
     */
    public function getContentArray($myId, $maxLevel = 0, $level = 1)
    {
        $returnArray = [];
        //一级数组
        $children = $this->getChild($myId);

        if (is_array($children)) {
            foreach ($children as $child) {
                $child['_level']           = $level;
                $returnArray[$child['id']] = $child;
                if ($maxLevel === 0 || ($maxLevel !== 0 && $maxLevel > $level)) {

                    $mLevel                                = $level + 1;
                    $returnArray[$child['id']]["children"] = $this->getContentArray($child['id'], $maxLevel, $mLevel);
                }

            }
        }

        return $returnArray;
    }

    public function getChild($myId)
    {
        $newArr = [];
        if (is_array($this->arr)) {
            foreach ($this->arr as $id => $a) {

                if ($a['parent_id'] == $myId) {
                    $newArr[$id] = $a;
                }
            }
        }

        return $newArr ? $newArr : false;
    }



}