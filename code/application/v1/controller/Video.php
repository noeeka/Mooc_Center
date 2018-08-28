<?php
/**
 * Created by PhpStorm.
 * User: zhangchun
 * Date: 2018/6/27
 * Time: 16:40
 */

namespace app\v1\controller;

use app\v1\model\MoocUser;
use app\v1\model\Video as Videos;
use app\v1\model\MoocCenter;

class Video extends Core
{
    /**
     * 视频列表
     * @param center_token 场馆令牌
     * @param source 来源(center_id)
     * @param type 用户类型 1后台  2用户
     * @param user_id 上传用户id (center_id or user_id)
     * @param page 页数
     * @param len  长度
     * @return array|bool
     */
    public function  index(){
        $source = input('param.source',0,'intval');
        $type = input('param.type',0,'intval');
        $user_id = input('param.uploder_id',0,'intval');
        $start_time = input('param.start_time',0,'intval');
        $end_time = input('param.end_time',0,'intval');
        $title = input('param.title','','string');
        $page = input('param.page',1,'intval');
        $len = input('param.len',10,'intval');

        $msg = verify();
        if($msg['status'] == 0){
            return $msg;
        }else{
            $center_id = $msg['data']['center_id'];
        }

        $where = [];
        $where['v.delete_time'] = 0;
        if(!empty($source)){
            $where['v.center_id'] = $source;
        }

        if(!empty($title)){
            $where['v.title'] = ['like',"%$title%"];
        }

        if(!empty($start_time)){
            $where['v.create_time'] = ['>=',$start_time];
        }

        if(!empty($end_time)){
            $where['v.create_time'] = ['<=',$end_time];
        }

        if(!empty($start_time) && !empty($end_time)){
            $where['v.create_time'] = ['between',[$start_time,$end_time]];
        }

        $join[] = ['mooc_center mc','mc.id=v.center_id'];
        $field = 'v.*,mc.center_name';
        if(!empty($type)){
            if($type == 1){
                if(!empty($user_id)){
                    $where['v.user_id'] = $user_id;
                    $where['v.type'] = 1;
                }
            }else{
                if(!empty($user_id)){
                    $where['v.user_id'] = $user_id;
                    $where['v.type'] = 2;
                }
            }
        }

        $videoModel = new Videos();
        if($center_id != 1){
            $where['center_id'] = $center_id;
        }
        $videoList = $videoModel->alias('v')->join($join)->where($where)->order(['v.center_id'=>'asc','v.id'=>'asc'])->field($field)->page($page,$len)->select();
        $num = $videoModel->alias('v')->join($join)->where($where)->field($field)->count(1);
        if($videoList){
            $videoList = \collection($videoList)->toArray();
            foreach($videoList as $key=>$value){
                if($value['type'] == 1){
                    //后台
                    $center = (new MoocCenter())->where(['id'=>$value['user_id']])->find();
                    $videoList[$key]['uploder'] = $center['center_name'];
                    $videoList[$key]['creator_center_id'] = $center['id'];

                }else{
                    //用户
                    $user = (new MoocUser())->where(['id'=>$value['user_id']])->find();
                    $videoList[$key]['uploder'] = $user['nick_name'];
                    $videoList[$key]['creator_center_id'] = $user['center_id'];
                }
                if($value['status'] == 1){
                    $videoList[$key]['state'] = '显示';
                }else{
                    $videoList[$key]['state'] = '隐藏';
                }
            }
        }else{
            return $this->fail(11000,'获取视频失败');
        }

        return $this->ok(['list'=>$videoList,'num'=>$num],11111,'获取视频信息成功');
    }

    /**
     * 视频列表
     * @param center_id 场馆id
     * @param page 页数
     * @param len  长度
     * @return array|bool
     */
    public function  getVideoList(){
        $center_id = input('param.center_id',0,'intval');
//        $page = input('param.page',1,'intval');
//        $len = input('param.len',10,'intval');

        $videoModel = new Videos();
        $where = [];
        $where['delete_time'] = 0;
        $where['status'] = 1;
        $where['center_id'] = $center_id;
//return $where;
        $videoList = $videoModel->where($where)->select();
//        $num = $videoModel->where($where)->count(1);
//        if($videoList){
//            $videoList = \collection($videoList)->toArray();
//            foreach($videoList as $key=>$value){
//                if($value['type'] == 1){
//                    //后台
//                    $center = (new MoocCenter())->where(['id'=>$value['user_id']])->find();
//                    $videoList[$key]['uploder'] = $center['center_name'];
//                    $videoList[$key]['creator_center_id'] = $center['id'];
//
//                }else{
//                    //用户
//                    $user = (new MoocUser())->where(['id'=>$value['user_id']])->find();
//                    $videoList[$key]['uploder'] = $user['nick_name'];
//                    $videoList[$key]['creator_center_id'] = $user['center_id'];
//                }
//                if($value['status'] == 1){
//                    $videoList[$key]['state'] = '显示';
//                }else{
//                    $videoList[$key]['state'] = '隐藏';
//                }
//            }
//        }else{
//            return $this->fail(11000,'获取视频失败');
//        }

        return $this->ok($videoList,11111,'获取视频信息成功');
    }


    /**
     * 添加视频
     * @param center_token 场馆令牌  上传者token  上传者为后台用户是传center_token,为前台用户传user_token
     * @param user_token user令牌    上传者token
     * @param center_id 来源 文化馆id  给哪个文化馆创建视频
     * @param title 标题
     * @param thumb 缩略图
     * @param url 视频url
     *  @param type 类型:1 后台添加  2 用户添加
     * @return array|bool
     */
    public function save(){
//        $_GET['user_token'] = 'a99ebc96a437f31656a169c31ef928f7c39d7ef6';
//        $_GET['user_login'] = 'test12345';
//        $_GET['user_pass'] = '123456';
//        $_GET['id'] = 3;
//        $_GET['timestamp'] = time();
//        $salt = 'l5Y2Q';
//        $_GET['sign'] = encrypt_key(['v1/video/save', $_GET['timestamp'], $_GET['user_token'], $salt], '');
//        $_GET['title'] = '用户创建视频';
//        $_GET['thumb'] = 'admin/20180315/e30ee982eae4efb1251873311548dd9a.png';
//        $_GET['url'] = 'http:www.baidu.com';
//        $_GET['center_id'] = 14;

//        $center_token = input('param.center_token');//上传者token
//        $user_token = input('param.user_token');//上传者
        $center_id = input('center_id','0','intval'); //来源
        $title = input('param.title');
        $thumb = input('param.thumb');
        $url = input('param.url','','string');
        $video_time = input('param.video_time',0,'intval');
        $status = input('param.status',0,'intval');

        $msg = verify();
        if($msg['status'] == 0){
            return $msg;
        }else{
            $user_id = $msg['data']['user_id'];
            $type = $msg['data']['type'];
            if($msg['data']['center_id'] != 1){
                $center_id = $msg['data']['center_id'];
            }
        }

        $data = [
            'center_id'=>$center_id,
            'title'=>$title,
            'user_id'=>$user_id,
            'thumb'=>$thumb,
            'url'=>$url,
            'type'=>$type,
            'video_time'=>$video_time,
            'status'=>$status,
            'create_time'=>time()
        ];

        $videoModel = new Videos();
        $result = $videoModel->allowField(true)->validate(true)->isUpdate(false)->save($data);
        if($result){
            return $this->ok([],12111,'添加视频成功');
        }else{
            return $this->fail(12101,$videoModel->getError());
        }

    }

    /**
     * 视频编辑详情页
     * @param center_token 场馆令牌
     * @param user_token user令牌
     * @param id  视频id
     * @return array|bool
     */
    public function read(){
        $video_id = input('id',0,'intval');

        $msg = verify();
        if($msg['status'] == 0){
            return $msg;
        }
//        else{
//            $center_id = $msg['data']['center_id'];
//        }

        $videoModel = new Videos();
        $video_info = $videoModel->alias('v')->join('mooc_center mc','mc.id = v.center_id')->where(['v.id'=>$video_id])->field('v.*,mc.center_name')->find();
        if($video_info){
            if($video_info->type == 1){
                $video_info->uploder = (new MoocCenter())->where(['id'=>$video_info->user_id])->value('center_name');
            }else{
                $video_info->uploder = (new MoocUser())->where(['id'=>$video_info->user_id])->value('nick_name');
            }
            return $this->ok($video_info,13111,'获取视频成功');
        }else{
            return $this->fail(13101,'此视频不存在');
        }

    }

    /**
     * 视频编辑
     * @param center_token 场馆令牌
     * @param id  视频id
     * @param center_id  来源
     * @param title  标题
     * @param thumb  缩略图
     * @param url
     * @return array|bool
     */
    public function update(){
        $video_id = input('param.id',0,'intval');
        $centerid = input('center_id','0','intval');//来源
        $title = input('param.title');
        $thumb = input('param.thumb');
        $url = input('param.url');
        $status = input('param.status',1,'intval');
        $video_time = input('param.video_time',0,'intval');

        $msg = verify();
        if($msg['status'] == 0){
            return $msg;
        }else{
            $center_id = $msg['data']['center_id'];
        }

        $data = [
            'id'=>$video_id,
            'center_id'=>$centerid,
            'title'=>$title,
            'user_id'=>$center_id,
            'thumb'=>$thumb,
            'url'=>$url,
            'video_time'=>$video_time,
            'status'=>$status,
            'update_time'=>time()
        ];

        $videoModel = new Videos();
        $result = $videoModel->allowField(true)->validate(true)->isUpdate(true)->save($data);
        if($result){
            return $this->ok([],12111,'修改视频成功');
        }else{
            return $this->fail(12101,$videoModel->getError());
        }
    }


    /**
     * 视频删除
     * @param center_token 场馆令牌
     * @param id  视频id
     * @return array|bool
     */
    public function delete(){
        $video_id = input('param.id',0,'intval');
        $video_ids = input('param.ids/a');

        $msg = verify();
        if($msg['status'] == 0){
            return $msg;
        }else{
            $center_id = $msg['data']['center_id'];
        }

        $videoModel = new Videos();
        if(!empty($video_id)){
            $result = $videoModel->where(['center_id'=>$center_id,'id'=>$video_id])->update(['delete_time'=>time()]);
            if(empty($result)){
                return $this->fail(11200,'删除视频失败');
            }else{
                return $this->ok([],11222,'删除视频成功');
            }
        }

        if(!empty($video_ids)){
            $result = $videoModel->where(['center_id'=>$center_id,'id'=>['in',$video_ids]])->update(['delete_time'=>time()]);
            if($result === false){
                return $this->fail(11201,'删除视频失败');
            }else{
                return $this->ok([],11223,'删除视频成功');
            }
        }
    }

}
