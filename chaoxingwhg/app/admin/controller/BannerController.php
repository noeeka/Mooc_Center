<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/1
 * Time: 10:58
 */

namespace app\admin\controller;


use app\admin\model\BannerModel;

use cmf\controller\AdminBaseController;
class BannerController extends AdminBaseController
{
    public function index()
    {
        $bannerModel = new BannerModel();
        $banners   = $bannerModel->where('type','in',[1,3])->order('list_order ASC')->select();
        foreach($banners as &$v){
            if(strpos($v['img'],'http')!==false){
                $v['is_local']=0;
            }elseif(strpos($v['img'],'https')!==false){
                $v['is_local']=0;
            }else{
                $v['is_local']=1;
            }
        }

        $this->assign('banners', $banners);

        return $this->fetch();
    }


    public function add()
    {
        return $this->fetch();
    }


    public function addPost()
    {
        $data      = $this->request->param();
        $data['publish_time'] = strtotime($data['publish_time']);
        $bannerModel = new BannerModel();
        $result    = $bannerModel->validate(true)->allowField(true)->save($data);
        if ($result === false) {
            $this->error($bannerModel->getError());
        }

        $this->success("添加成功！", url("banner/index"));
    }

    /**
     * 编辑友情链接
     * @adminMenu(
     *     'name'   => '编辑友情链接',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑友情链接',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $id        = $this->request->param('id', 0, 'intval');
        $bannerModel = BannerModel::get($id);
        $this->assign('banner', $bannerModel);
        return $this->fetch();
    }

    /**
     * 编辑友情链接提交保存
     * @adminMenu(
     *     'name'   => '编辑友情链接提交保存',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑友情链接提交保存',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        $data      = $this->request->param();
        $data['publish_time'] = strtotime($data['publish_time']);
        $bannerModel = new BannerModel();
        $result    = $bannerModel->validate(true)->allowField(true)->isUpdate(true)->save($data);
        if ($result === false) {
            $this->error($bannerModel->getError());
        }

        $this->success("保存成功！", url("banner/index"));
    }

    /**
     * 删除友情链接
     * @adminMenu(
     *     'name'   => '删除友情链接',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除友情链接',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $id = $this->request->param('id', 0, 'intval');
        BannerModel::destroy($id);

        $this->success("删除成功！", url("banner/index"));
    }

    /**
     * 友情链接排序
     * @adminMenu(
     *     'name'   => '友情链接排序',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '友情链接排序',
     *     'param'  => ''
     * )
     */
    public function listOrder()
    {
        $bannerModel = new  BannerModel();
        parent::listOrders($bannerModel);
        $this->success("排序更新成功！");
    }

    /**
     * 友情链接显示隐藏
     * @adminMenu(
     *     'name'   => '友情链接显示隐藏',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '友情链接显示隐藏',
     *     'param'  => ''
     * )
     */
    public function toggle()
    {
        $data      = $this->request->param();
        $bannerModel = new BannerModel();

        if (isset($data['ids']) && !empty($data["display"])) {
            $ids = $this->request->param('ids/a');
            $bannerModel->where(['id' => ['in', $ids]])->update(['status' => 1]);
            $this->success("更新成功！");
        }

        if (isset($data['ids']) && !empty($data["hide"])) {
            $ids = $this->request->param('ids/a');
            $bannerModel->where(['id' => ['in', $ids]])->update(['status' => 0]);
            $this->success("更新成功！");
        }


    }

}