<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/5
 * Time: 16:57
 */

namespace app\api\controller;


use app\admin\model\AreaModel;
use app\admin\model\RoomApplyModel;
use app\admin\model\RoomModel;
use app\admin\model\RoomTypeModel;
use think\Db;

class RoomController extends Base
{

    public function index()
    {
        $sort = input('param.sort', '');
        $page = input('param.page', 1, 'intval');
        $len = input('param.len', 10, 'intval');
        $venue_type = input('param.typeid', 0, 'intval');
        $venue = input('param.venue', 0, 'intval');
        $address = input('param.area', 0, 'intval');
        $where['r.status'] = 1;
        $order = [];
        if ($venue_type != 0) {
            $where['r.venue_type'] = $venue_type;
        }
        if ($venue != 0) {
            $where['v.id'] = $venue;
        }
        if ($address != 0) {
            $ids = $areaModel = (new AreaModel())->getTreeIds($address);
            array_unshift($ids, $address);
            $where['v.address'] = ['in', $ids];
        }
        if ($sort == 1) {
            $order['r.page_view'] = 'desc';
        } else {
            $order['r.publish_time'] = 'desc';
        }
        $where['v.status'] = 1;
        $roomModel = new RoomModel();
        $rooms = $roomModel->alias('r')->join('__VENUE__ v', 'r.venue = v.id')->field('r.*,v.name as venue_name')->where($where)->order($order)->page($page, $len)->select();
        $count = $roomModel->alias('r')->join('__VENUE__ v', 'r.venue = v.id')->where($where)->count();
        if (empty($rooms)) {
            return $this->output_error('13100', '场馆获取失败');
        } else {
            $rooms = $rooms->toArray();
            foreach ($rooms as &$v) {
                $v['banner'] = json_decode($v['banner'],1);
                if (count($v['banner']) > 0) {
                    $v['thumb'] = $v['banner'][0]['url']; //设置第一张图为封面图
                    foreach ($v['banner'] as &$item) {
                        $item['url'] =  cmf_get_image_preview_url($item['url']);
                    }
                }
            }
            return $this->output_success('13101', ['list' => $rooms, 'count' => $count], '场馆获取成功');
        }
    }

    public function read()
    {
        $id = input('param.id', 0, 'intval');
        $rooms = (new RoomModel())->alias('r')
            ->join('__VENUE__ v', 'r.venue = v.id')
            ->field('r.*,v.name as venue_name,r.cost,r.banner')
            ->where('r.id', $id)->find()->toArray();

        $rooms['banner'] = json_decode($rooms['banner'] , 1);

        foreach ($rooms['banner'] as &$banner) {
            $banner['url'] = cmf_get_image_preview_url($banner['url']);
        }

        if (empty($rooms)) {
            return $this->output_error(13001, '场馆获取失败');
        } else {
            $rooms['thumb_url'] = cmf_get_image_preview_url($rooms['thumb']);
            return $this->output_success('13101', $rooms, '场馆获取成功');
        }
    }

    public function read_book()
    {
        $uid = $this->getuid(false);
        $room_id = input('param.room_id', 0, 'intval');
        $apply = RoomApplyModel::where([ 'room_id' => $room_id, 'start_time' => ['egt', time()]])->field('user_id,start_time')->select()->toArray();
        $res = ['my'=>[], 'other'=>[], 'num'=>count($apply)];
        foreach ($apply as $v){
            if($v['user_id'] == $uid){
                $res['my'][] = $v['start_time'];
            }else{
                $res['other'][] = $v['start_time'];
            }
        }
        return $this->output_success('13101', $res, '场馆获取成功');
    }

    /**
     * 预约
     */
    public function book()
    {
        $user_id = $this->getuid();
        $room_id = input('param.room_id', 7, 'intval');
        $start_time = input('param.start_time', 0, 'intval');
        $roomApplyModel = new RoomApplyModel();
        $book = $roomApplyModel->book($room_id, $user_id, $start_time);
        if ($book === true) {
            return $this->output_success(13120, [], '预约成功');
        } else {
            return $this->output_error(13011, $book);
        }
    }
    /**
     * 判断是否登录
     */
    public function is_login()
    {

        $user_id = $this->getuid();

        if ($user_id) {
           
            return $this->output_success(13120, $user_id, '已登陆');
        } else {
            return $this->output_error(13011, '未登录');
        }
    }

    public function type()
    {
        $list = RoomTypeModel::where(['status' => 1])->select();
        return $this->output_success(13120, $list, '获取类型成功');
    }

    public function book_filter()
    {
        $uid = $this->getuid();
        $address = input('param.address', 0, 'intval');
        $venue = input('param.venue', 0, 'intval');
        $status = input('param.status', 0, 'intval');
        $start_time = input('param.start_time', 0, 'intval');
        $end_time = input('param.end_time', 0, 'intval');
        $page = input('param.page', 1, 'intval');
        $len = input('param.len', 10, 'intval');
        $where = ['a.user_id' => $uid];
        $join = [['__ROOM__ r', 'r.id=a.room_id'], ['__VENUE__ v', 'v.id=r.venue']];
        $where['v.status'] = 1;
        if ($address != 0) {
            $ids = (new AreaModel())->getTreeIds($address);
            $ids[] = $address;
            $where['v.address'] = ['in', $ids];
        }
        if ($venue != 0) {
            $where['r.venue'] = $venue;
        }
        if ($status != 0) {
            //1 已完成 2 已预约
            $where['end_time'] = $status == 1 ? ['elt', time()] : ['egt', time()];
        }
        if ($start_time != 0) {
            $where['start_time'] = ['egt', $start_time];
        }
        if ($end_time != 0) {
            $where['end_time'] = ['elt', $end_time];
        }
        $list = (new RoomApplyModel())
            ->alias('a')
            ->join($join)
            ->where($where)
            ->field('a.*, v.name as venue_name,r.full_address,r.name')
            ->order(['create_time' => 'desc'])
            ->page($page, $len)
            ->select();
        $count = (new RoomApplyModel())
            ->alias('a')
            ->join($join)
            ->where($where)
            ->count(1);
        $now = time();
        foreach ($list as &$v){
            $v['status'] = $now > $v['end_time'] ? 1 : 2;
        }
        return $this->output_success(13120, ['list' => $list, 'count' => $count], '获取预定信息成功');
    }

    public function cancel()
    {
        $uid = $this->getuid();
        $book_id = input('param.id', 0, 'intval');
        $apply = RoomApplyModel::where(['user_id' => $uid, 'id' => $book_id])->find();
        if(empty($apply)){
            return $this->output_error(13020, '预约不存在');
        }else{
            $now = time();
            if($now >= $apply->start_time && $now <= $apply->end_time){
                return $this->output_error(13021, '进行中,无法取消');
            }else{
                RoomApplyModel::where('id', $apply->id)->delete();
                return $this->output_success(13120, [], '取消预约成功');
            }
        }
    }


    public function wx_read_book()
    {
        $uid = $this->getuid();
        $room_id = input('param.room_id', 0, 'intval');
        $date=input('param.date', 0, 'intval');
        $ym=date('Y-m',time());
        $date=$ym.'-'.$date;
        $date=date("Y-m-d",strtotime($date));
        //$other_apply =Db::name('room_apply')->where(['user_id' => ['neq',$uid],'room_id' => $room_id, 'start_time' => ['egt', time()]])->where("date(from_unixtime(`start_time`,'%Y%m%d'))='$date'")->column('start_time');
        //$self_apply =Db::name('room_apply')->where(['user_id' => $uid,'room_id' => $room_id, 'start_time' => ['egt', time()]])->where("date(from_unixtime(`start_time`,'%Y%m%d'))='$date'")->column('start_time');
        $other_apply =Db::name('room_apply')->where(['user_id' => ['neq',$uid],'room_id' => $room_id, 'start_time' => ['egt', time()]])->column('start_time');
        $self_apply =Db::name('room_apply')->where(['user_id' => $uid,'room_id' => $room_id, 'start_time' => ['egt', time()]])->column('start_time');


        $room_time=Db::name('room')->where([ 'id' => $room_id])->find();
        $data['yiyuyue']=$self_apply;
        $data['other_yiyuyue']=$other_apply;
        $data['shijianduan']=$room_time;
        return $this->output_success('13101', $data, '场馆获取成功');
    }

}