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
namespace app\admin\controller;


use app\admin\model\AssetModel;
use app\admin\model\WeedModel;
use cmf\controller\AdminBaseController;
use app\admin\model\PortalPostModel;
use app\admin\service\PostService;
use app\admin\model\PortalCategoryModel;
use think\Db;
use app\admin\model\ThemeModel;
use think\Exception;

class AssetController extends AdminBaseController
{
    public function index()
    {
        $assetService = new AssetModel();
        $param = $this->request->param();

        $assets = $assetService->AssetList($param);
        $assets->appends($param);

        $this->assign('assets', $assets->items());
        $this->assign('page', $assets->render());

        $this->assign('suffix', isset($param['suffix']) ? $param['suffix'] : 0);
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('assetType', isset($param['assetType']) ? $param['assetType'] : 0);
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('website' , config('server_address'));
        return $this->fetch();
    }

    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');

        $assetService = new AssetModel();
        try {
            $asset = $assetService->where('id', $id)->find()->toArray();
        } catch (Exception $exception) {
        }
        $this->assign('asset' , $asset);
        $this->assign('id', $id);
        return $this->fetch();
    }

    public function editPost()
    {

        if ($this->request->isPost()) {
            $data   = $this->request->param();
            empty($data['filename']) && $this->error("图片名称不能为空");
            $assetService = new AssetModel();
            $assetService->where('id' , $data['id'])
                ->update([
                    'filename' => filter_var($data['filename'],FILTER_SANITIZE_STRING),
                    'type' => $data['assetType']
                ]);
            $this->success('保存成功!');

        }
    }

}