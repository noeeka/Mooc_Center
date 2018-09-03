<?php
/**
 * Created by PhpStorm.
 * User: 18534
 * Date: 2018/7/9
 * Time: 11:14
 */

namespace app\admin\controller;



use app\admin\model\AdvisoryModel;
use cmf\controller\AdminBaseController;
use think\Db;

class AdvisoryController extends AdminBaseController
{
    public function index(){
        $where=[];
        $param = $this->request->param();


        $keyword = $this->request->param('keyword');
        if(!empty($keyword)){
            $where['a.title']= ['like', "%$keyword%"];
        }

        $where['a.delete_time']=0;


        $advisoryModel = new AdvisoryModel();
        $advisory_list    = $advisoryModel->alias('a')
                            ->join('user u','u.id = a.user_id','left')
                            ->where($where)
                            ->order('a.create_time DESC')
                            ->paginate(15);
        foreach ($advisory_list as $value){
            $list = Db::name('user')->where('id',$value['reply_id'])->field('user_nickname')->find();
          var_dump($list['user_nickname']);die;
        }

        // 获取分页显示
        $advisory_list->appends($param);
        $page = $advisory_list->render();


        $this->assign('keyword', isset($keyword) ?$keyword : '');
        $this->assign('list', $advisory_list);

        $this->assign('page', $page);

        return $this->fetch();
    }
}