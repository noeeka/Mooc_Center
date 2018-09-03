<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/5/29
 * Time: 13:50
 */

namespace app\api\controller;

use app\admin\model\HomePageLogModel;

class HomepagelogController extends Base
{
    /**
     * 设置为默认值
     * @param $key tablename;field;id
     * @param $value 值
     * @return array|bool|string
     */
    public function setDefaultLog(){
        $key = input('key','');
        $value = input('value','');

        $LogModel = new HomePageLogModel();
        $result = $LogModel->setDefault($key,$value);

        if($result){
            return $this->output_success(11211,[],'设置默认值成功');
        }else{
            return $this->output_error(11011,'设置默认值失败');
        }
    }

    /**
     * 获取默认日志
     * @param $key tablename;field;id
     */
    public function getDefault(){
        $key = input('key','');

        $LogModel = new HomePageLogModel();
        $resultObj = $LogModel->getDefaultLog($key);

        if($resultObj != null){
            $value = htmlspecialchars_decode($resultObj->value);
            return $this->output_success(11111,['value'=>$value],'获取默认值成功');
        }else{
            return $this->output_error(11011,'获取默认值失败');
        }
    }
}