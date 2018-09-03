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

use app\admin\model\LinkModel;
class LinkController extends Base
{
    /**ink
     * 链接列表
     * @param array $param
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function index($param = [])
    {
        $linkModel = new LinkModel();

        //返回的数据必须是数据集或数组,item里必须包括id,name,如果想表示层级关系请加上 parent_id
        $res=$linkModel->where(['status'=>1])->order('list_order asc')->select();
        if($res){
            return $this->output_success(11001, $res, '获取视频列表成功');
        }else{
            return $this->output_error(11002, '无视频');
        }
    }
}