<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/14
 * Time: 10:09
 */

namespace app\admin\controller;


use app\admin\model\MienModel;
use cmf\controller\AdminBaseController;
use think\Db;
class MienController extends AdminBaseController
{
    public function index()
    {
        $param = $this->request->param();
        $keyword = $this->request->param('keyword', '', 'trim');
        $where =[];
        if (strlen($keyword) != 0) {
            $where['title'] = ['like', "%$keyword%"];
        }
        $mien = new MienModel();

        $list = $mien->where(['status'=>1,'delete_time'=>0])->where($where)->order('list_order ASC')->order('id DESC')->paginate(10);
        $list->appends($param);
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('articles', $list->items());

        $this->assign('page', $list->render());

        return $this->fetch();
    }


    public function add()
    {

        return $this->fetch();
    }


    public function addPost()
    {
        if ($this->request->isPost()) {
            $data   = $this->request->param();
            $post   = $data['post'];

            $result = $this->validate($post, 'Mien');

            if ($result !== true) {
                $this->error($result);
            }

            $mienModel = new MienModel();

//            if (!empty($data['photo_names']) && !empty($data['photo_urls'])) {
//                $data['post']['thumb'] = [];
//                foreach ($data['photo_urls'] as $key => $url) {
//                    $photoUrl = cmf_asset_relative_url($url);
//                    array_push($data['post']['thumb'], ["url" => $photoUrl, "name" => $data['photo_names'][$key]]);
//                }
//            }
            if (!empty($data['photo_names']) && !empty($data['photo_urls'])) {
                $data['post']['imgs'] = [];
                foreach ($data['photo_urls'] as $key => $url) {
                    $photoUrl = cmf_asset_relative_url($url);
                    array_push($data['post']['imgs'] , ["url" => $photoUrl, "name" => $data['photo_names'][$key]]);
                }
            }
            $data['post']['create_time'] = time();
            $mienModel->adminAddActivity($data['post']);
            $this->success('添加成功!', url('Mien/index'));
        }
    }


    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');

        $activity = MienModel::get($id);

        $this->assign('activity', $activity);

        return $this->fetch();
    }


    public function editPost()
    {
        $data = $this->request->param();
        $post = $data['post'];
        $result = $this->validate($post, 'Mien');
        if ($result !== true) {
            $this->error($result);
        }

        $performModel = new MienModel();
        if (!empty($data['photo_names']) && !empty($data['photo_urls'])) {
            $data['post']['imgs'] = [];
            foreach ($data['photo_urls'] as $key => $url) {
                $photoUrl = cmf_asset_relative_url($url);
                array_push($data['post']['imgs'] , ["url" => $photoUrl, "name" => $data['photo_names'][$key]]);
            }
        }
        $data['post']['update_time'] =time();
        $performModel->adminEditActivity($data['post']);
        $this->success("保存成功！", url("Mien/index"));


    }


    public function delete()
    {
        $param           = $this->request->param();
        $portalPostModel = new MienModel();

        if (isset($param['id'])) {
            $id           = $this->request->param('id', 0, 'intval');
            $result       = $portalPostModel->where(['id' => $id])->find();

            $resultPortal = $portalPostModel
                ->where(['id' => $id])
                ->update(['delete_time' => time()]);

            $this->success("删除成功！", '');

        }

        if (isset($param['ids'])) {
            $ids     = $this->request->param('ids/a');
            $recycle = $portalPostModel->where(['id' => ['in', $ids]])->select();
            $result  = $portalPostModel->where(['id' => ['in', $ids]])->update(['delete_time' => time()]);

                $this->success("删除成功！", '');
            }

    }









    public function listOrder()
    {
        $bannerModel = new  MienModel();
        parent::listOrders($bannerModel);
        $this->success("排序更新成功！");
    }


    public function toggle()
    {
        $data      = $this->request->param();
        $bannerModel = new MienModel();

        if (isset($data['ids']) && !empty($data["display"])) {
            $ids = $this->request->param('ids/a');
            $bannerModel->where(['id' => ['in', $ids]])->update(['status' => 1, 'publish_time' => time()]);
            $this->success("更新成功！");
        }

        if (isset($data['ids']) && !empty($data["hide"])) {
            $ids = $this->request->param('ids/a');
            $bannerModel->where(['id' => ['in', $ids]])->update(['status' => 0, 'publish_time' => time()]);
            $this->success("更新成功！");
        }


    }

}