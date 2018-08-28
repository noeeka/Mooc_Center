<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 2018/7/20
 * Time: 10:43
 */

namespace app\v1\controller;

use app\v1\model\WeedModel;

class Upload extends Core
{

    //上传
    public function upload()
    {
        if ($this->request->isPost()) {
            $file = $this->request->file('file');
            $user_token = $this->request->param('user_token');
            if ($user_token) {
                $res = checkUserToken($user_token);
                if (true !== $res) {
                    return $res;
                }
            } else {
                $center_token = $this->request->param('center_token');
                $res = checkCenterToken($center_token);
                if (true !== $res) {
                    return $res;
                }
            }
            if ($file) {
                $info = $file->move(config('upload_dir'));
                if ($info) {
                    $result['file_path'] = $info->getSaveName();
                    $result['file_url'] = config('upload_server') . $info->getSaveName();
                    //图片上传到文件服务器
                    if(in_array($info->getExtension(), ['jpg', 'png', 'jpeg'])){
                        $file_path = config('upload_dir') . $info->getSaveName();
                        $weedModel = new WeedModel();
                        $fileKey = $weedModel->getFileKey();
                        $weedModel->createTask($fileKey, [
                            "filePath" => $file_path,
                            "file_store_key" => $fileKey,
                            "fileKey" => $fileKey,
                            "file_type" => $info->getExtension(),
                            "task" => "storeFile"
                        ]);
                        $result['img_key'] = $fileKey;
                        $result['img_url'] = $weedModel->weedVolume . DIRECTORY_SEPARATOR . $fileKey;
                    }else if(in_array($info->getExtension(), ['mp4'])){
                        $result['total_time'] = exec("ffmpeg -i ".dirname(dirname(dirname(dirname(__FILE__))))."/public/upload/".$result['file_path']." 2>&1 | grep 'Duration' | cut -d ' ' -f 4 | sed s/,//");
                        $img_path = config('upload_dir').time() .'.png';
                        $cmdImg = "ffmpeg -y -ss 10 -i " .dirname(dirname(dirname(dirname(__FILE__))))."/public/upload/".$result['file_path'].
                            " -vframes 1 -f image2 -s 120*120 " . $img_path;
                        exec($cmdImg, $outputImg, $errorImg);
                        $file_path = $img_path;
                        $weedModel = new WeedModel();
                        $fileKey = $weedModel->getFileKey();
                        $weedModel->createTask($fileKey, [
                            "filePath" => $file_path,
                            "file_store_key" => $fileKey,
                            "fileKey" => $fileKey,
                            "file_type" => 'png',
                            "task" => "storeFile"
                        ]);
                        $result['img_key'] = $fileKey;
                        $result['img_url'] = $weedModel->weedVolume . DIRECTORY_SEPARATOR . $fileKey;
                    }
                    return ok($result, 28101, '上传成功');
                } else {
                    return fail(28001, $info->getError());
                }
            } else {
                return fail(28002, '上传失败');
            }
        }
    }

}