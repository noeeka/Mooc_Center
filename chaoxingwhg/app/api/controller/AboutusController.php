<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/2/28
 * Time: 15:00
 */

namespace app\api\controller;

use app\admin\model\AboutusModel;
use think\Db;

class AboutusController extends Base
{

    public function read()
    {

        $aboutusModel = new AboutusModel();
        $aboutus =$aboutusModel->find();
        if($aboutus!=null){
            $aboutus['thumb'] = cmf_get_image_preview_url($aboutus['thumb']);
            $aboutus['abstract'] = $aboutusModel->getPostContentAttr($aboutus['content']);

            return $this->output_success(18101, $aboutus, '获取关于我们成功');
        }else{
            return $this->output_error(18001, '获取关于我们失败');
        }
    }

}