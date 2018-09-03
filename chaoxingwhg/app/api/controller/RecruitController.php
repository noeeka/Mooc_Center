<?php
/**
 * Created by PhpStorm.
 * User: zyf
 * Date: 2018/3/5
 * Time: 16:57
 * Remark:志愿者活动招募接口
 */

namespace app\api\controller;


use app\admin\model\ActivityModel;
use app\admin\model\ActivityBaomingModel;
use app\admin\model\ActivitySignInModel;
use app\admin\model\SfzimgModel;
use app\user\model\UserModel;
use think\Db;
use token\Token;

class RecruitController extends Base
{
    //获取所有活动招募
    public function index()
    {
        //当前页
        $page = input('page', 1, 'intval');
        //场馆id
        $venue_id = input('venue_id', 0, 'intval');
        //区域id
        $area_id = input('area_id', 0, 'intval');
        //活动招募类型id
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
            $area_path = Db::name('area')->where('id', $area_id)->value('path');
            $area_ids = Db::name('area')->where(['path' => ['like', "%$area_path%"]])->column('id');
            $venue_ids = Db::name('venue')->where(['address' => ['in', $area_ids]])->column('id');

            if (!empty($venue_id)) {
                $where['a.venue'] = array(['eq', $venue_id], ['in', $venue_ids], 'and');
            } else {
                $where['a.venue'] = ['in', $venue_ids];
            }
        }
        if (!empty($activity_type_id)) {
            $where['pt.id'] = $activity_type_id;
        }
        if (!empty($order) && $order == 'page_view') {
            $order = 'a.page_view desc';
        } else {
            $order = 'a.published_time desc';
        }
        $where['a.status'] = 1;
        $where['a.delete_time'] = 0;
        $where['a.type'] = 0;

        $activityModel = new ActivityModel();

        //当前页数据
        $activity_list = $activityModel->alias('a')
            ->join('venue v', 'a.venue =v.id', 'left')
            ->join('perform_type pt', 'a.volun_type=pt.id', 'left')
            ->where($where)
            ->field("a.*,v.name venue_name,from_unixtime(a.start_time,'%Y-%m-%d %h:%i') as format_start_time,from_unixtime(a.end_time,'%Y-%m-%d %h:%i') as format_end_time")
            ->order($order)
            ->page($page, $len)
            ->select();

        //活动总数
        $activity_count = $activityModel->alias('a')
            ->join('venue v', 'a.venue =v.id', 'left')
            ->join('perform_type pt', 'a.volun_type=pt.id', 'left')
            ->where($where)
            ->count();

        if ($activity_list->isEmpty()) {
            return $this->output_success('13101', ['list' => []], '活动获取成功');
        }

        $activityIds = array_column($activity_list->toArray(), 'id');
        $ActivityBaomingModel = new ActivityBaomingModel();
        $baomingCount = $ActivityBaomingModel
            ->where('activity_id', 'in', $activityIds)
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
        }

        $result['list'] = $activity_list;
        $result['count'] = $activity_count;
        if ($result) {
            return $this->output_success('13101', $result, '活动获取成功');
        } else {
            return $this->output_error('13100', '', '活动获取失败');
        }
    }

    //获取单个活动招募
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
        $activity['cnum'] = ActivityBaomingModel::where(['activity_id' => $id])->count();
        $activity['thumb'] = cmf_get_image_preview_url($activity['thumb']);
        //判断用户是否登录
        $user_id = Token::get_user_id($token);

        if (!empty($user_id)) {
            $count = db::name('activity_baoming')->where(['activity_id' => $id, 'user_id' => $user_id])->count();
            if ($count) {
                $activity['yibaoming'] = 1;
            } else {
                $activity['yibaoming'] = 0;
            }

            //判断下用户是否报名过
            $activityBaomingModel = new ActivityBaomingModel();
            $activityInfo = $activityBaomingModel->where([
                'user_id' => $user_id,
                'activity_id' => $id
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

        $activity['cnum'] = ActivityBaomingModel::where(['activity_id' => $id])->count();

        if ($activity) {
            return $this->output_success('13101', $activity, '活动获取成功');
        } else {
            return $this->output_error('13100', '', '活动获取失败');
        }
    }

    //活动招募报名
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
                $user = db('user')->where(['id' => $user_id, 'user_role' => 2])->find();
                if (!empty($user)) {
                    //判断是否已报名
                    $count = Db::name('activity_baoming')->where(['activity_id' => $id, 'user_id' => $user_id])->count();
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
                    return $this->output_error(13004, '不是志愿者，请先注册为志愿者');
                }
            }
        }
    }

    public function myact()
    {
        //验证登录
        $this->check_sign();
        $token = input('param.token');
        $uid = Token::get_user_id($token);
        $status = input('param.status', 0, 'intval');
        $start_time = input('param.start_time', 0, 'intval');
        $end_time = input('param.end_time', 0, 'intval');
        $page = input('param.page', 1, 'intval');
        $len = input('param.len', 10, 'intval');
        $where = [];
        $ids = [];
        if ($status != 0) {
            //1 已完成 2 已预约
            $where['a.end_time'] = $status == 1 ? ['elt', time()] : ['egt', time()];
        }
        if ($start_time != 0) {
            $where['a.start_time'] = ['egt', $start_time];
        }
        if ($end_time != 0) {
            $where['a.end_time'] = ['elt', $end_time];
        }
        $baoming = new ActivityBaomingModel();


        $list = $baoming
            ->alias('b')
            ->where(['b.user_id' => $uid])
            ->join('activity a', 'a.id = b.activity_id')
            ->where($where)
            ->field('a.*, b.created_at as baoming_time,from_unixtime(start_time,\'%Y-%m-%d\') as formate_start_time, from_unixtime(end_time,\'%Y-%m-%d\') as formate_end_time')
            ->order("baoming_time","desc")
            ->page($page, $len)
            ->select();

        $count = $baoming
            ->alias('b')
            ->where(['b.user_id' => $uid])
            ->join('activity a', 'a.id = b.activity_id')
            ->where($where)
            ->count(1);
        $now = time();
        foreach ($list as &$v) {
            //   $v['status'] = $now > $v['end_time'] ? 1 : 2;
            $v['formate_start_time'] = date('n.d H:i', $v['start_time']);
            $v['formate_end_time'] = date('n.d H:i', $v['end_time']);
            $v['formate_baoming_time'] = date('n月d号 H:i', $v['end_time']);
            $v['status'] = $now > $v['end_time'] ? 1 : 2;
            $v['status_msg'] = $now > $v['end_time'] ? '已完成' : '已报名';

        }

        return $this->output_success(13120, ['list' => $list, 'count' => $count], '获取场馆信息成功');
    }

    public function cancel()
    {
//        验证登录
        $this->check_sign();
        $token = input('param.token');
//        echo $token;die;
        $uid = Token::get_user_id($token);
        $activity_id = input('param.id', 0, 'intval');
        $res = ActivityBaomingModel::where(['user_id' => $uid, 'activity_id' => $activity_id])->delete();
        if ($res) {
            ActivityModel::where("id", $activity_id)->setDec("current_num", 1);
            return $this->output_success(13120, [], '取消报名成功');
        } else {
            return $this->output_error(13020, '取消报名失败');
        }
    }

    public function signIn() {

        $mobile = trim( input('mobile') );
        $activityId = intval(input('activityId'));

        $activityInfo = ActivityModel::where("id", $activityId)->find();

        if(empty($activityInfo) || empty($activityId)) //活动查询失败
            return $this->output_error(10005, '签到失败');

        $activityInfo = $activityInfo->toArray();
        if($activityInfo['need_sign'] == 0) //活动不需要签到
            return $this->output_error(10006, '签到失败');


        if($activityInfo['sign_end_time'] <= time() )
            return $this->output_error(10012, '签到结束');

        if($activityInfo['sign_start_time'] >= time() )
            return $this->output_error(10013, '签到未开始');

        //查询用户信息
        $where = [
            'mobile' => $mobile,
            'user_status' => 1,
            'user_type' => 2
        ];
        $userInfo = UserModel::where($where)->find();
        $userInfo = isset($userInfo) ?$userInfo->toArray() : [];

        switch ($activityInfo['sign_type']) {
            case 1:  //1报名用户
                if(empty($userInfo)) //用户不是注册用户 失败
                    return $this->output_error(10007, '签到失败');

                $baoming = ActivityBaomingModel::where(['user_id' => $userInfo['id'] , "activity_id" => $activityId])->find();
                if(empty($baoming)) //用户没报名失败
                    return $this->output_error(10008, '签到失败');

                if (ActivitySignInModel::where(['user_id' => $userInfo['id'] , 'activity_id' => $activityId ])->find())
                    return $this->output_error(10009, '重复签到');

                //身份证信息
                $sfzInfo = SfzimgModel::where('user_id', $userInfo['id'])->find();
                $inserReuslt = ActivitySignInModel::insertSignIn($userInfo  , $sfzInfo ,$activityId, $mobile , $activityInfo['sign_type']);
                if ($inserReuslt)
                    return $this->output_success(200, [], '签到成功');
                else
                    return $this->output_error(100010, '签到失败');

                break;
            case 2: //2系统用户
                if(empty($userInfo)) //用户不是注册用户 失败
                    return $this->output_error(10011, '签到失败');

                if (ActivitySignInModel::where(['user_id' => $userInfo['id'] , 'activity_id' => $activityId ])->find())
                    return $this->output_error(10009, '重复签到');

                $sfzInfo = SfzimgModel::where('user_id', $userInfo['id'])->find();
                $inserReuslt = ActivitySignInModel::insertSignIn($userInfo  , $sfzInfo ,$activityId, $mobile , $activityInfo['sign_type']);
                if ($inserReuslt)
                    return $this->output_success(200, [], '签到成功');
                else
                    return $this->output_error(10013, '签到失败');

                break;
            case 3: //3全网用户

                if (ActivitySignInModel::where(['sign_mobile' => $mobile  , 'activity_id' => $activityId ])->find())
                    return $this->output_error(10009, '重复签到');

                $sfzInfo = SfzimgModel::where('user_id', isset($userInfo['id']) ? $userInfo['id']:0 )->find();
                $inserReuslt = ActivitySignInModel::insertSignIn($userInfo  , $sfzInfo ,$activityId, $mobile , $activityInfo['sign_type']);
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

}