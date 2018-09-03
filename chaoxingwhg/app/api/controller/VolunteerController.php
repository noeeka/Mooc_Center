<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/3/13
 * Time: 14:37
 */

namespace app\api\controller;

use app\admin\model\ActivityModel;
use app\admin\model\SfzimgModel;
use think\Db;
use app\admin\model\PortalCategoryModel;
use app\admin\model\PortalPostModel;
use app\admin\model\BannerModel;
use app\admin\model\UserModel;
use token\Token;


class VolunteerController extends Base
{
    private $reportCategory = 26;

    //获取Volunbanner
    public function getBanner()
    {
        $bannerModel = new BannerModel();

        $res = $bannerModel->where(['status' => 1, 'type' => 2])->select()->toArray();


        if ($res) {
            //图片处理
            foreach ($res as &$v) {
                $v['img'] = cmf_get_image_preview_url($v['img']);
            }
            return $this->output_success(11101, $res, '获取banner列表成功');
        } else {
            return $this->output_error(11102, '无banner列表');
        }
    }

    //获取活动报道
    public function getReport()
    {
        $postModel = new PortalPostModel();
        $where = [];
        $where['b.category_id'] = $this->reportCategory;
        $where['delete_time'] = 0;
        $where['post_status'] = 1;
        $where['v.status'] = 1;
        $postList = $postModel
            ->alias('a')
            ->join('__PORTAL_CATEGORY_POST__ b', 'b.post_id = a.id ')
            ->join('venue v', 'v.id = a.venue')
            ->where($where)
            ->field('a.*,v.name')->order('a.is_top desc,a.id desc')->select();
        if ($postList) {
            return $this->output_success(11101, $postList, '获取活动报道成功');
        } else {
            return $this->output_error(11102, '获取活动列表失败');
        }
    }

    //志愿者活跃度
    public function VolunScore()
    {
        $scoreList = Db::name('user')->alias('u')->where(['user_role' => 2])->join('sfzimg s', 's.user_id=u.id')->field('u.id,u.score,u.avatar,s.realname')->order("score DESC")->select()->toArray();

        if (empty($scoreList)) {
            //图片处理
            return $this->output_error(14011, '志愿者活跃度获取失败');
        } else {
            foreach ($scoreList as $k => $v) {
                $scoreList[$k]['avatar'] = $v['avatar'] == '' ? '' : cmf_get_image_preview_url($v['avatar']);
            }

            return $this->output_success(14111, $scoreList, '活跃度获取成功');
        }
    }

    //活动招募
    public function getrecurit()
    {
        $where = [];
        $where['a.volun_type'] = ['neq', 0];
        $where['a.type'] = 0;
        $where['a.delete_time']=0;
        $where['a.status'] = 1;
        $where['v.status'] = 1;
        $recuritModel = new ActivityModel();
        $recuritList = $recuritModel->alias('a')->join('venue v', 'v.id = a.venue')->join('volun_type vt', 'vt.id =a.volun_type')->where($where)->field('v.name,a.*,vt.name as type_name')->order('a.is_top desc,a.baoming_end_time desc ,a.id desc')->select();

        if ($recuritList) {
            foreach ($recuritList as $key => $value) {
                $recuritList[$key]['thumb'] = cmf_get_image_preview_url($value['thumb']);;
            }
            return $this->output_success(11101, $recuritList, '招募获取成功');
        } else {
            return $this->output_error(11102, '获取招募失败');
        }
    }

    //风采展示
    public function getMien()
    {

        $where = ['user_role' => 2,'volun_skill_imgs'=>['neq',''],'is_show'=>1];//志愿者且已展示且风采图片不为空
        $order = ['list_order'=>'asc'];
        $userModel = new UserModel();
        $user_list = $userModel->where($where)->order($order)->select();

        foreach ($user_list as $key => $value) {
            //获取真实姓名
            $user_realname = SfzimgModel::where('user_id', $value['id'])->value('realname');
            $value['user_realname'] = $user_realname;

            //获取风采图片
            $user_list[$key]['imgs'] = json_decode($value['volun_skill_imgs'], true);
            if (is_array($user_list[$key]['imgs'])) {
                $imgs = $user_list[$key]['imgs'];
                $imgs1 = [];
                foreach ($imgs as $k => $v) {
                    $imgs1[$k] = cmf_get_image_preview_url($v);
                }
                $user_list[$key]['imgs'] = $imgs1;
            }
        }

        if ($user_list) {
            return $this->output_success(11101, $user_list, '获取风采展示成功');
        } else {
            return $this->output_error(11102, '获取风采展示失败');
        }
    }

    //单个风采展示
    public function get_single_mien()
    {
        $id = input('id', 0, 'intval');
        $where = ['user_role' => 2, 'volun_skill_imgs'=>['neq',''],'id' => $id];//单个志愿者且已审核

        $userModel = new UserModel();
        $user_list = $userModel->where($where)->find();
        if(empty($user_list)){ //用户信息未找到
            return $this->output_error(11201,'获取风采失败');
        }

        //获取真实姓名
        unset($user_list['user_realname']);
        $user_realname = SfzimgModel::where('user_id', $id)->value('realname');
        $user_list['user_realname'] = $user_realname;

        if($user_list['volun_skill_imgs'] != ''){
            $imgs = json_decode($user_list['volun_skill_imgs']);
        }else{
            $imgs = "";
        }

        //user图片处理
        $user = [];
        if (is_array($imgs)) {
            foreach ($imgs as $k => $v) {
                $user[] = cmf_get_image_preview_url($v);
            }
            $user_list['img'] = $user;
        }

        if ($user_list) {
            $user_list['speciality_html'] = parseTextArea($user_list['speciality']);
            return $this->output_success(11101, $user_list, '获取风采展示详情成功');
        } else {
            return $this->output_error(11102, '获取风采展示失败');
        }
    }

    //志愿者总数
    public function volun_count()
    {
        //志愿者总数
        $userModel = new UserModel();
        $volun_num = $userModel->where(['user_role' => 2])->count(1);
        return $this->output_success(13012, ['num' => $volun_num], '审核中');
    }

    //注册成为志愿者
    public function auth_read()
    {
        $user_id = $this->getuid(true);

        $userModel = new UserModel();

        if (empty($user_id)) {
            //未登录
            return $this->output_error(12011, '请先登录');
        }

        //是否为志愿者
        $volun_status = $userModel->where(['id' => $user_id])->value('volun_status');
        if ($volun_status == 3) {
            //是志愿者
            return $this->output_error(13011, '已成为志愿者');
        } elseif ($volun_status == 1) {
            //审核中
            return $this->output_error(13012, '已注册，审核中');
        } else {
            return $this->output_success(13111, [], '');
        }

    }

    //是否为志愿者
    function is_volun()
    {
        //登录检查
        $this->check_sign();
        $token = input('param.token');
        $user_id = Token::get_user_id($token);

        $userModel = new UserModel();
        $is_volun = $userModel->where('id', $user_id)->value('user_role');

        if ($is_volun == 2) {
            //是志愿者
            return $this->output_success(12011, ['is_volun' => 1], '是志愿者');
        } else {
            return $this->output_error(12111, '不是志愿者');
        }


    }


}