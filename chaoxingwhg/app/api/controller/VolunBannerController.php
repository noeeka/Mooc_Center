<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/3/13
 * Time: 14:37
 */

namespace app\api\controller;

use app\admin\model\BannerModel;

class VolunBannerController extends Base
{
    public function index($param = [])
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
}