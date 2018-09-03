<?php
/**
 * Created by PhpStorm.
 * User: 18534
 * Date: 2018/7/9
 * Time: 11:14
 */

namespace app\admin\controller;



use app\admin\model\AdvisoryModel;
use app\admin\model\AnswerModel;
use cmf\controller\AdminBaseController;
use think\Config;
use think\Db;

class AnswerController extends AdminBaseController
{
    public function index(){
        $where = [];
        $param = $this->request->param();
        $keyword = $this->request->param('keyword');

        if(!empty($keyword)){
            $where['a.title']= ['like', "%$keyword%"];
        }

        $where['delete_time'] = 0;
        $advisoryModel = new AnswerModel();
        $advisory_list    = $advisoryModel->alias('a')
                            ->where($where)
                            ->order('a.create_time DESC')
                            ->paginate(15);

        // 获取分页显示
        $advisory_list->appends($param);
        $page = $advisory_list->render();

        $this->assign('keyword', isset($keyword) ?$keyword : '');
        $this->assign('list', $advisory_list);
        $this->assign('page', $page);

        return $this->fetch();
    }


    /**
     * 活动删除
     */
    public function delete()
    {
        $param = $this->request->param();
        $answerModel = new AnswerModel();
        if(isset($param['id'])){
            $id = $param['id'];
            $answerModel->where(['id' => $id])->update(['delete_time' => time()]);
            //删除活动报名信息

            Db::name('activity_baoming')->where(['activity_id'=>$id])->delete();

            $this->success("删除成功！", '');
        }

        if (isset($param['ids'])) {
            $ids = $this->request->param('ids/a');
            $answerModel->where(['id' => ['in', $ids]])->update(['delete_time' => time()]);
            $this->success("删除成功！", '');
        }
    }


    /**
     * 编辑活动
     */
    public function edit()
    {

        $id = $this->request->param('id', 0, 'intval');
        $answer = AnswerModel::get($id);

        $this->assign('answer', $answer);
        return $this->fetch();
    }

    public function editPost()
    {
        $data = $this->request->param();
        if (empty($data['reply']))
            $this->error("回答不能为空");

        $answerModel = new AnswerModel();
        $update = $answerModel->where('id' , $data['id'])->update([
           "reply" => $data['reply'] ,
           "status" => $data['status']
        ]);

        $this->success("保存成功！", url("answer/index"));
    }
}