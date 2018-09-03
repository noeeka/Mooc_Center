<?php
/**
 * Created by PhpStorm.
 * User: 18534
 * Date: 2018/8/24
 * Time: 15:50
 */

namespace app\api\controller;



use app\admin\model\BannerModel;

class GetbannerController extends Base
{
//    公共文化共享资源库banner
    public function index($page = 1, $len = 4)
    {
        $banner = BannerModel::where(['type' => 3, 'status' => 1])->field('id, title,img as thumb, description as abstract, publish_time as time, url')->order(['list_order' => 'asc'])->page($page, $len)->select();

        foreach ($banner as &$v) {
            $v['thumb'] = cmf_get_image_preview_url($v['thumb']);
        }

        return $this->output_success('13101',$banner,'活动获取成功');
    }
}