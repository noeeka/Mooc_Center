<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/1
 * Time: 14:40
 */

namespace app\api\controller;


use app\admin\model\BannerModel;

class BannerController extends Base
{
    public function index($param = [])
    {
        $bannerModel = new BannerModel();

        $res = $bannerModel->where(['status' => 1, 'type' => 1])->select();
        //图片处理
        foreach ($res as &$v) {
            $v['img'] = cmf_get_image_preview_url($v['img']);
            $v['wx_img'] = $v['wx_img'] == '' ? $v['img'] : cmf_get_image_preview_url($v['wx_img']);
        }
        if ($res) {
            return $this->output_success(11101, $res, '获取banner列表成功');
        } else {
            return $this->output_error(11102, '无banner列表');
        }
    }
}