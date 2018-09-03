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
use tree\Tree;
use app\admin\model\AdminMenuModel;
use think\Cache;
class SuperRbacController extends AdminBaseController
{



    /**
     * 设置角色权限
     * @adminMenu(
     *     'name'   => '设置角色权限',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '设置角色权限',
     *     'param'  => ''
     * )
     */
    public function authorize()
    {
        $hiddens = [];
        $AuthAccess     = Db::name("AuthAccess");
        $adminMenuModel = new AdminMenuModel();


        $tree       = new Tree();
        $tree->icon = ['│ ', '├─ ', '└─ '];
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $result = $this->menuCache($adminMenuModel);

        $newMenus      = [];

        foreach ($result as $m) {
            $newMenus[$m['id']] = $m;
        }

        foreach ($result as $n => $t) {

            if($result[$n]['rule_show'] == 0) {
                array_push($hiddens ,   $result[$n]['id']);
            }
            $result[$n]['checked']      =  $result[$n]['rule_show'] == 0  ? ' checked' : '';
            $result[$n]['level']        = $this->_getLevel($t['id'], $newMenus);
            $result[$n]['style']        = empty($t['parent_id']) ? '' : 'display:none;';
            $result[$n]['parentIdNode'] = ($t['parent_id']) ? ' class="child-of-node-' . $t['parent_id'] . '"' : '';
        }

        $str = "<tr id='node-\$id'\$parentIdNode  style='\$style'>
                   <td style='padding-left:30px;'>\$spacer<input type='checkbox' name='menuId[]' value='\$id' level='\$level' \$checked onclick='javascript:checknode(this);'> \$name</td>
    			</tr>";

        $tree->init($result);

        $category = $tree->getTree(0, $str);
        $this->assign("category", $category);
        $this->assign("hiddens", json_encode($hiddens) );
        return $this->fetch();
    }

    /**
     * 角色授权提交
     * @adminMenu(
     *     'name'   => '角色授权提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '角色授权提交',
     *     'param'  => ''
     * )
     */
    public function authorizePost()
    {
        if ($this->request->isPost()) {
                $adminMenuModel = new AdminMenuModel();
                $adminMenuModel->where('rule_show' , 0)->update(['rule_show' => 1]);

                if( isset($_POST['menuId']) && !empty($_POST['menuId'])) {
                    $adminMenuModel->whereIn('id' ,  $_POST['menuId'])->update(['rule_show' => 0 , 'status' => 0]);
                }
                cache(null, 'admin_menus');// 删除后台菜单缓存
                $this->success("授权成功！");
        }
    }



    /**
     * 获取菜单深度
     * @param $id
     * @param array $array
     * @param int $i
     * @return int
     */
    protected function _getLevel($id, $array = [], $i = 0)
    {
        if ($array[$id]['parent_id'] == 0 || empty($array[$array[$id]['parent_id']]) || $array[$id]['parent_id'] == $id) {
            return $i;
        } else {
            $i++;
            return $this->_getLevel($array[$id]['parent_id'], $array, $i);
        }
    }

    public function menuCache($adminMenuModel)
    {
        if (empty($data)) {
            $data = $adminMenuModel->order("list_order", "ASC")->column('');
            Cache::set('Menu', $data, 0);
        } else {
            Cache::set('Menu', $data, 0);
        }
        return $data;
    }
}

