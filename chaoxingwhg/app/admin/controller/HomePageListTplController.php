<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/5/22
 * Time: 14:47
 */

namespace app\admin\controller;

use app\admin\model\HomePageLogModel;
use app\admin\model\HomePageTplModel;
use cmf\controller\AdminBaseController;

class HomePageListTplController  extends AdminBaseController
{
    //显示模板列表
    public function index()
    {
        $homePageModel = new HomePageTplModel();
        $homePageList = $homePageModel->paginate(10);

        $page = $homePageList->render();

        $this->assign('list', $homePageList);
        $this->assign('page',$page);
        return $this->fetch();
    }


    public function add()
    {
        return $this->fetch();
    }

    //添加数据
    public function addPost()
    {
        $data      = $this->request->param();
        $data['creat_time'] = time();

        $homePageModel = new HomePageTplModel();
        $result = $homePageModel->validate(true)->allowField(true)->save($data);

        if($result === false){
            $this->error($homePageModel->getError());
        }else{
            $id = $homePageModel->getLastInsID();
            $data['id'] = $id;
            $this->addLog($data,1);

            $this->success("添加成功",'home_page_list_tpl/index');
        }


    }

    public function edit()
    {
        $id = $this->request->param('id',0,'intval');

        $homePageModel = new HomePageTplModel();
        $moban_msg = $homePageModel->where('id',$id)->find();

        $this->assign('moban_msg',$moban_msg);
        return $this->fetch();


    }


    public function editPost()
    {
        $data = $this->request->param();
        $data['update_time'] = time();

        $homePageModel = new HomePageTplModel();
        $result = $homePageModel->validate(true)->allowField(true)->isUpdate(true)->save($data);
        if($result === false){
            $this->error($homePageModel->getError());
        }else{
            $this->addLog($data,0);
            $this->success('保存成功','home_page_list_tpl/index');
        }



    }

    public function delete()
    {
        $param = $this->request->param();

        if(isset($param['id'])){
            $id = $this->request->param('id',0,'intval');
            HomePageTplModel::destroy($id);
        }

        if(isset($param['ids'])){
            $ids = $this->request->param('ids/a');
            HomePageTplModel::destroy($ids);
        }

        $this->success("删除成功！", url("home_page_list_tpl/index"));
    }

    public function addLog($data,$is_default)
    {
        $tablename = 'home_page_tpl';
        $first_field = 'html';
        $first_key = $tablename.';'.$first_field.';'.$data['id'];
        $first_value = $data['html'];

        $LogModel = new HomePageLogModel();
        $LogModel->addlog($first_key,$first_value,$is_default);

        if(isset($data['css'])){
            $second_field = 'css';
            $second_key = $tablename.';'.$second_field.';'.$data['id'];
            $second_value = $data['css'];
            $LogModel->addlog($second_key,$second_value,$is_default);
        }


    }


}