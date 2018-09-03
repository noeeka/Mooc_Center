<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/9
 * Time: 14:42
 */

namespace app\admin\controller;


use app\admin\model\ActivityBaomingModel;
use app\admin\model\ActivityModel;
use app\admin\model\UserModel;
use cmf\controller\AdminBaseController;
use think\Db;
class VolunScoreController extends AdminBaseController
{
    public function index()
    {
        $where   = [];
        $request = input('request.');

        if (!empty($request['uid'])) {
            $where['id'] = intval($request['uid']);
        }
        $where['user_role'] = 2;
        $keywordComplex = [];
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];

            $keywordComplex['sex|s.realname|score']    = ['like', "%$keyword%"];
        }
        $usersQuery = Db::name('user');

        $list = $usersQuery->alias('u')->join('sfzimg s','u.id =s.user_id')->whereOr($keywordComplex)->where($where)->order("score DESC")->field('u.*,s.realname')->paginate(10);
        // 获取分页显示
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch();
    }

    public function info()
    {
        $id = $this->request->param('id', 0, 'intval');
        $start_time = $this->request->param('start_time', '');
        $end_time = $this->request->param('end_time', '');
        $keyword = $this->request->param('keyword', '', 'trim');

        $where = [];
        if ($start_time != '') {
            $where['b.created_at'] = ['>=', strtotime($start_time)];
        }
        if ($end_time != '') {
            $where['b.created_at'] = ['<=', strtotime($end_time)];
        }
        if ($start_time != '' && $end_time != '') {
            $where['b.created_at'] = [['>=', strtotime($start_time)], ['<=', strtotime($end_time)]];
        }
        if (strlen($keyword) != 0) {
            $where['title'] = ['like', "%$keyword%"];
        }

        $user = UserModel::get($id);
        $activity = new ActivityBaomingModel();

        if (!empty($where)) {

            $list = $activity->alias('b')
                ->where(['b.user_id' => $id, 'b.status' => 1])
                ->where($where)
                ->join('activity a', 'a.id=b.activity_id','left')
                ->join('venue v', 'v.id=a.venue','left')
                ->field('b.*,a.title,v.name')
                ->order('b.created_at')
                ->select();
        } else {

            $list = $activity->alias('b')
                ->where(['b.user_id' => $id, 'b.status' => 1])
                ->join('activity a', 'a.id=b.activity_id','left')
                ->join('venue v', 'v.id=a.venue','left')
                ->field('b.*,a.title,v.name')
                ->order('b.created_at')
                ->select();
        }
        $origin_score=0;
        foreach ($list as $v){
            $origin_score+=$v['score'];
        }
        $this->assign('user', $user);
        $this->assign('list', $list);
        $this->assign('origin_score', $origin_score);
        return $this->fetch();
    }
    public function editPost(){
        $id        = $this->request->param('id', 0, 'intval');
        $score        = $this->request->param('score', 0, 'intval');
        $user = new UserModel();
        $res = $user->where(['id'=>$id])->update(['score'=>$score]);
        if ($res=== false) {
            $this->error($res->getError());
        }

        $this->success("保存成功！", url("VolunScore/index"));
    }

    /**
     * 活动结束三天后如果未派发积分自动加上活动活跃积分
     */
    public function actionthreedays(){
        $baomingModel = new ActivityBaomingModel();
        $baomingList = $baomingModel->alias('b')->join('activity a', 'a.id=b.activity_id', 'left')->field('b.*,a.score as activity_score,a.end_time')->where(['b.score' => 0, 'a.type' => 0, 'b.status' => 1])->select()->toArray();

        foreach ($baomingList as $value) {
            $timestamp = 3600 * 24 * 3;

            if (time() > ($value['end_time'] + $timestamp) && $value['score'] == 0) {
                $baomingModel->where('id', $value['id'])->update(['score' => $value['activity_score']]);

                $activity_id = $value['activity_id'];
                $activity_score = $value['activity_score'];
                $user_id = $value['user_id'];
                //给用户加总积分
                $sql = "update cxtj_user u,cxtj_activity_baoming b set u.score=(if((u.score-b.score+$activity_score)>0,u.score-b.score+$activity_score,0)) where u.id = b.user_id and u.id=$user_id and b.activity_id=$activity_id";

                Db::name('user')->query($sql);
            }
        }

    }

    public function toggle()
    {
        $data      = $this->request->param();
        $user = new UserModel();

        if (isset($data['ids']) && !empty($data["display"])) {
            $ids = $this->request->param('ids/a');
            $user->where(['id' => ['in', $ids]])->update(['status' => 1]);
            $this->success("更新成功！");
        }

        if (isset($data['ids']) && !empty($data["hide"])) {
            $ids = $this->request->param('ids/a');
            $user->where(['id' => ['in', $ids]])->update(['status' => 0]);
            $this->success("更新成功！");
        }


    }

}