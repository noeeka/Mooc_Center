<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: kane <chengjin005@163.com>
// +----------------------------------------------------------------------
namespace app\user\controller;

use app\admin\model\AssetModel;
use app\admin\model\WeedModel;
use cmf\controller\AdminBaseController;
use cmf\lib\Upload;
use think\Db;
use think\View;
use WeedPhp\Transport\Curl;

/**
 * 附件上传控制器
 * Class Asset
 * @package app\asset\controller
 */
class AssetController extends AdminBaseController
{
    public function _initialize()
    {
        $adminId = cmf_get_current_admin_id();
        $userId  = cmf_get_current_user_id();
        if (empty($adminId) && empty($userId)) {
            exit("非法上传！");
        }
    }

    /**
     * webuploader 上传
     * 上传成功后，检查一下文件是否存在，如果已经存在自己数据库中了，走最下面的else逻辑
     * 如果本地库不存在的话发送到队列中等待上传，todo这里差一步 缓存一下所有上传过的file_key
     * 如果file_key存在 返回存在缓存服务器中的file_store_key，不用再发送到队列上传
     */
    public function webuploader()
    {
        if ($this->request->isPost()) {
            $weed = new WeedModel();
            $uploader = new Upload();
            $assetModel = new AssetModel();
            $result = $uploader->upload();
            if ($result === false) {
                $this->error($uploader->getError());
            }
            else if(empty($result['local_exist']) && in_array($result['suffix'] , ['jpg','png','jpeg']) ) {
                try {
                    $filePath = ROOT_PATH . 'public' . $result['url'];
                    $fileKey = $weed->getFileKey();
                    $queueResult = $weed->createTask($fileKey , [
                        "filePath"=> $filePath,
                        "file_store_key" => $fileKey ,
                        "fileKey" => $result['file_key'],
                        "file_type" => $result['suffix'],
                        "task" => "storeFile"
                    ]);
                    $result['queue'] = $queueResult;
                    $result['weedPath'] = $weed->weedVolume. DIRECTORY_SEPARATOR. $fileKey;
                    $result['weedReuslt'] = $assetModel->updateAsset($fileKey , $weed->weedVolume , $result['file_key']);
                } catch (\Exception $e) {
                    var_dump($e);
                    var_dump($e->getMessage());
                    $this->error($uploader->getError()."上传服务区出现问题获取key失败");
                }
                $this->success("上传成功!", '', $result);
            } else if($result['local_exist'] && in_array($result['suffix'] , ['jpg','png','jpeg'])) {
                $result['weedPath'] = $weed->weedVolume. DIRECTORY_SEPARATOR. $result['file_store_key'];
                $this->success("上传成功!", '', $result);
            } else {
                $result['weedPath']  = cmf_get_image_preview_url($result['url']);
                $this->success("上传成功!", '', $result);
            }

        } else {

            $uploadSetting = cmf_get_upload_setting();

            $arrFileTypes = [
                'image' => ['title' => 'Image files', 'extensions' => $uploadSetting['file_types']['image']['extensions']],
                'video' => ['title' => 'Video files', 'extensions' => $uploadSetting['file_types']['video']['extensions']],
                'audio' => ['title' => 'Audio files', 'extensions' => $uploadSetting['file_types']['audio']['extensions']],
                'file'  => ['title' => 'Custom files', 'extensions' => $uploadSetting['file_types']['file']['extensions']]
            ];

            $arrData = $this->request->param();

            if (empty($arrData["filetype"])) {
                $arrData["filetype"] = "image";
            }

            $fileType = $arrData["filetype"];

            if (array_key_exists($arrData["filetype"], $arrFileTypes)) {
                $extensions                = $uploadSetting['file_types'][$arrData["filetype"]]['extensions'];
                $fileTypeUploadMaxFileSize = $uploadSetting['file_types'][$fileType]['upload_max_filesize'];
            } else {
                $this->error('上传文件类型配置错误！');
            }

            View::share('openLocalAsset' , isset($arrData['openLocalAsset']) ? $arrData['openLocalAsset']:0 );
            View::share('filetype', $arrData["filetype"]);
            View::share('extensions', $extensions);
            View::share('upload_max_filesize', $fileTypeUploadMaxFileSize * 1024);
            View::share('upload_max_filesize_mb', intval($fileTypeUploadMaxFileSize / 1024));
            $maxFiles  = intval($uploadSetting['max_files']);
            $maxFiles  = empty($maxFiles) ? 20 : $maxFiles;
            $chunkSize = intval($uploadSetting['chunk_size']);
            $chunkSize = empty($chunkSize) ? 512 : $chunkSize;
            View::share('max_files', $arrData["multi"] ? $maxFiles : 1);
            View::share('chunk_size', $chunkSize); //// 单位KB
            View::share('multi', $arrData["multi"]);
            View::share('app', $arrData["app"]);

            $content = hook_one('fetch_upload_view');

            $tabs = ['local', 'url', 'cloud'];

            $tab = !empty($arrData['tab']) && in_array($arrData['tab'], $tabs) ? $arrData['tab'] : 'local';

            if (!empty($content)) {
                $this->assign('has_cloud_storage', true);
            }

            if (!empty($content) && $tab == 'cloud') {
                return $content;
            }

            $tab = $tab == 'cloud' ? 'local' : $tab;

            $this->assign('tab', $tab);
            return $this->fetch(":webuploader");

        }
    }

}
