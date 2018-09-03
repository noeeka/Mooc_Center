<?php
/**
 * Created by PhpStorm.
 * User: 18534
 * Date: 2018/8/15
 * Time: 9:41
 */

namespace app\admin\controller;


use cmf\controller\AdminBaseController;
use think\Db;
use app\admin\model\NavigationModel;
class TnavigationController extends AdminBaseController
{
    /*
     * 地方特色文化资源库
     * */
    public function index()
    {
        $navModel = new NavigationModel();
        $navigationTree = $navModel->adminNavigationTableTree(0,'',3);
        $this->assign('nav_list', $navigationTree);
        return $this->fetch();
    }
    /**
     * 首页导航添加
     */
    public function add()
    {
        $id=$this->request->param('parent',0,'intval');

        $navigationModel=new NavigationModel();

        $nav_tree = $navigationModel->adminNavigationTree($id,0,0,[],3);
        $this->assign('nav_tree',$nav_tree);

        return $this->fetch();
    }

    /**
     * 首页导航添加提交
     */
    public function addPost(){
        if($this->request->isPost()){
            $data=$this->request->param();
            $data['post']['type']=3;
            $post=$data['post'];
            $result=$this->validate($post,'navigation');
            if($result !==true){
                $this->error($result);
            }

            $nav_model = new NavigationModel();
            $res=$nav_model->allowField(true)->save($post);
            if($res ===false ){
                $this->error($nav_model->getError());
            }
            $this->success('添加成功',url('Tnavigation/index'));
        }
    }
    /**
     * 首页导航编辑
     */
    public function edit()
    {
        $id=$this->request->param('id',0,'intval');

        $navigationModel=new NavigationModel();
        $nav=$navigationModel->where(['id'=>$id])->find();
        $nav_tree = $navigationModel->adminNavigationTree($nav['parent_id']);

        $this->assign('nav_tree',$nav_tree);
        $this->assign('nav',$nav);

        return $this->fetch();
    }

    /**
     * 首页导航编辑提交
     */
    public function editPost()
    {
        if($this->request->isPost()){
            $data =$this->request->param();
            $post = $data['post'];
            $result=$this->validate($post,'Navigation');
            if($result !==true){
                $this->error($result);
            }

            $navigationModel=new NavigationModel();
            $navigationModel->allowField(true)->isUpdate(true)->save($post);
            $this->success('保存成功!','Tnavigation/index');
        }
    }
    /**
     * 首页导航排序
     */
    public function listOrder()
    {
        $navigationModel = new NavigationModel();
        parent::listOrders($navigationModel);
        $this->success("排序更新成功！");
    }

    /**
     * 首页导航删除
     */
    public function delete()
    {
        $id    = $this->request->param("id", 0, 'intval');
        $count = Db::name('navigation')->where(["parent_id" => $id])->count();
        if ($count > 0) {
            $this->error("该导航下还有子导航，无法删除！");
        }
        if (Db::name('navigation')->delete($id) !== false) {
            $this->success("删除导航成功！");
        } else {
            $this->error("删除失败！");
        }
    }

}