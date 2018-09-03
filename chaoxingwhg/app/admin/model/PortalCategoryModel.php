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

class PortalCategoryModel extends Model
{
    private static $URL_MAP = ['8'=>'portal/category/info',
                                '36'=>'portal/live/index',
                                '31'=>'portal/culture/index',
                                '12'=>'portal/category/resource',
                                '17'=>'portal/room/index',
                                '18'=>'portal/category/quanjing',
                                '20'=>'portal/about/index',
                                '85'=>'portal/total/index',
                                '16'=>'portal/activity/index'];

    private static $TARGET = ['8'=>'_self',
                                '36'=>'_self',
                                '31'=>'_self',
                                '12'=>'_self',
                                '17'=>'_self',
                                '18'=>'_blank',
                                '19'=>'_blank',
                                '20'=>'_self',
                                '16'=>'_self',
                                '85'=>'_self'

    ];

    public function isArticle(){
        return true;
       // return in_array($this->getData('type'), [2, 3]);
    }
    private static $CONFIG = [
        'cant_sort'=>[5, 6, 20],
        'level_1_can_add_child'=>[8, 12, 37, 45, 46, 47, 48],
        'level_2_cant_add_child'=>[21, 22],
    ];
    private $ret = [];

    static function getMoreUrl($id){
        $url = 'javascript:;';
        $category = PortalCategoryModel::get($id)->toArray();

        if($id == 49){
            $url = '/portal/video/index';
        }elseif($id == 19){
            $more = $category['more'];
            if(is_array($more) && isset($more['muke_url'])){
                $url = $more['muke_url'];
            }
        }elseif($category['type'] == 2 || $category['type'] == 3){
            $url = '/portal/articles/'.$id;
        }
        return isset(self::$URL_MAP[$id]) ? self::$URL_MAP[$id] : $url;
    }

    function getLevel($id)
    {
        $parent_id = $this->where('id', $id)->value('parent_id', 0);
        $level = 1;
        while ($parent_id != 0) {
            $parent_id = $this->where('id', $parent_id)->value('parent_id', 0);
            $level++;
        }
        return $level;
    }

    protected $type = [
        'more' => 'array',
    ];

    function getPid($id)
    {
        return $this->where('id', $id)->value('parent_id');
    }

    function getReversePidTree($id)
    {
        $pid = $this->getPid($id);
        if ($pid == 0) {
            return $this->ret;
        } else {
            $this->ret[] = $pid;
            return $this->getReversePidTree($pid);
        }
    }

    function getTopPid($id)
    {
        $tree = $this->getReversePidTree($id);
        return empty($tree) ? $id :array_pop($tree);
    }


    /**
     * 生成分类 select树形结构
     * @param int $selectId 需要选中的分类 id
     * @param int $currentCid 需要隐藏的分类 id
     * @return string
     */
    public function adminCategoryTree($selectId = 0, $currentCid = 0, $parentid = 0)
    {
        $where = ['delete_time' => 0];
        if (!empty($currentCid)) {
            $where['id'] = ['neq', $currentCid];
        }
        $categories = $this->order("list_order ASC")->where($where)->select()->toArray();

        $tree = new Tree();
        $tree->icon = ['&nbsp;&nbsp;│', '&nbsp;&nbsp;├─', '&nbsp;&nbsp;└─'];
        $tree->nbsp = '&nbsp;&nbsp;';

        $newCategories = [];
        foreach ($categories as $item) {
            $item['selected'] = $selectId == $item['id'] ? "selected" : "";

            array_push($newCategories, $item);
        }

        $tree->init($newCategories);
        $str = '<option value=\"{$id}\" {$selected}>{$spacer}{$name}</option>';
        $treeStr = $tree->getTree($parentid, $str);

        return $treeStr;
    }

    function getList()
    {
        $list = $this->where(['status' => 1, 'parent_id' => 0, 'id'=>['not in', [32, 33, 34, 37]]])->field(['id', 'name as title', 'template_id', 'more as background_image', 'background_color', 'font_color'])->order(['list_order' => 'asc'])->select();
        $res = [];
        $nav = null;
        $banner = null;
        $about = null;
        foreach ($list as $item) {
            $item['url'] = self::getMoreUrl($item['id']);
            if(array_key_exists($item['id'],self::$TARGET)){
                $item['target'] = self::$TARGET[$item['id']];
            }
            $item['background_image'] = cmf_get_image_preview_url(json_decode($item['background_image'], true)['thumbnail']);
            if ($item['id'] == 5) {
                $nav = $item;
                $nav['logo'] = cmf_get_image_preview_url(BaseinfoModel::get(0)->value('home_page_logo'));
            } elseif ($item['id'] == 6) {
                $banner = $item;
            } elseif ($item['id'] == 20) {
                $about = $item;
            } else {
                array_push($res, $item);
            }
        }
        if($banner != null)array_unshift($res, $banner);
        array_unshift($res, $nav);
        array_push($res, $about);
        return $res;
    }

    function getChild($id, $maxdepth = 3)
    {
        if ($maxdepth < 1) return [];
        $list = $this->where(['status' => 1, 'delete_time' => 0])->field('id,name,parent_id,list_order,type')->order(['list_order' => 'asc'])->select()->toArray();
        $tree = new Tree();
        $tree->init($list);
        $arr = $tree->getTreeArray($id, $maxdepth);
        //结果排序
        $this->sortTree($arr);
        if ($maxdepth > 1) {
            foreach ($arr as &$v1) {
                $this->sortTree($v1['children']);
                foreach ($v1['children'] as &$v3) {
                    $this->sortTree($v3['children']);
                }
            }
        }
        $res = [];
        $nav = null;
        $banner = null;
        $about = null;
        foreach ($arr as &$item) {
            if ($item['id'] == 5) {
                $nav = $item;
            } elseif ($item['id'] == 6) {
                $banner = $item;
            } elseif ($item['id'] == 20) {
                $about = $item;
            } else {
                array_push($res, $item);
            }
        }
        if ($banner != null) {
            array_unshift($res, $banner);
        }
        if ($nav != null) {
            array_unshift($res, $nav);
        }
        if ($about != null) {
            array_push($res, $about);
        }
        return $res;
    }

    function sortTree(&$arr)
    {
        if (!empty($arr)) {
            usort($arr, function ($a, $b) {
                if ($a['list_order'] < $b['list_order']) {
                    return -1;
                } elseif ($a['list_order'] > $b['list_order']) {
                    return 1;
                } else {
                    return $a['id'] < $b['id'] ? -1 : 1;
                }
            });
        }
        return $arr;
    }

    function menu($type = 0)
    {
        $menu = $this->where(['status' => 1, 'parent_id' => 0])->field(['id', 'name as title'])->order(['list_order' => 'asc'])->select()->toArray();
        foreach ($menu as $k => &$v) {
            switch ($v['id']) {
                case 8:
//                    信息展示
                    $v['url'] = $type == 1 ? '/wx/articles/8' : '/portal/articles/8';
                    $v['target'] = '_self';
                    break;
                case 12:
//                    资源展示
                    $v['url'] = $type == 1 ? '/wx/articles/12' : '/portal/articles/12';
                    $v['target'] = '_self';
                    break;
                case 16:
//                    活动展示
                    $v['url'] = $type == 1 ? '/wx/activity/index' : '/portal/activity/index';
                    $v['target'] = '_self';
                    break;
                case 17:
//                    场馆预约
                    $v['url'] = $type == 1 ? '/wx/venue/index' : '/portal/room/index';
                    $v['target'] = '_self';
                    break;
                case 18:
//                    全景展示
                    $v['url'] = $type == 1 ? '/wx/category/quanjing' : '/portal/category/quanjing';
                    $v['target'] = '_self';
                    break;
                case 19:
//                    慕课平台
                    if($type == 1){
                        unset($menu[$k]);
                    }else{
                        $more = $this->where('id', 19)->value('more', '');
                        $v['url'] = 'javascript:;';
                        if($more != ''){
                            $more = json_decode($more, true);
                            $v['url'] = $more['muke_url'];
                        }
                        $v['target'] = '_blank';
                    }
                    break;
                case 20:
//                    关于我们
                    if($type == 1){
                        unset($menu[$k]);
                    }else{
                        $v['url'] = '/portal/about/index';
                        $v['target'] = '_self';
                    }
                    break;
                case 31:
//                    文化点单
                    $v['url'] = $type == 1 ? '/wx/culture/index' : '/portal/culture/index';
                    $v['target'] = '_self';
                    break;

                case 88:
//                    品牌文化
                    $v['url'] = $type == 1 ? '/wx/articles/88' : '/portal/articles/88';
                    $v['target'] = '_self';
                    break;


                case 32:
//                    民意征集
                    $v['url'] = $type == 1 ? '/wx/opinion/info' : '/portal/opinion/info';
                    $v['target'] = '_self';
                    break;
                case 33:
//                    资源库
//                    $v['url'] = $type == 1 ? '/wx/repository/index' : '/portal/repository/index';
//                    $v['target'] = '_self';
                    $v['url'] = $type == 1 ? 'http://www.chaoxing.com' : 'http://www.chaoxing.com';
                    $v['target'] = '_blank';
                    break;
                case 34:
//                    特色库     注：34要与特色库绑定
                    $v['url'] = $type == 1 ? 'http://www.chaoxing.com' : 'http://www.chaoxing.com';
                    $v['target'] = '_blank';
                    break;
                case 36:
//                    文化直播
                    $v['url'] = $type == 1 ? '/wx/live/index' : '/portal/live/index';
                    $v['target'] = '_self';
                    break;
                case 37:
//                    馆务公开
                    $v['url'] = $type == 1 ? '/wx/articles/37' : '/portal/articles/37';
                    $v['target'] = '_self';
                    break;
                case 81:
//                    馆务公开
                    $v['url'] = $type == 1 ? '/wx/volunteer/index' : '/portal/volunteer/index';
                    $v['target'] = '_self';
                    break;
                case 49:
//                    精彩视频
                    $v['url'] = $type == 1 ? '/wx/video/index' : '/portal/video/index';
                    $v['target'] = '_self';
                    break;
                case 45:
//                    免费开放
                    $v['url'] = $type == 1 ? '/wx/articles/45' : '/portal/articles/45';
                    $v['target'] = '_self';
                    break;
                case 46:
//                    馆办团队
                    $v['url'] = $type == 1 ? '/wx/articles/46' : '/portal/articles/46';
                    $v['target'] = '_self';
                    break;
                case 47:
//                    文艺创作
                    $v['url'] = $type == 1 ? '/wx/articles/47' : '/portal/articles/47';
                    $v['target'] = '_self';
                    break;
                case 48:
//                    非遗保护
                    $v['url'] = $type == 1 ? '/wx/articles/48' : '/portal/articles/48';
                    $v['target'] = '_self';
                    break;
                case 50:
//                    公告公示
                    $v['url'] = $type == 1 ? '/wx/articles/50' : '/portal/articles/50';
                    $v['target'] = '_self';
                    break;
                case 85:
//                    总分管
                    $v['url'] = $type == 1 ? '/wx/total/' : '/portal/total/';
                    $v['target'] = '_self';
                    break;
                case 89:
//                    作品征集
                    $v['url'] = $type == 1 ? '/wx/product_collect/' : '/portal/product_collect/';
                    $v['target'] = '_self';
                    break;
                default:
                    unset($menu[$k]);
            }
        }
//        array_unshift($menu, ['id' => 0, 'title' => '首页', 'url' => '/']);
        return $menu;
    }
//    function menu()
//    {
//        $menu = $this->where(['status' => 1, 'parent_id' => 0])->field(['id', 'name as title'])->order(['list_order' => 'asc'])->select()->toArray();
//        foreach ($menu as $k => &$v) {
//            switch ($v['id']) {
//                case 8:
////                    信息展示
//                    $v['url'] = '/wb/site/info';
//                    $v['target'] = '_self';
//                    break;
//                case 12:
////                    资源展示
//                    $v['url'] = '/wb/site/resource';
//                    $v['target'] = '_self';
//                    break;
//                case 16:
////                    活动展示
//                    $v['url'] = '/wb/site/activity';
//                    $v['target'] = '_self';
//                    break;
//                case 17:
////                    场馆预约
//                    $v['url'] = '/wb/site/venue';
//                    $v['target'] = '_self';
//                    break;
//                case 18:
////                    全景展示
//                    $v['url'] = '/wb/site/quanjing';
//                    $v['target'] = '_blank';
//                    break;
//                case 19:
////                    慕课平台
//                    $v['url'] = '/wb/site/muke';
//                    $v['target'] = '_blank';
//                    break;
//                case 20:
////                    关于我们
//                    $v['url'] = '/wb/site/aboutUs';
//                    $v['target'] = '_self';
//                    break;
//                default:
//                    unset($menu[$k]);
//            }
//        }
//        array_unshift($menu, ['id' => 0, 'name' => '首页', '/']);
//        return $menu;
//    }

    /**
     * @param int|array $currentIds
     * @param string $tpl
     * @return string
     */
    public function adminCategoryTableTree($currentIds = 0, $tpl = '', $parentid = 0)
    {
        $where = ['delete_time' => 0];
//        if (!empty($currentCid)) {
//            $where['id'] = ['neq', $currentCid];
//        }
        $categories = $this->order("list_order ASC")->where($where)->select()->toArray();


        $tree = new Tree();
        $tree->icon = ['&nbsp;&nbsp;│', '&nbsp;&nbsp;├─', '&nbsp;&nbsp;└─'];
        $tree->nbsp = '&nbsp;&nbsp;';

        if (!is_array($currentIds)) {
            $currentIds = [$currentIds];
        }

        $newCategories = [];
        $nav = null;
        $banner = null;
        $about = null;
        foreach ($categories as $item) {
            $level = $this->getLevel($item['id']);
            $item['checked'] = in_array($item['id'], $currentIds) ? "checked" : "";
            if (in_array($item['id'], self::$CONFIG['cant_sort'])) {
                $item['disabled'] = 'disabled';
            } else {
                $item['disabled'] = '';
            }
            if ($level == 1 && !in_array($item['id'], self::$CONFIG['level_1_can_add_child'])) {
                $item['str_action'] = '<a href="' . url("AdminCategory/edit", ["id" => $item['id']]) . '">' . lang('EDIT') . '</a>';
            } elseif($level == 2 && in_array($item['id'], self::$CONFIG['level_2_cant_add_child'])) {
                $item['str_action'] = '<a href="' . url("AdminCategory/edit", ["id" => $item['id']]) . '">' . lang('EDIT') . '</a>';
            } elseif ($level == 3) {
                $item['str_action'] = '<a href="' . url("AdminCategory/edit", ["id" => $item['id']]) . '">' . lang('EDIT') . '</a> <a class="js-ajax-delete" href="' . url("AdminCategory/delete", ["id" => $item['id']]) . '">删除</a>';
            }else{
                $item['str_action'] = '<a href="' . url("AdminCategory/add", ["parent" => $item['id']]) . '">添加子栏目</a>  <a href="' . url("AdminCategory/edit", ["id" => $item['id']]) . '">' . lang('EDIT') . '</a>';
            }
            $item['status'] = $item['status'] == 1 ? '显示' : '隐藏';
            if ($item['id'] == 5) {
                $nav = $item;
            } elseif ($item['id'] == 6) {
                $banner = $item;
            } elseif ($item['id'] == 20) {
                $about = $item;
            } else {
                array_push($newCategories, $item);
            }
        }
        if ($banner != null) {
            array_unshift($newCategories, $banner);
        }
        if ($nav != null) {
            array_unshift($newCategories, $nav);
        }
        if ($about != null) {
            array_push($newCategories, $about);
        }
        $tree->init($newCategories);
        if (empty($tpl)) {
            $tpl = "<tr>
                        <td><input name='list_orders[\$id]' type='text' size='3' \$disabled value='\$list_order' class='input-order'></td>
                        <td>\$id</td>
                        <td>\$spacer \$name</td>
                        <td>\$status</td>
                        <td>\$str_action</td>
                    </tr>";
        }
        $treeStr = $tree->getTree($parentid, $tpl);

        return $treeStr;
    }

    function getTreeIds($parentid)
    {
        $tree = new Tree();
        $data = $this->where(['delete_time' => 0])->select()->toArray();
        $tree->init($data);
        return array_filter(explode(',', $tree->getTree($parentid, "\$id,")));
    }

    public function getTemplate($id, $tempdate_id = null)
    {
        $where = [];
        if ($id == 5) {
            $where['type'] = 1;
        } elseif ($id == 6) {
            $where['type'] = 2;
        } else {
            $where['type'] = 3;
        }
        $template = db('template')->where($where)->select();
        $templateStr = '';
        foreach ($template as $value) {
            $selected = $tempdate_id == $value['id'] ? 'selected' : '';
            $templateStr .= '<option value="' . $value['id'] . '" ' . $selected . '>' . $value['name'] . '</option>';
        }
        return $templateStr;
    }

    /**
     * 添加文章分类
     * @param $data
     * @return bool
     */
    public function addCategory($data)
    {
        $result = true;
        self::startTrans();
        try {
            if (!empty($data['more']['thumbnail'])) {
                $data['more']['thumbnail'] = cmf_asset_relative_url($data['more']['thumbnail']);
            }
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

        if ($result != false) {
            //设置别名
            $routeModel = new RouteModel();
            if (!empty($data['alias']) && !empty($id)) {
                $routeModel->setRoute($data['alias'], 'portal/List/index', ['id' => $id], 2, 5000);
                $routeModel->setRoute($data['alias'] . '/:id', 'portal/Article/index', ['cid' => $id], 2, 4999);
            }
            $routeModel->getRoutes(true);
        }

        return $result;
    }

    public function editCategory($data)
    {
        $result = true;

        $id = intval($data['id']);
        $parentId = intval($data['parent_id']);
        $oldCategory = $this->where('id', $id)->find();

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

        if (empty($oldCategory) || empty($newPath)) {
            $result = false;
        } else {


            $data['path'] = $newPath;
            if (!empty($data['more']['thumbnail'])) {
                $data['more']['thumbnail'] = cmf_asset_relative_url($data['more']['thumbnail']);
            }
            $this->isUpdate(true)->allowField(true)->save($data, ['id' => $id]);

            $children = $this->field('id,path')->where('path', 'like', "%-$id-%")->select();

            if (!empty($children)) {
                foreach ($children as $child) {
                    $childPath = str_replace($oldCategory['path'] . '-', $newPath . '-', $child['path']);
                    $this->isUpdate(true)->save(['path' => $childPath], ['id' => $child['id']]);
                }
            }

            $routeModel = new RouteModel();
            if (!empty($data['alias'])) {
                $routeModel->setRoute($data['alias'], 'api/ar/index', ['id' => $data['id']], 2, 5000);
                $routeModel->setRoute($data['alias'] . '/:id', 'portal/Article/index', ['cid' => $data['id']], 2, 4999);
            } else {
                $routeModel->deleteRoute('portal/List/index', ['id' => $data['id']]);
                $routeModel->deleteRoute('portal/Article/index', ['cid' => $data['id']]);
            }

            $routeModel->getRoutes(true);
        }


        return $result;
    }


}