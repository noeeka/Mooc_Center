<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/5/22
 * Time: 15:05
 */

namespace app\admin\controller;

use app\admin\model\HomePageLogModel;
use app\admin\model\HomePageTplSpecialModel;
use cmf\controller\AdminBaseController;
class HomePageSpecialTplController  extends AdminBaseController
{
    public function index(){
        $specialModel = new HomePageTplSpecialModel();
        $specialList = $specialModel->select();

        $this->assign('list', $specialList);
        return $this->fetch();
    }

    public function add(){
        return $this->fetch();
    }

    public function addPost(){
        $data = $this->request->param();
        $data['create_time'] = time();
        $specialModel = new HomePageTplSpecialModel();
        $result = $specialModel->validate(true)->save($data);

        if($result === false){
            $this->error($specialModel->getError());
        }else{
            $id = $specialModel->getLastInsID();
            $data['id'] = $id;
            $this->addLog($data,1);

            $this->success('添加成功','home_page_special_tpl/index');
        }



    }

    public function edit(){
        $id = $this->request->param('id');

        $specialtpl = HomePageTplSpecialModel::get($id);

        $this->assign('specialtpl',$specialtpl);
        return $this->fetch();

    }

    public function editPost(){
        $data = $this->request->param();
        $data['update_time'] = time();

        $specialModel = new HomePageTplSpecialModel();
        $result = $specialModel->validate(true)->allowField(true)->isUpdate(true)->save($data);

        if($result === false){
            $this->error($specialModel->getError());
        }else{
            $this->addLog($data,0);
            $this->success('修改成功','home_page_special_tpl/index');
        }



    }

    public function delete(){
        $param = $this->request->param();

        if(isset($param['id'])){
            $id = $this->request->param('id',0,'intval');
            HomePageTplSpecialModel::destroy($id);
            $this->success("删除成功！", url("HomePageSpecialTpl/index"));
        }

        if(isset($param['ids'])){
            $ids = $this->request->param('ids/a');
            HomePageTplSpecialModel::destroy($ids);
            $this->success("删除成功！", url("HomePageSpecialTpl/index"));
        }

    }

    public function addLog($data,$is_default)
    {
        if(isset($data['css'])){
            $tablename = 'home_page_tpl_special';
            $field = 'css';
            $key = $tablename.';'.$field.';'.$data['id'];
            $value = $data['css'];

            $LogModel = new HomePageLogModel();
            $LogModel->addlog($key,$value,$is_default);
        }

    }
}