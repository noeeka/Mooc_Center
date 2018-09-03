<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/13
 * Time: 14:37
 */

namespace app\api\controller;

use app\admin\model\ActivityModel;
use app\admin\model\MienModel;
use think\Db;
use app\admin\model\PortalCategoryModel;
use app\admin\model\PortalPostModel;
use app\admin\model\BannerModel;



class VolunBannerController extends Base
{
    private $reportCategory=26;

    //获取Volunbanner
    public function getBanner()
    {
        $bannerModel = new BannerModel();

        $res=$bannerModel->where(['status'=>1,'type'=>2])->select();
        //图片处理
        foreach ($res as &$v){
            $v['img'] = cmf_get_image_preview_url($v['img']);
        }
        if($res){
            return $this->output_success(11101, $res, '获取banner列表成功');
        }else{
            return $this->output_error(11102, '无banner列表');
        }
    }

    //获取活动报道
    public function getReport(){
        $postModel = new PortalPostModel();
        $postList = $postModel->alias('a')->jion('__PORTAL_CATEGORY_POST__ b','b.post_id = a.id ')->where('b.categoryid',$this->reportCategory)->select()->toArray();
        if($postList){
            return $this->output->success(11101,$postList,'获取活动报道成功');
        }else{
            return $this->output->error(11102,'获取活动列表失败');
        }
    }

    //活动招募
    public function getrecurit(){
        $where = [];
        $where['volun_type'] = ['neq',0];
        $recuritModel = new ActivityModel();
        $recuritList = $recuritModel->alias('a')->jion('venue v','v.id = a.venue')->where($where)->select->toArray();
        if($recuritList){
            return $this->output->success(11101,$recuritList,'招募获取成功');
        }else{
            return $this->output->error(11102,'获取招募失败');
        }
    }

    //风采展示
    public function getMien(){
        $where = ['delete_time'=>0,'status'=>1];
        $mienModel = new MienModel();
        $mienList = $mienModel->where($where)->select();
        if($mienList){
            return $this->output->success(11101,$mienList,'获取活动风采成功');
        }else{
            return $this->output->error(11102,'获取活动风采成功');
        }
    }

    //注册成为志愿者
    public function auth_read()
    {
        $this->check_sign();
        $token = input('param.token');
        if(empty($token)){
            //未登录
            return $this->output->error(12011,'请先登录');
        }else{
            $user_id = Token::get_user_id($token);
        }
        $data = db('sfzimg')->where('user_id', $user_id)->find();
        if (empty($data)) {
            return $this->output_error(13011, '未认证用户');
        } else {
            if($data['status'] == 0){
                //身份未验证
                return $this->output->error(13012,'身份未认证');
            }else if($data['status']== 1){
                //身份验证未通过
                return $this->output->error(13013,'身份认证未通过');
            }else{
                //身份验证成功
                return $this->output->success(13111,$data,'身份获取成功');
            }
//            $data['img'] = json_decode($data['img'], true);
//            foreach ($data['img'] as $value) {
//                $data['server_img'][] = cmf_get_image_preview_url($value);
//            }
//            return $this->output_success(13104, $data, '身份信息获取成功');
        }
    }

    //志愿者活跃度
    public function VolunScore(){
        $scoreList = Db::name('user')->where(['user_role' => 2])->order("score DESC")->select();
        if(empty($scoreList)){
            $this->output->error(14011,'志愿者活跃度获取失败');
        }else{
            $this->output->success(14111,$scoreList,'活跃度获取成功');
        }
    }
}