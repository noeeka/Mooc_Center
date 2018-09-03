<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/2/28
 * Time: 15:00
 */

namespace app\api\controller;


use app\admin\model\BaseinfoModel;
use app\api\controller\Base;

class BaseinfoController extends Base
{

    public function read($param = [])
    {
        $BaseinfoModel = new BaseinfoModel();


        $res =$BaseinfoModel->find();

        if($res!=null){
            $ewm = json_decode($res['ewm'], true);
            foreach ($ewm as $k => $v){
                $ewm[$k]['img'] = $v['img'] == '' ? '' : cmf_get_image_preview_url($v['img']);
            }
            $res['ewm'] = $ewm;
            $res['home_page_logo'] = cmf_get_image_preview_url($res['home_page_logo']);
            $res['second_page_logo'] = cmf_get_image_preview_url($res['second_page_logo']);
            return $this->output_success(18101, $res, '获取视频列表成功');
        }else{
            return $this->output_error(18001, '无视频');
        }
    }

    public function uploaderror(){
        $data = input('param.data');
        trace('jserror:----'.json_encode($data), 'debug');
    }
}