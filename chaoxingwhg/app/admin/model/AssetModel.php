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
namespace app\admin\model;

use app\admin\model\RouteModel;
use think\Model;
use tree\Tree;

class AssetModel extends Model
{

    public function AssetList($filter, $haveChild = false)
    {
        $where = [];
        $where['status'] = 1;
        isset($filter['keyword']) && !empty($filter['keyword']) && $where['filename'] = ['like' , '%'.$filter['keyword'].'%' ] ;
        isset($filter['suffix']) && !empty($filter['suffix']) && $where['suffix'] = $filter['suffix'];
        isset($filter['assetType']) && !empty($filter['assetType']) && $where['type'] = $filter['assetType'];
        isset($filter['start_time']) && isset($filter['end_time'])
        && !empty($filter['end_time']) && !empty($filter['start_time'])
        && $where['create_time'] = [
              ['>=',strtotime($filter['start_time'])],
              [ '<=',strtotime($filter['end_time'])]
        ];

        $assets = $this
            ->where($where)
            ->order('create_time' , 'desc')
            ->paginate(15);


        return $assets;
    }

    public function updateAsset($file_store_key,$file_url_path , $file_key )
    {
        $modifyFiles = [];
        $modifyFiles['file_store_key'] = $file_store_key;//$fileKey;
        $modifyFiles['file_url_path'] = $file_url_path;// $weed->weedVolume;
        $modifyFiles['file_store_status'] = 1;

        return $this->where('file_key', $file_key)->update($modifyFiles);;
    }
}