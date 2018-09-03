<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/7
 * Time: 15:44
 */

namespace app\api\controller;

use app\admin\model\NavigationModel;


class NavigationController extends Base
{

    public function index()
    {
        $type=input('type',1,'intval');
        $navigationModel = new NavigationModel();
        $res=$navigationModel->get_nav($type);

        foreach($res as &$v){
            if($v['target']==0){
                $v['target']='_blank';
            }else{
                $v['target']='_self';
            }
            if($v['sub_nav'] != []){
                foreach($v['sub_nav'] as $item){
                    if($item['target']==0){
                        $item['target']='_blank';
                    }else{
                        $item['target']='_self';
                    }
                }
            }
            $v['title'] = $v['name'];
        }
        if($res){
            return $this->output_success(11001, $res, '获取导航列表成功');
        }else{
            return $this->output_error(11002, '无导航');
        }

    }


}