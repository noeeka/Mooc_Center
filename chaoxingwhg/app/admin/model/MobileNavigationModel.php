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

use think\Model;
use tree\Tree;

class MobileNavigationModel extends Model
{
    /**
     * 生成分类 select树形结构
     * @param int $selectId 需要选中的分类 id
     * @param int $currentCid 需要隐藏的分类 id
     * @return string
     */
    public function adminNavigationTree($selectId = 0, $currentCid = 0, $parent_id = 0, $whe=[],$type=1)
    {
        if(!empty($whe)){
            $where = $whe;
        }
        $where['status'] =1;
        $where['type'] =$type;
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
     * @param int|array $currentIds
     * @param string $tpl
     * @return string
     */
    public function adminNavigationTableTree($currentIds = 0, $tpl = '')
    {

        $categories = $this->order("list_order ASC")->select()->toArray();

        $tree       = new Tree();
        $tree->icon = ['&nbsp;&nbsp;│', '&nbsp;&nbsp;├─', '&nbsp;&nbsp;└─'];
        $tree->nbsp = '&nbsp;&nbsp;';

        if (!is_array($currentIds)) {
            $currentIds = [$currentIds];
        }

        $newCategories = [];
        foreach ($categories as $item) {
            $item['checked'] = in_array($item['id'], $currentIds) ? "checked" : "";
            $item['url']     = cmf_url('portal/List/index', ['id' => $item['id']]);
            if($item['parent_id']>0){
                $item['str_action'] = '<a href="' . url("Navigation/edit", ["id" => $item['id']]) . '">' . lang('EDIT') . '</a>  <a class="js-ajax-delete" href="' . url("Navigation/delete", ["id" => $item['id']]) . '">' . lang('DELETE') . '</a> ';
            }else{
                $item['str_action'] = '<a href="' . url("Navigation/add", ["parent" => $item['id']]) . '">添加子分类</a>  <a href="' . url("Navigation/edit", ["id" => $item['id']]) . '">' . lang('EDIT') . '</a>  <a class="js-ajax-delete" href="' . url("Navigation/delete", ["id" => $item['id']]) . '">' . lang('DELETE') . '</a> ';
            }
            $item['status'] = ($item['status']==1)?'已启用':'已禁用';
            array_push($newCategories, $item);
        }

        $tree->init($newCategories);

        if (empty($tpl)) {
            $tpl = "<tr>
                         
                        <td><input name='list_orders[\$id]' type='text' size='3' value='\$list_order' class='input-order'></td>
                        <td>\$id</td>
                        <td>\$spacer <a href='\$url' target='_blank'>\$name</a></td>
                        <td>\$url</td>
                        <td>\$status</td>
                        <td>\$str_action</td>
                    </tr>";
        }
        $treeStr = $tree->getTree(0, $tpl);

        return $treeStr;
    }

    /**
     * 获取首页导航
     */
    public function get_nav(){
        $parent_navs = $this->where(['status'=>1,'parent_id'=>0])->order("list_order ASC")->select();
        foreach ($parent_navs as &$nav){
            $sub_nav=$this->where(['status'=>1,'parent_id'=>$nav['id']])->order("list_order ASC")->select();
            $nav['sub_nav']=$sub_nav;
        }
        return $parent_navs;
    }

}