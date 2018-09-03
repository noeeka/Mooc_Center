<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/3/14
 * Time: 11:20
 */

namespace app\api\controller;
use app\admin\model\PerformDiandanModel;

class PerformDiandan extends Base
{
    /*
     * 添加点单
     */
    public function add(){
        $performid = input('parma.id',0,'intval');
        $area = input('param.area',0,'intval');
        $userid = cmf_get_current_user_id();

        $performDiandan = new PerformDiandanModel();
        $result = $performDiandan->where(['userid'=>$userid,'performid'=>$performid])->find();

        if($result){
            return $this->output_error('13100','','已过此表演，点单失败');
        }else{
            //点单信息存入数据库
            $data =[
                'perform_id'=>$performid,
                'user_id'=>$userid,
                'area_id'=>$area,
                'create_time'=>time()
            ];
            $diandan = $performDiandan->save($data);
            return $this->output_success('13101','','点单成功');
        }

    }
}