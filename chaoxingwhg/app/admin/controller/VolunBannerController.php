<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/3/9
 * Time: 13:38
 */


namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use app\admin\model\VolunBannerModel;


class VolunBannerController extends AdminBaseController
{
    public function index()
    {
        $param = $this->request->param();
        $volunBannerModel = new VolunBannerModel();
        $volunBanners   = $volunBannerModel->order('list_order ASC')->where('type',2)->paginate(10);

        //获取分页显示
        $volunBanners->appends($param);
        $page = $volunBanners->render();

        $this->assign('volunbanners', $volunBanners->items());
        $this->assign('page',$page);
        return $this->fetch();
    }


    public function add()
    {
        return $this->fetch();
    }


    public function addPost()
    {
        $data      = $this->request->param();
//        print_r($data);die;

        $data['publish_time'] = strtotime($data['publish_time']);
//        echo $data['publish_time'];die;
        $data['type'] = 2;
        $data['status'] =1;
//        var_dump($data);die;
        $volunBannerModel = new VolunBannerModel();
        $result    = $volunBannerModel->validate(true)->allowField(true)->save($data);
        if ($result === false) {
            $this->error($volunBannerModel->getError());
        }

        $this->success("添加成功！", url("VolunBanner/index"));
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
        $volunBannerModel = VolunBannerModel::get($id);
        $this->assign('volunbanners', $volunBannerModel);
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
        $volunBannerModel = new VolunBannerModel();
        $result    = $volunBannerModel->validate(true)->allowField(true)->isUpdate(true)->save($data);
        if ($result === false) {
            $this->error($volunBannerModel->getError());
        }

        $this->success("保存成功！", url("VolunBanner/index"));
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
        VolunBannerModel::destroy($id);

        $this->success("删除成功！", url("VolunBanner/index"));
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
        $volunBannerModel = new  VolunBannerModel();
        parent::listOrders($volunBannerModel);
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
        $volunBannerModel = new VolunBannerModel();

        if (isset($data['ids']) && !empty($data["display"])) {
            $ids = $this->request->param('ids/a');
            $volunBannerModel->where(['id' => ['in', $ids]])->update(['status' => 1]);
            $this->success("更新成功！");
        }

        if (isset($data['ids']) && !empty($data["hide"])) {
            $ids = $this->request->param('ids/a');
            $volunBannerModel->where(['id' => ['in', $ids]])->update(['status' => 0]);
            $this->success("更新成功！");
        }


    }
}