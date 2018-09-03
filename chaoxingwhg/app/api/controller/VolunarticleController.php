<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/5/7
 * Time: 13:40
 */

namespace app\api\controller;


use app\admin\model\PhotoWallModel;
use app\admin\model\PortalCategoryModel;
use app\admin\model\PortalPostModel;
use app\portal\model\UserModel;
use think\Db;

class VolunarticleController extends Base
{
    private $reportCategory=26;

    //活动报道列表
    public function index(){
        $page = input('page',1,'intval');
        $len  = input('len',10,'intval');

        $where = [];
        $where['b.category_id'] = $this->reportCategory;
        $where['delete_time'] = 0;
        $where['post_status'] = 1;

        $postModel = new PortalPostModel();
        $postList = $postModel->alias('a')
            ->join('__PORTAL_CATEGORY_POST__ b','b.post_id = a.id ')
            ->join('venue v','v.id = a.venue')
            ->where($where)
            ->field('a.*,v.name')
            ->page($page,$len)
            ->order(['a.is_top desc', 'a.published_time desc'])
            ->select()
            ->toArray();
        

        $count = $postModel->alias('a')
            ->join('__PORTAL_CATEGORY_POST__ b','b.post_id = a.id ')
            ->join('venue v','v.id = a.venue')
            ->where($where)
            ->count(1);

        if(empty($postList)){
            return $this->output_error(11102,'获取活动列表失败');
        }else{
            //图片处理
            foreach($postList as $key=>$value){
                $postList[$key]['thumb'] = cmf_get_image_preview_url($value['more']['thumbnail']);
            }
            $portal = [];
            $portal['list'] = $postList;
            $portal['count'] = $count;
            return $this->output_success(11101,$portal,'获取活动报道成功');
        }

    }

    //活动报道详情页
    public function read(){
        $id = input('id', 0, 'intval');
        $is_photo = input('is_photo',0,'intval');

        $uid = $this->getuid(false);
        if(empty($uid)){
            //未登录
           $user_role = 2;
        }else{
            $user_role = (new UserModel())->where('id',$uid)->value('user_role');
        }

        //活动详情
        $portalPostModel = new PortalPostModel();
        $postdetail = $portalPostModel->alias('p')->join('venue v','v.id = p.venue')->where('p.id',$id)->field('p.*,v.name as venue_name')->find();


        //照片墙
        $photoModel = new PhotoWallModel();
        $photoList = $photoModel->alias('pw')->where(['pw.post_id'=> $id,'pw.status'=>1])->join('sfzimg s','s.user_id = pw.user_id')->order(['id'=>'asc'])->field('pw.*,s.realname as user_realname')->select()->toArray();
        foreach ($photoList as $key => $value) {
            $photoList[$key]['img'] = cmf_get_image_preview_url($photoList[$key]['img']);
        }


        if($postdetail){
            $postdetail['img'] = cmf_get_image_preview_url($postdetail['more']['thumbnail']);
            $post['photo'] = $photoList;
            $post['detail'] = $postdetail;
            $post['user_role'] = $user_role;
            if($is_photo != 1){
                $portalPostModel->where('id', $id)->setInc('post_hits');
            }

            return $this->output_success(12111,$post,'报道获取成功');

        }else{
            return $this->output_error(12101,'报道获取失败');
        }

    }

    //照片墙
    public function photo_wall(){
        $id = input('id', 0, 'intval');
        $page = input('page',1,'intval');
        $len  = input('len',10,'intval');
        $uid = $this->getuid(false);

        $photoModel = new PhotoWallModel();

        //照片墙
        $photoList = $photoModel->alias('pw')->where(['pw.post_id'=> $id,'pw.status'=>1])->join('sfzimg s','s.user_id = pw.user_id')->order(['id'=>'asc'])->field('pw.*,s.realname as user_realname')->page($page,$len)->select()->toArray();
        foreach ($photoList as $key => $value) {
            $photoList[$key]['img'] = cmf_get_image_preview_url($photoList[$key]['img']);
        }

        //登录用户已上传照片数量
        if(!empty($uid)){
            $photo_num = $photoModel->where(['user_id'=>$uid,'post_id'=>$id])->count(1);
            $postList['num'] = $photo_num;
        }

        if($photoList){
            return $this->output_success(12111,$photoList,'照片墙获取成功');
        }else{
            return $this->output_error(12011,'照片墙获取失败');
        }

    }

    /**
     * 获取当前用户已上传图片数量
     * @return array
     */
    public function get_my_photo(){
        $article_id = input('param.id', 0, 'intval');
        $uid = $this->getuid(false);
        $photo_num = PhotoWallModel::where(['user_id'=>$uid,'post_id'=>$article_id])->count(1);
        return $this->output_success(12111,$photo_num,'照片墙获取成功');
    }

    public function photo_add()
    {
        $cid = input('cid', 0, 'intval');
        $uid = $this->getuid(true);
        $limit_num = 15;
        $photos = input('photos/a');
        $num = count($photos);
        $photoModel = new PhotoWallModel();
        $photoList = $photoModel->where(['user_id'=> $uid,'post_id'=>$cid])->select();
        $tablenum = count($photoList);


        $is_volunterr = Db('user')->where(['id'=>$uid,'user_role'=>2])->count();
        if(!$is_volunterr){
            return $this->output_success(12011,'请先注册为志愿者');
        }

        $data = [];

        $data['user_id'] = $uid;
        $data['post_id'] = $cid;
        $data['create_at'] = time();
        if ($tablenum < $limit_num) {
            if ($tablenum + $num <= $limit_num) {
                if (is_array($photos)) {
                     foreach ($photos as $key => $value){
                         $data['img'] = $value;
                         Db::name('photo_wall')->insert($data);
                     }
                }
                return $this->output_success(12111, '', '上传成功');
            } else {
                $ablenum = $limit_num - $tablenum;
                return $this->error(12011, '你还可上传' . $ablenum . '张');
            }


        } else {
            return $this->output_error(11011, '图片数量已满');
        }

    }

}

