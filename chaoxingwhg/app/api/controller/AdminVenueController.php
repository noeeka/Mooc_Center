<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/2/28
 * Time: 18:16
 */

namespace app\api\controller;


use app\admin\model\VenueModel;
use think\Db;

class AdminVenueController extends Base
{
    public function index($param = [])
    {

        $res = Db::name('venue')->alias('v')->order('list_order ASC')->join('area a','v.address=a.id','left')->field('v.*,a.name as aname')->select();
        if($res){
            return $this->output_success(18101, $res, '获取场馆列表成功');
        }else{
            return $this->output_error(18001, '获取场馆列表失败');
        }
    }
}