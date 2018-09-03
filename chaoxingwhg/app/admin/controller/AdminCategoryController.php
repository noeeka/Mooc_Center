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

use app\admin\model\BaseinfoModel;
use app\admin\model\RouteModel;
use cmf\controller\AdminBaseController;
use app\admin\model\PortalCategoryModel;
use think\Db;
use app\admin\model\ThemeModel;


class AdminCategoryController extends AdminBaseController
{
    private static $CONFIG = [
        //一级栏目特殊设置
        'level_1_only_name' => '32,33,34,37,43', //只能修改名称
        'level_1_not_set_font_color' => '5,6',   //不能设置字体颜色
        'level_1_cant_set_template' => '20',     //不能选择模板
        'level_1_show_rich_text' => '20',        //显示富文本框
        //全局设置
        'cant_set_map_is_show' => '5,6,20',      //不能设置文化地图功能区显示隐藏
        'cant_set_status' => '5',                 //不能设置状态
        'cant_set_type' => '5,6',                  //不能设置栏目类型
        'types' => ['1' => '其他', '2' => '单个文章', '3' => '文章列表页']
    ];

    /**
     * 文章分类列表
     * @adminMenu(
     *     'name'   => '分类管理',
     *     'parent' => 'portal/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章分类列表',
     *     'param'  => ''
     * )
     */
    public function index()
    {

        $portalCategoryModel = new PortalCategoryModel();
        $categoryTree = $portalCategoryModel->adminCategoryTableTree();

        $this->assign('category_tree', $categoryTree);
        return $this->fetch();
    }

    public function level()
    {
        $id = input('param.id', 21, 'intval');
        $portalCategoryModel = new PortalCategoryModel();
        return json($portalCategoryModel->getLevel($id));
    }

    /**
     * 添加文章分类
     * @adminMenu(
     *     'name'   => '添加文章分类',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加文章分类',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        $parentId = $this->request->param('parent', 0, 'intval');
        $portalCategoryModel = new PortalCategoryModel();
        if($parentId == 0){
            $categoriesTree = '<option value="0">作为一级分类</option>' . $portalCategoryModel->adminCategoryTree($parentId);
        }else{
            $cate = $portalCategoryModel->where('id', $parentId)->field('id,name')->find();
            $categoriesTree = '<option value="'.$cate->id.'">'.$cate->name.'</option>';
        }

        $themeModel = new ThemeModel();
        $listThemeFiles = $themeModel->getActionThemeFiles('portal/List/index');
        $articleThemeFiles = $themeModel->getActionThemeFiles('portal/Article/index');

        $this->assign('list_theme_files', $listThemeFiles);
        $this->assign('article_theme_files', $articleThemeFiles);
        $this->assign('categories_tree', $categoriesTree);
        $this->assign('parent', $parentId);
        return $this->fetch();
    }

    /**
     * 添加文章分类提交
     * @adminMenu(
     *     'name'   => '添加文章分类提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加文章分类提交',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        $portalCategoryModel = new PortalCategoryModel();

        $data = $this->request->param();

        $result = $this->validate($data, 'PortalCategory');

        if ($result !== true) {
            $this->error($result);
        }

        $result = $portalCategoryModel->addCategory($data);

        if ($result === false) {
            $this->error('添加失败!');
        }

        $this->success('添加成功!', url('AdminCategory/index'));

    }

    /**
     * 编辑文章分类
     * @adminMenu(
     *     'name'   => '编辑文章分类',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑文章分类',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');
        if ($id > 0) {
            $category = PortalCategoryModel::get($id)->toArray();

            $portalCategoryModel = new PortalCategoryModel();
            $categoriesTree = $portalCategoryModel->adminCategoryTree($category['parent_id'], $id);

            $themeModel = new ThemeModel();
            $listThemeFiles = $themeModel->getActionThemeFiles('portal/List/index');
            $articleThemeFiles = $themeModel->getActionThemeFiles('portal/Article/index');

            $routeModel = new RouteModel();
            $alias = $routeModel->getUrl('portal/List/index', ['id' => $id]);

            $category['alias'] = $alias;
            $about = (new BaseinfoModel)->where('id', 0)->find();
            $about = $about == null ? '' : $about->about;
            $this->assign($category);
            $this->assign('list_theme_files', $listThemeFiles);
            $this->assign('about', $about);
            $this->assign('article_theme_files', $articleThemeFiles);
            $this->assign('categories_tree', $categoriesTree);
            $this->assign('config', self::$CONFIG);
            $this->assign('template_list', $portalCategoryModel->getTemplate($id, $category['template_id']));
            return $this->fetch();
        } else {
            $this->error('操作错误!');
        }

    }

    /**
     * 编辑文章分类提交
     * @adminMenu(
     *     'name'   => '编辑文章分类提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑文章分类提交',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        $data = $this->request->param();
        $result = $this->validate($data, 'PortalCategory');

        if ($result !== true) {
            $this->error($result);
        }

        $portalCategoryModel = new PortalCategoryModel();

        $result = $portalCategoryModel->editCategory($data);
        if (isset($data['about'])) {
            BaseinfoModel::where('id', 0)->update(['about' => $data['about']]);
        }

        if ($result === false) {
            $this->error('保存失败!');
        }

        $this->success('保存成功!');
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
        $parent_id = $this->request->param('parent_id', null);
        $has_my = $this->request->param('has_my', 0);
        $selectedIds = explode(',', $ids);
        $parentIds = explode(',', $parent_id);
        $portalCategoryModel = new PortalCategoryModel();
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

        $categoryTree = $portalCategoryModel->adminCategoryTableTree($selectedIds, $tpl, $parent_id);
        if ($has_my == 1) {
            $my = $portalCategoryModel->where('id', $parent_id)->find();
            if ($my) {
                $checked = in_array($parent_id, $parentIds) ? ' checked ' : '';
                $myTr = "<tr class='data-item-tr'> <td><input type='checkbox' " . $checked . "class='js-check' data-yid='js-check-y' data-xid='js-check-x' name='ids[]' value='$parent_id' data-name='" . $my->name . "' ></td><td>$parent_id</td><td> " . $my->name . "</td></tr>";
                $categoryTree = $myTr . $categoryTree;
            }
        }
        $where = ['delete_time' => 0];
        $categories = $portalCategoryModel->where($where)->select();
        $this->assign('categories', $categories);
        $this->assign('selectedIds', $selectedIds);
        $this->assign('categories_tree', $categoryTree);
        return $this->fetch();
    }

    /**
     * 文章分类排序
     * @adminMenu(
     *     'name'   => '文章分类排序',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章分类排序',
     *     'param'  => ''
     * )
     */
    public function listOrder()
    {
        parent::listOrders(Db::name('portal_category'));
        $this->success("排序更新成功！", '');
    }

    /**
     * 删除文章分类
     * @adminMenu(
     *     'name'   => '删除文章分类',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除文章分类',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $portalCategoryModel = new PortalCategoryModel();
        $id = $this->request->param('id');
        //获取删除的内容
        $findCategory = $portalCategoryModel->where('id', $id)->find();

        if (empty($findCategory)) {
            $this->error('分类不存在!');
        }
//判断此分类有无子分类（不算被删除的子分类）
        $categoryChildrenCount = $portalCategoryModel->where(['parent_id' => $id, 'delete_time' => 0])->count();

        if ($categoryChildrenCount > 0) {
            $this->error('此分类有子类无法删除!');
        }

        $categoryPostCount = Db::name('portal_category_post')->where('category_id', $id)->delete();

//        if ($categoryPostCount > 0) {
//            $this->error('此分类有文章无法删除!');
//        }

        $data = [
            'object_id' => $findCategory['id'],
            'create_time' => time(),
            'table_name' => 'portal_category',
            'name' => $findCategory['name']
        ];
        $result = $portalCategoryModel
            ->where('id', $id)
            ->update(['delete_time' => time()]);
        if ($result) {
            Db::name('recycleBin')->insert($data);
            $this->success('删除成功!');
        } else {
            $this->error('删除失败');
        }
    }
}
