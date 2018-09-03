<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/5/9
 * Time: 14:56
 */

namespace app\api\controller;

use app\admin\model\ActivityBaomingModel;
use app\admin\model\ActivityModel;
use think\Db;

class VolunRecruitController extends Base
{

    //活动招募列表页
    public function index(){
        $page = input('page',1,'intval');
        $len = input('len',10,'intval');
        $venue = input('venue',0,'intval');
        $area = input('area',0,'intval');
        $type = input('type',0,'intval');

        $where = [];
        $where['a.volun_type'] = ['neq',0];

        if(!empty($area)){
            $where['a.area'] = $area;
        }

        if(!empty($venue)){
            $where['a.venue'] = $venue;
        }

        if(!empty($type)){
            $where['a.volun_type'] = $type;
        }



        $recuritModel = new ActivityModel();
        $recuritList = $recuritModel
                        ->alias('a')
                        ->join('venue v','v.id = a.venue')
                        ->join('volun_type vt','vt.id =a.volun_type')
                        ->where($where)
                        ->field('v.name as venue_name,a.*,vt.name as type_name')
                        ->page($page,$len)
                        ->select()
                        ->toArray();


        $count = $recuritModel
            ->alias('a')
            ->join('venue v','v.id = a.venue')
            ->join('volun_type vt','vt.id =a.volun_type')
            ->where($where)
            ->count(1);


        if($recuritList){
            foreach($recuritList as $key=>$value){
                $recuritList[$key]['thumb'] = cmf_get_image_preview_url($value['thumb']);;
            }
            $recurit['list'] = $recuritList;
            $recurit['count'] = $count;
            return $this->output_success(11101,$recurit,'招募获取成功');
        }else{
            return $this->output_error(11102,'获取招募失败');
        }
    }


    //活动招募详情页
    public function read(){
        $id  = input('id',0,'intval');
        $uid = $this->getuid(false);

        $recuritModel = new ActivityModel();
        $recurit = $recuritModel->alias('r')->where(['r.id'=>$id])->join('venue v','v.id=r.venue')->field('r.*,v.name as venue_name')->find();
        $recurit['thumb'] = cmf_get_image_preview_url($recurit['thumb']);


        //报名审核通过人数
        $num = (new ActivityBaomingModel())->where(['activity_id'=>$id,'status'=>1])->count(1);
        $recurit['num'] = $num;
        unset($recurit['current_num']);


        //判断用户是否报名
        if(!empty($uid)){
            $count = db::name('activity_baoming')->where(['user_id'=>$uid,'activity_id'=>$id,'status'=>1])->count();
            if($count){
                $recurit['yibaoming'] = 1;
            }else{
                $recurit['yibaoming'] = 0;
            }
        }

        $recurit['volun_type'] = 2;

        if($recurit){
            return $this->output_success('13111', $recurit, '招募获取成功');
        }else{
            return $this->output_success('13101','招募获取失败');
        }

    }


    //报名
    public function baoming(){
        $this->check_sign();
        $token = input('post.token');
        $id = input('id',0,'intval');
        $user_id = Token::get_user_id($token);

        //活动最大报名人数 当前报名人数
        $activity = Db::name('activity')->where(['id' => $id])->find();
        $max_num = $activity['max_num'];
        $current_num = $activity['current_num'];

        //报名审核通过人数
        $num = (new ActivityBaomingModel())->where(['activity_id'=>$id,'status'=>1])->count(1);


        if (empty($user_id)) {
            return $this->output_error(13000, '未登录！');
        } else {
            if (empty($id)) {
                return $this->output_error(13001, '没有该活动！');
            } else {
                $user_role = db('user')->where(['id' => $user_id])->value('user_role');
                if($user_role == 2){  //是志愿者可以报名
                    if($num<$max_num){
                        //可以报名
                        $data['activity_id'] = $id;
                        $data['user_id'] = $user_id;
                        $date['create_time'] = time();

                        $activityBaomingModel = new ActivityBaomingModel();
                        $res = $activityBaomingModel->data($data)->save();

                        if ($res === false) {
                            return $this->output_error(13002, '报名失败！');
                        }else{
                            //更新报名人数
                            Db::name('activity')->where(['id' => $id])->update(['current_num' => $current_num + 1]);
                            return $this->output_success(13002, '', '报名成功！');
                        }
                    }else{
                        //报名人数已满
                        return $this->output_error(13005, '报名人数已满！');
                    }
                }else{
                    //非志愿者
                    //注册成为志愿者可报名
                    $this->output_error(13004, '请先注册为志愿者');
                }

            }
        }
    }
}