<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/3/13
 * Time: 14:37
 */

namespace app\api\controller;

use app\admin\model\VenueModel;
use app\admin\model\AreaModel;
use think\Db;
use token\Token;


class VenueController extends Base
{

    //获取场馆列表
    public function index()
    {

        //当前页
        $page = input('page', 1, 'intval');
        //区域id
        $area_id = input('area_id', 0, 'intval');
        //场馆类型id
        $venue_type_id = input('venue_type', 0, 'intval');
        //排序方式
        $order = input('order', 'publish_time');
        //一页显示条数
        $len = input('len', 6, 'intval');

        $where=[];
        if (!empty($area_id)) {
            $area_model = new AreaModel();
            $aids = $area_model->getTreeIds($area_id);
            array_unshift($aids , $area_id);
            $where['v.address'] = ['in', $aids];

        }
        if (!empty($venue_type_id)) {
            $where['v.venue_type'] = $venue_type_id;
        }

        $venueModel = new VenueModel();
        $list = $venueModel->alias('v')
            ->join('venue_type vt', 'v.venue_type =v.id','left')
            ->order($order.' desc')
            ->page($page, $len)
            ->where($where)
            ->field('v.*')
            ->select();
        $count = $venueModel->alias('v')
            ->join('venue_type vt', 'v.venue_type =v.id','left')
            ->where($where)
            ->count();

        foreach ($list as &$item) {
            $item['thumb'] = cmf_get_image_preview_url($item['thumb']);
            //评论数
            $comments_count=$this->comments_count($item['id']);
            if(!empty($comments_count)){
                $item['comment_count'] =$comments_count ;
            }else{
                $item['comment_count'] =0;
            }
            //点赞数
            $dianzan_count=$this->dianzan_count($item['id']);
            if(!empty($dianzan_count)){
                $item['dianzan_count'] =$dianzan_count ;
            }else{
                $item['dianzan_count'] =!empty($item['hit_like'])?$item['hit_like']:0;
            }
        }
        $result['list'] = $list;
        $result['count'] = $count;
        if ($result) {
            return $this->output_success(11101, $result, '获取场馆列表成功');
        } else {
            return $this->output_error(11102, '无banner列表');
        }
    }

    //获取场馆详情
    public function read()
    {
        $id = input('id', 0, 'intval');

        $venueModel = new VenueModel();
        $venue= $venueModel->where(['status' => 1, 'id' => $id])->find();
        $venue['thumb'] = cmf_get_image_preview_url($venue['thumb']);
        $venue['dianzan'] =0;
        $venue['comment_status'] =1;

        //判断点赞状态
        $token = $token = input('post.token');
        $user_id = Token::get_user_id($token);
        if(!empty($user_id)){
            $user_id = db('like')->where(['resource_id'=>$id, 'user_id'=>$user_id,'type'=>4])->value('id');
            if($user_id >0){
                $venue['dianzan']=1;
            }
        }

        if ($venue) {
            $venueModel->where('id', $id)->setInc('page_view');
            return $this->output_success(11101, $venue, '获取场馆详情成功');
        } else {
            return $this->output_error(11102, '获取场馆详情失败');
        }
    }

    //场馆点赞
    public function like()
    {
        $this->check_sign();

        $token = input('post.token');
        $user_id = Token::get_user_id($token);

        $data['resource_id'] = input('param.id', 0, 'intval');
        $data['type'] = 4;//场馆
        $data['user_id'] = $user_id;
        $id = db('like')->where($data)->value('id');
        if ($id > 0) {
            db('like')->where('id', $id)->delete();
            return $this->output_success('13100', [], '取消赞成功');
        } else {
            $data['create_time'] = time();
            db('like')->insert($data);
            return $this->output_success('13101', [], '点赞成功');
        }

    }

    //总分馆类型
    public function venue_type()
    {
        //获取总分馆类型
        $result=Db::name('venue_type')->select();

        if($result){
            return $this->output_success('13101',$result,'总分馆类型获取成功');
        }else{
            return $this->output_error('13100','','总分馆类型获取失败');
        }
    }
    //场馆评论数
    public function comments_count($venue_id){
        return Db::name('venue_comments')->where(['venueid'=>$venue_id])->count();
    }
    //场馆点赞数
    public function dianzan_count($venue_id){
        return Db::name('like')->where(['resource_id'=>$venue_id])->count();
    }

    public function map(){
        return json_decode('{"status":1,"code":17002,"data":[], "msg":"成功"}');
    }
}