<?php
/**
 * Created by PhpStorm.
 * User: zyf
 * Date: 2018/3/5
 * Time: 16:57
 */

namespace app\api\controller;


use app\admin\model\ActivityModel;
use app\admin\model\ActivityBaomingModel;
use app\admin\model\ActivitySignInModel;
use app\admin\model\AreaModel;
use app\admin\model\SfzimgModel;
use app\user\model\UserModel;
use think\Db;
use token\Token;

class ActivityController extends Base
{
    //获取所有活动
    public function index()
    {
        //当前页
        $page = input('page', 1, 'intval');
        //场馆id
        $venue_id = input('venue_id', 0, 'intval');
        //区域id
        $area_id = input('area_id', 0, 'intval');
        //活动类型id
        $activity_type_id = input('activity_type_id', 0, 'intval');
        //排序方式
        $order = input('order', 'published_time');
        //一页显示条数
        $len = input('len', 6, 'intval');

        $where = '';
        if (!empty($venue_id)) {
            $where['a.venue'] = $venue_id;
        }
        if (!empty($area_id)) {
//            $area_path = Db::name('area')->where('id', $area_id)->value('path');
//            $area_ids = Db::name('area')->where(['path' => ['like', "%$area_path%"]])->column('id');
//            $venue_ids = Db::name('venue')->where(['address' => ['in', $area_ids]])->column('id');

            $ids = $areaModel = (new AreaModel())->getTreeIds($area_id);
            array_unshift($ids, $area_id);

            $venue_ids = Db::name('venue')->where(['address' => ['in', $ids]])->column('id');
            if (!empty($venue_id)) {
                $where['a.venue'] = array(['eq', $venue_id], ['in', $venue_ids], 'and');
            } else {
                $where['a.venue'] = ['in', $venue_ids];
            }
        }
        if (!empty($activity_type_id)) {
            $where['at.id'] = $activity_type_id;
        }
        if (!empty($order) && $order == 'page_view') {
            $order = 'a.page_view desc';
        } else {
            $order = 'a.published_time desc';
        }
        $where['a.status'] = 1;
        $where['v.status'] = 1;
        $where['a.volun_type'] = 0;
        $where['a.delete_time'] = 0;
        $where['v.id'] = ['gt',0];
        $activityModel = new ActivityModel();

        //当前页数据
        $activity_list = $activityModel->alias('a')
            ->join('venue v', 'a.venue =v.id', 'left')
            ->join('activity_type at', 'a.type=at.id', 'left')
            ->where($where)
            ->field("a.*,v.name venue_name,from_unixtime(a.start_time,'%Y-%m-%d %h:%i') as format_start_time,from_unixtime(a.end_time,'%Y-%m-%d %h:%i') as format_end_time")
            ->order($order)
            ->page($page, $len)
            ->select();

         //halt($activityModel->getLastSql());
        //活动总数
        $activity_count = $activityModel->alias('a')
            ->join('venue v', 'a.venue =v.id', 'left')
            ->join('activity_type at', 'a.type=at.id', 'left')
            ->where($where)
            ->count();

        if ($activity_list->isEmpty()) {
            return $this->output_success('13101', ['list' => []], '活动获取成功');
        }

        $activityIds = array_column($activity_list->toArray(), 'id');
        $ActivityBaomingModel = new ActivityBaomingModel();
        $baomingCount = $ActivityBaomingModel
            ->where('activity_id', 'in', $activityIds)
            ->where('delete_time' , 0)
            ->group('activity_id')
            ->field('count(*) as col,activity_id')
            ->select();

        if ($baomingCount->isEmpty())
            $baomingCount = [];
        else
            $baomingCount = array_column($baomingCount->toArray(), NULL, 'activity_id');

        foreach ($activity_list as &$item) {
            $item['thumb'] = cmf_get_image_preview_url($item['thumb']);
            if (isset($baomingCount[$item['id']])) {
                $item['apply_people_count'] = $baomingCount[$item['id']]['col'];
            } else {
                $item['apply_people_count'] = 0;
            }

            $item['format_start_time'] = date('Y-m-d H:i:s' , $item['start_time']);
            $item['format_end_time'] = date('Y-m-d H:i:s' , $item['end_time']);
        }

        $result['list'] = $activity_list;
        $result['count'] = $activity_count;
        if ($result) {
            return $this->output_success('13101', $result, '活动获取成功');
        } else {
            return $this->output_error('13100', '', '活动获取失败');
        }
    }

    //获取单个活动
    public function read()
    {

        $id = input('id', 0, 'intval');
        $token = $token = input('post.token');
        $activity = ActivityModel::get($id);

        //场馆名称
        if (!empty($activity['venue'])) {
            $venue_name = Db::name('venue')->where(['status' => 1, 'id' => $activity['venue']])->value('name');
        } else {
            $venue_name = '';
        }
        $activity['venue_name'] = $venue_name;
        //格式化时间
        $activity['format_published_time'] = date('Y-m-d', $activity['published_time']);
        $activity['format_start_time'] = date('Y-m-d H:i', $activity['start_time']);
        $activity['format_end_time'] = date('Y-m-d H:i', $activity['end_time']);
        $activity['format_baoming_start_time'] = date('Y-m-d H:i', $activity['baoming_start_time']);
        $activity['format_baoming_end_time'] = date('Y-m-d H:i', $activity['baoming_end_time']);


        $activityModel = new ActivityModel();
        $activity['content'] = $activityModel->getContentAttr($activity['content']);
        $activity['cnum'] = ActivityBaomingModel::where([ 'activity_id' => $id , 'delete_time' => 0])->count();

        $activity['thumb'] = cmf_get_image_preview_url($activity['thumb']);
        //判断用户是否登录
        $user_id = Token::get_user_id($token);

//        halt($user_id,$id);
        if (!empty($user_id)) {
            $count = db::name('activity_baoming')->where(['activity_id' => $id, 'user_id' => $user_id, 'delete_time' => 0 ])->count();
            if ($count) {
                $activity['yibaoming'] = 1;
            } else {
                $activity['yibaoming'] = 0;
            }

            //判断下用户是否报名过
            $activityBaomingModel = new ActivityBaomingModel();
            $activityInfo = $activityBaomingModel->where([
                'user_id' => $user_id,
                'activity_id' => $id,
                'delete_time' => 0
            ])->select();

            if ($activityInfo->isEmpty()) {
                $activity['baoming'] = 0;
            } else {
                $activity['baoming'] = 1;
            }

        } else {
            $activity['baoming'] = 0;
            $activity['yibaoming'] = 0;
        }

        if ($activity) {
            return $this->output_success('13101', $activity, '活动获取成功');
        } else {
            return $this->output_error('13100', '', '活动获取失败');
        }
    }

    //活动预约
    function enroll()
    {
        $this->check_sign();
        $token = input('post.token');
        $id = input('id');
        $user_id = Token::get_user_id($token);

        if (empty($user_id)) {
            return $this->output_error(13000, '未登录，请先登录且实名认证后方可报名！');
        } else {
            if (empty($id)) {
                return $this->output_error(13001, '没有该活动！');
            } else {
                $user = db('user')->where(['id' => $user_id, 'user_role' => ['in',[1,2]]])->find();
                if (!empty($user)) {
                    //判断是否已报名
                    $count = Db::name('activity_baoming')->where(['activity_id' => $id, 'user_id' => $user_id, 'delete_time' => 0])->count();
                    if (!$count) {
                        //已报名人数
                        $yibaoming_count = Db::name('activity_baoming')->where(['activity_id' => $id])->count();
                        //活动最大人数
                        $max_num = Db::name('activity')->where(['id' => $id])->value('max_num');
                        if ($yibaoming_count >= $max_num) {
                            return $this->output_error(13005, '报名人数已满！');
                        } else {
                            $data['activity_id'] = $id;
                            $data['created_at'] = time();
                            $data['user_id'] = $user_id;

                            $activityBaomingModel = new ActivityBaomingModel();
                            $res = $activityBaomingModel->data($data)->save();
                            if ($res === false) {
                                return $this->output_error(13002, '报名失败！');
                            }else{
                                //更新报名人数
                                Db::name('activity')->where(['id' => $id])->update(['current_num' => $yibaoming_count + 1]);
                                return $this->output_success(13002, '', '报名成功！');
                            }
                        }
                    } else {
                        return $this->output_error(13003, '已报名！');
                    }
                } else {
                    return $this->output_error(13004, '未认证，请先到用户中心认证');
                }
            }
        }
    }


    public function apply()
    {
        $this->check_sign();
        $token = input('token');
        $activity_id = input('activity_id' ,0);
        $contacts_ids = $this->fetchContactsId($_POST);
        $user_id = Token::get_user_id($token);

        if (empty($user_id))
            return $this->output_error(13000, '未登录，请先登录且实名认证后方可报名！');

        if (empty($activity_id))
            return $this->output_error(13001, '没有该活动！');

        if (count($contacts_ids) == 0)
            return $this->output_error(13001, '请选择报名的联系人！');

        $user = db('user')->where([
                'id' => $user_id ,
                'user_role' => ['in',[1,2]]
            ]) ->find();

        if (empty($user))
            return $this->output_error(13004, '未认证，请先到用户中心认证');

        $count = Db::name('activity_baoming')
            ->where([
                'user_id' => $user_id,
                'activity_id' => $activity_id,
                'delete_time' => 0
            ])->count();

        if (count($contacts_ids) > 5 || $count > 5 || intval($count + count($contacts_ids)) > 5)
            return $this->output_error(130011, '一个活动用户只能给5个联系人报名');

        //循环报名
        foreach ($contacts_ids as $contacts_id) {
            $contactInfo = Db::name('contacts')->where([
                'id' => $contacts_id,
                'user_id' => $user_id
            ])->find();

            if (empty($contactInfo))
                return $this->output_error(130010, '联系人不存在');

            $count = Db::name('activity_baoming')
                ->where([
                    'activity_id' => $activity_id,
                    'id_card' => $contactInfo['id_card'],
                    'delete_time' => 0
                ])->find();

            if ($count)
                return $this->output_error(13003, $contactInfo['name'].'已经被报名');

            $postData = [];
            $postData['activity_id'] = $activity_id;
            $postData['user_id'] = $user_id;
            $postData['user_name'] = $user['user_login'];
            $postData['id_card'] = $contactInfo['id_card'];
            $postData['contacts_id'] = $contacts_id;
            $postData['score'] = 0;
            $postData['status'] = 1;
            $postData['created_at'] = time();
            $postData['sign_code'] =  substr(strval(rand(10000,19999)),0,5);
            $applyResult = Db::name('activity_baoming')->insert($postData);

            if (!$applyResult)
                return $this->output_error(130012, $contactInfo['name'].'报名失败');
        }

        return $this->output_success(200, '报名成功');
    }

    private function fetchContactsId($POST)
    {
        $contactsId = isset($POST['contacts_id'])
        && is_array($POST['contacts_id'])
        ? $POST['contacts_id'] : [] ;

        foreach ($contactsId as $key => $item) {
             if (!is_numeric($item)) {
                 unset($contactsId[$key]);
             }
        }

        return $contactsId;
    }

    public function myact()
    {
        //验证登录
        $this->check_sign();
        $token = input('param.token');
        $uid = Token::get_user_id($token);
        $status = input('param.status', 0, 'intval');
        $page = input('param.page', 1, 'intval');
        $len = input('param.len', 10, 'intval');
        $where = [];
        $where['a.delete_time'] = ['=', 0];
        $where['v.status'] = 1;
        $ids = [];
        if ($status == 3) {
            //1 已完成 2 已预约
            $where['b.delete_time'] = ['>' , 0];
        }

        if ($status == 2) { //已完成
            $where['a.start_time'] = ['egt', time()];
            $where['a.end_time'] = ['egt', time()];
            $where['b.delete_time'] = ['=' , 0];
        }
        if ($status == 1) { //待使用
            $where['a.start_time'] = ['elt', time()];
            $where['a.end_time'] = ['egt', time()];
            $where['b.delete_time'] = ['=' , 0];
        }

        $baoming = new ActivityBaomingModel();

        $list = $baoming
            ->alias('b')
            ->where(['b.user_id' => $uid])
            ->join('activity a', 'a.id = b.activity_id')
            ->join('venue v','v.id = a.venue')
            ->where($where)
            ->field('b.id as baoming_id,a.id,a.thumb,a.start_time,a.end_time,a.need_sign,b.sign_code,v.name,b.delete_time, b.created_at as baoming_time')
            ->order("baoming_time","desc")
            ->page($page, $len)
            ->select();

        $count = $baoming
            ->alias('b')
            ->where(['b.user_id' => $uid])
            ->join('activity a', 'a.id = b.activity_id')
            ->join('venue v','v.id = a.venue')
            ->where($where)
            ->count(1);
        $now = time();
        foreach ($list as &$v) {
            //   $v['status'] = $now > $v['end_time'] ? 1 : 2;
            $statusMsg = ['未知状态','待使用' , '已完成' ,'已取消'];
            $v['formate_start_time'] = date('n.d H:i', $v['start_time']);
            $v['formate_end_time'] = date('n.d H:i', $v['end_time']);
            $v['formate_baoming_time'] = date('n月d号 H:i', $v['end_time']);
            $v['status'] = $v['delete_time'] == 0 ?  $this->getActivityStatus($v['start_time'] , $v['end_time']) : 3;
            $v['status_msg'] = $statusMsg[$v['status']];
            $v['thumb'] = cmf_get_image_preview_url($v['thumb']);
        }

        return $this->output_success(13120, ['list' => $list, 'count' => $count], '获取场馆信息成功');
    }

    public function cancel()
    {
//        验证登录
        $this->check_sign();
        $token = input('param.token');
        $uid = Token::get_user_id($token);
        $activity_id = input('param.id', 0, 'intval');
        $res = ActivityBaomingModel::where(['user_id' => $uid, 'id' => $activity_id, 'delete_time' => 0])->update(['delete_time' => time()]);
        if ($res) {
            ActivityModel::where("id", $activity_id)->setDec("current_num", 1);
            return $this->output_success(13120, [], '取消报名成功');
        } else {
            return $this->output_error(13020, '取消报名失败');
        }
    }

    /**
     * 活动签到
     *
     * @param string $mobile 签到手机号
     * @param int $activityId 活动信息
     *
     * @return json
     */
    public function signIn() {
        $activityId = intval(input('activityId'));
        $activityInfo = ActivityModel::where("id", $activityId)->find();

        if(empty($activityInfo) || empty($activityId)) //活动查询失败
            return $this->output_error(10005, '签到失败');

        $activityInfo = $activityInfo->toArray();
        if($activityInfo['need_sign'] == 0) //活动不需要签到
            return $this->output_error(10006, '活动不需要签到');

        if($activityInfo['sign_end_time'] <= time() )
            return $this->output_error(10012, '签到结束');

        if($activityInfo['sign_start_time'] >= time() )
            return $this->output_error(10013, '签到未开始');

        switch ($activityInfo['sign_type']) {
            case 1:  //1报名用户
                $id_card = input('id_card');
                if (strlen($id_card) <= 0)
                    return $this->output_error(10007, '身份证或签到码格式错误');

                if (strlen($id_card) == 18 && !isShenfenzheng($id_card))
                    return $this->output_error(10007, '身份证或签到码格式错误');

                $where = array();
                $where['activity_id'] = $activityId;
                $where['delete_time'] = 0;
                if (strlen($id_card) == 18)
                    $where['id_card'] = $id_card;
                if (strlen($id_card) == 5)
                    $where['sign_code'] = $id_card;

                $baoming = ActivityBaomingModel::where($where)->find();
                if(empty($baoming)) //用户不是注册用户 失败
                    return $this->output_error(10007, '不是报名用户不能签到');

                if (ActivitySignInModel::where([
                        'user_id' => $baoming['user_id'] ,
                        'activity_id' => $activityId ,
                        'id_card' => $baoming['id_card']
                    ])->find())
                    return $this->output_error(10009, '重复签到');

                $userinfo = Db::name('user')->where(['id' => $baoming['user_id']])->find();
                if (empty($userinfo))
                    return $this->output_error(10007, '报名用户不存在');

                $contacts = Db::name('contacts')->where([
                        'id_card' => $baoming['id_card'] ,
                        'user_id' => $baoming['user_id']
                    ])->find();

                if (empty($contacts))
                    return $this->output_error(10007, '联系人不存在');

                //身份证信息
                $signInfo = array();
                $signInfo['user_id'] = $baoming['user_id'];
                $signInfo['activity_id'] = $activityId;
                $signInfo['user_name'] = $userinfo['user_login'];
                $signInfo['create_time'] = time();
                $signInfo['delete_time'] = 0;
                $signInfo['type'] = 1;
                $signInfo['id_card'] = $baoming['id_card'];
                $signInfo['contacts_name'] = $contacts['name'];
                $signInfo['contacts_mobile'] = $contacts['mobile'];
                $signInfo['contacts_type'] = $contacts['type'];
                $signInfo['contacts_guardian'] = $contacts['guardian'];

                $inserReuslt = ActivitySignInModel::insert($signInfo);
                if ($inserReuslt)
                    return $this->output_success(200, [], '签到成功');
                else
                    return $this->output_error(100010, '签到失败');
                break;
            case 3: //3全网用户
                $id_card = input('id_card' ,0);
                $name = input('name' ,0);
                $mobile = intval(input('mobile' ,0));

                if (!isShenfenzheng($id_card))
                    return $this->output_error(10015, '身份证格式不正确');
                if (empty($name) || mb_strlen($name) < 0 || mb_strlen($name) > 4)
                    return $this->output_error(10015, '联系人姓名不正确');

                if (strlen($mobile) != 11)
                    return $this->output_error(10015, '手机号不正确');

                if (ActivitySignInModel::where([
                    'activity_id' => $activityId ,
                    'id_card' => $id_card
                ])->find())
                    return $this->output_error(10009, '重复签到');

                $signInfo = array();
                $signInfo['user_id'] = '';
                $signInfo['activity_id'] = $activityId;
                $signInfo['user_name'] = '';   //报名的姓名
                $signInfo['create_time'] = time();
                $signInfo['delete_time'] = 0;
                $signInfo['type'] = 1;
                $signInfo['id_card'] = $id_card;
                $signInfo['contacts_name'] = $name;
                $signInfo['contacts_mobile'] = $mobile;

                $inserReuslt = ActivitySignInModel::insert($signInfo);
                if ($inserReuslt)
                    return $this->output_success(200, [], '签到成功');
                else
                    return $this->output_error(10015, '签到失败');

                break;
            default:
                return $this->output_error(10016, '签到失败');
                break;
        }
    }

    public function getSignQrcode() {

        $activityId = intval(input('activityId'));
        $activityInfo = ActivityModel::where("id", $activityId)->field('need_sign,sign_qrcode,title')->find();

        if (empty($activityInfo))
            return $this->output_error(404, '活动不存在');

        if( !empty($activityInfo['sign_qrcode']) )
            $activityInfo['sign_qrcode'] = cmf_get_image_preview_url($activityInfo['sign_qrcode']);

        return $this->output_success(200, $activityInfo , '查询成功');

    }

    /**
     * 返回活动状态
     *
     * @param string $method
     * @param array  $args
     *
     * @return [0,1,2,3]
     */
    public function getActivityStatus($startTime , $endTime)
    {
        $now = time();
        if ($startTime >= $now  && $endTime >= $now) {
            return 1;
        } elseif ($now >= $startTime  && $now <= $endTime) {
            return 1;
        } elseif ($now >= $startTime && $now >= $endTime) {
            return 2;
        }
        return 0;
    }

}