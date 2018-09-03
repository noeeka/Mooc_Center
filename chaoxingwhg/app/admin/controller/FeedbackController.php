<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/1
 * Time: 15:03
 */

namespace app\admin\controller;


use app\admin\model\FeedbackModel;
use cmf\controller\AdminBaseController;

class FeedbackController extends AdminBaseController
{
    public function index()
    {

        $where=[];
        $param = $this->request->param();
        $keyword = $this->request->param('keyword');
        if(!empty($keyword)){
            $where['name']= ['like', "%$keyword%"];
        }
        $feedbackModel = new FeedbackModel();
        $feedbacks     = $feedbackModel->where($where)->order('created_at DESC')->paginate(5);;

        // 获取分页显示
        $feedbacks->appends($param);
        $page = $feedbacks->render();

        $this->assign('feedbacks', $feedbacks);
        $this->assign('keyword', isset($keyword) ?$keyword : '');
        $this->assign('page', $page);

        return $this->fetch();
    }



    public function edit()
    {
        $id        = $this->request->param('id', 0, 'intval');
        $feedbackModel = FeedbackModel::get($id);
        $this->assign('feedback', $feedbackModel);
        return $this->fetch();
    }


    public function editPost()
    {
        $data      = $this->request->param();
        $feedbackModel = new FeedbackModel();
        $result    = $feedbackModel->validate(true)->allowField(true)->isUpdate(true)->save($data);
        if ($result === false) {
            $this->error($feedbackModel->getError());
        }

        $this->success("保存成功！", url("feedback/index"));
    }


    public function delete()
    {
        $id = $this->request->param('id', 0, 'intval');
        FeedbackModel::destroy($id);

        $this->success("删除成功！", url("feedback/index"));
    }


    public function listOrder()
    {
        $feedbackModel = new  FeedbackModel();
        parent::listOrders($feedbackModel);
        $this->success("排序更新成功！");
    }


    public function toggle()
    {
        $data      = $this->request->param();
        $feedbackModel = new FeedbackModel();

        if (isset($data['ids']) && !empty($data["display"])) {
            $ids = $this->request->param('ids/a');
            $feedbackModel->where(['id' => ['in', $ids]])->update(['status' => 1]);
            $this->success("更新成功！");
        }

        if (isset($data['ids']) && !empty($data["hide"])) {
            $ids = $this->request->param('ids/a');
            $feedbackModel->where(['id' => ['in', $ids]])->update(['status' => 0]);
            $this->success("更新成功！");
        }


    }


}