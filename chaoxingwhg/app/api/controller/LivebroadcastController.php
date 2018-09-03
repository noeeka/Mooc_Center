<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/13
 * Time: 14:39
 */

namespace app\api\controller;


use app\admin\model\AreaModel;
use app\admin\model\LiveBroadcastModel;
use think\Exception;

class LivebroadcastController extends Base
{
    public function index(){
        $page = input('param.page', 1, 'intval');
        $len = input('param.len', 10, 'intval');
        $perform_type = input('param.typeid', 0, 'intval');
        $address  = input('param.address', 0, 'intval');
        $venue  = input('param.venue', 0, 'intval');
        $where = [];
        $type = input('param.type',0,'intval');
        $page = input('param.page', 1, 'intval');
        $len = input('param.len', 6, 'intval');
        $perform_type = input('param.typeid', 0, 'intval');
        $address  = input('param.address', 0, 'intval');
        $venue  = input('param.venue', 0, 'intval');
        $where = [];

        if($perform_type != 0){
            if($perform_type == 1){
                $where =['r.start_time'=>['>',time()]];
            }elseif ($perform_type == 2){
                $where =['r.start_time'=>['<=', time()],'r.end_time'=>['>=',time()]];
            }else{
                $where = ['r.end_time'=>['<',time()]];
            }

        }
        if ($type!=0){
            $where['r.type']=$type;
        }
        if($address != 0){
            $ids = $areaModel = (new AreaModel())->getTreeIds($address);
            array_unshift($ids, $address);
            $where['r.areaid'] = ['in', $ids];
        }
        if($venue !=0){
            $where['r.venueid'] =$venue;
        }

        $roomModel = new LiveBroadcastModel();
        $rooms = $roomModel->alias('r')->join('__VENUE__ v', 'r.venueid = v.id')->field('r.*,v.name')->where($where)->order('r.id desc')->page($page, $len)->select()->toArray();
        $num = $roomModel->alias('r')->join('__VENUE__ v', 'r.venueid = v.id')->where($where)->order('r.id desc')->count(1);



        if (empty($rooms)) {
            return $this->output_success('13100', [],'文化直播获取失败');
        } else {
            //图片处理
            foreach($rooms as $k=>$v){
                $rooms[$k]['img'] = cmf_get_image_preview_url($v['img']);
                $rooms[$k]['playback_link'] = htmlspecialchars_decode($v['playback_link']);
                $rooms[$k]['live_link'] = htmlspecialchars_decode($v['live_link']);
                $rooms[$k]['wx_live_link'] = htmlspecialchars_decode($v['wx_live_link']);
                $rooms[$k]['wx_playback_link'] = htmlspecialchars_decode($v['wx_playback_link']);
            }
            $data = ['num'=>$num,'list'=>$rooms];
            return $this->output_success('13101',$data , '文化直播获取成功');
        }
    }

    /*
     * 直播详情页
     */
    public function read(){
        $id = input('param.id',0,'intval');
        $liveBroadcast = new LiveBroadcastModel;
        $result = $liveBroadcast->where('id',$id)->find();
        if($liveBroadcast){
            return $this->output_success('13101', $result->live_link, '文化直播获取成功');
        }else{
            return $this->output_error('13100', '文化直播获取失败');
        }

    }
}