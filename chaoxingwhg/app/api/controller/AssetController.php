<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/5
 * Time: 16:57
 */

namespace app\api\controller;

use app\admin\model\AssetModel;
use think\Exception;

class AssetController extends Base
{
    public $assetService;
    public function __construct()
    {
        parent::__construct();
        $this->assetService = new AssetModel();
    }

    public function getAssetList()
    {
        $where = [];
        $where['status'] = 1;

        $page = input('page', 1);
        $limit = input('limit', 10);
        $keyword = input('keyword', '');
        $type = input('AssetType', 0);

        !empty($keyword) && $where['filename'] = ['like' , '%'.$keyword.'%' ] ;
        if(!empty($type))
            $where['type'] =  $type;

        $assets = $this->assetService
            ->where($where)
            ->order('create_time' , 'desc')
            ->page($page, $limit)->select();

        $count = $this->assetService
            ->where($where)->count();

        if($count > $limit) {
            $pagination = $this->pagination($page , $count , $limit);
        } else
            $pagination = "";

        return $this->output_success('13101', ['list' => $assets  , 'num' => $count ,'pagination' => $pagination  ], '文章获取成功');
    }


    public function setAssetType()
    {

        $type = input('assetType' , '0');
        $files = $_POST['files'];

        if($type == 1 || empty($files))
            return $this->output_success('13101', [ 'msg' => '更新成功' ], '更新成功');

        $names = array_column($files , 'name');

        $this->assetService
            ->whereIn('filename' , $names)
            ->update(['type' => $type]);

        return $this->output_success('13101', [ 'msg' => '更新成功' ], '更新成功');
    }

}