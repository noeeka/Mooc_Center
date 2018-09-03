<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/1
 * Time: 17:44
 */

namespace app\admin\controller;


use app\admin\model\PerformTypeModel;
use app\admin\model\SfzimgModel;
use app\admin\model\UserModel;
use cmf\controller\AdminBaseController;
use think\Db;

class LibrarytypeController extends AdminBaseController
{

    public function index()
    {
        $param = $this->request->param();
        $keyword = $this->request->param('keyword', '', 'trim');

        $where = [];

        if (strlen($keyword) != 0) {
            $where['name'] = ['like', "%$keyword%"];
        }




        if(!empty($where)){

            $performtype = new PerformTypeModel();

            $data = $performtype
                ->where(['type'=>2,'status'=>1])
                ->where($where)
                ->paginate(15)
                ->each(function($item, $key){
//                var_dump($item);
//                var_dump($item['typeid']);die;
                    $type = UserModel::get($item['update_owner']);
                    $item['update_owner'] = $type == null ? '': $type->user_nickname;


                    $owner = UserModel::get($item['owner_id']);
                    $item['owner'] = $owner == null ? '': $owner->user_nickname;
                    return $item;
                });
            $this->assign('data',$data);
            $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
//            var_dump($where);die;


        }else{
            $performtype = new PerformTypeModel();

            $data        = $performtype
                ->where(['type'=>2,'status'=>1])
                ->paginate(15)
                ->each(function($item, $key){
//                var_dump($item);
//                var_dump($item['typeid']);die;
                    $type = UserModel::get($item['update_owner']);
                    $item['update_owner'] = $type == null ? '': $type->user_nickname;


                    $owner = UserModel::get($item['owner_id']);
                    $item['owner'] = $owner == null ? '': $owner->user_nickname;
                    return $item;
                });

            $this->assign('data',$data);
        }


        return $this->fetch();
    }

    public function add()
    {
        return $this->fetch();
    }


    public function addPost()
    {
        $data      = $this->request->param();
//        $data['publish_time'] = strtotime($data['publish_time']);
        $data['type'] = 2;
        $data['owner_id'] = cmf_get_current_admin_id();
        $data['update_owner'] = cmf_get_current_admin_id();
        $data['create_time'] =time();
        $performtype = new PerformTypeModel();
        $result    = $performtype->validate(true)->allowField(true)->save($data);
        if ($result === false) {
            $this->error($performtype->getError());
        }

        $this->success("添加成功！", url("librarytype/index"));
    }

    public function edit()
    {

        $id        = $this->request->param('id', 0, 'intval');
        $res = PerformTypeModel::get($id);


        $this->assign('res', $res);
        return $this->fetch();
    }


    public function editPost()
    {
        $data      = $this->request->param();

        $data['update_time'] =time();
        $data['update_owner'] = cmf_get_current_admin_id();





        $sfzimgModel = new PerformTypeModel();

        $result    = $sfzimgModel->validate(true)->allowField(true)->isUpdate(true)->save($data);
        if ($result === false) {
            $this->error($sfzimgModel->getError());
        }

        $this->success("保存成功！", url("librarytype/index"));
    }
    public function delete()
    {
        $id = $this->request->param('id', 0, 'intval');
        PerformTypeModel::destroy($id);

        $this->success("删除成功！", url("librarytype/index"));
    }




    public function listOrder()
    {
        parent::listOrders(Db::name('portal_category_post'));
        $this->success("排序更新成功！", '');
    }

    public function move()
    {

    }

    public function copy()
    {

    }


}