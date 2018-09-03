<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\api\controller;

use app\admin\model\AreaModel;
class AreaController extends Base
{
    /**区域列表
     */
    public function index($param = [])
    {
        $areaModel = new AreaModel();
        //返回的数据必须是数据集或数组,item里必须包括id,name,如果想表示层级关系请加上 parent_id
        $res=$areaModel->select();
        if($res){
            return $this->output_success(11101, $res, '获取区域列表成功');
        }else{
            return $this->output_error(11102, '无区域列表');
        }
    }

    public function volun_index(){
        $area = (new AreaModel)->query('select id,name from cxtj_area where path REGEXP \'^[0-9]+-[0-9]+-[0-9]+$\'');
        return $this->output_success(11101, $area, '获取区域列表成功');
    }
}