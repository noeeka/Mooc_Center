<?php
/**
 * Created by PhpStorm.
 * User: zyf
 * Date: 2018/3/5
 * Time: 16:57
 */

namespace app\api\controller;


use app\admin\model\CollectProductionModel;
use app\admin\model\ProductionCollectModel;
use app\admin\model\ProductionCollectTypeModel;
use app\admin\model\ProductionCollectAwardModel;
use app\admin\model\SfzimgModel;
use app\user\model\UserModel;
use app\admin\model\ProductionCommentsModel;
use think\Db;
use token\Token;
use think\Request;

class ProductcollectController extends Base
{
    //获取所有作品征集
    public function index()
    {
        //当前页
        $page = input('page', 1, 'intval');
        //场馆id
        $venue_id = input('venue_id', 0, 'intval');
        //区域id
        $area_id = input('area_id', 0, 'intval');
        //征集类型id
        $activity_type_id = input('activity_type_id', 0, 'intval');
        //排序方式
        $order = input('order', 'id','string');
        //一页显示条数
        $len = input('len', 6, 'intval');

        $where = '';
        if (!empty($venue_id)) {
            $where['p.venue'] = $venue_id;
        }
        if (!empty($area_id)) {
            $area_path = Db::name('area')->where('id', $area_id)->value('path');
            $area_ids = Db::name('area')->where(['path' => ['like', "%$area_path%"]])->column('id');
            $venue_ids = Db::name('venue')->where(['address' => ['in', $area_ids]])->column('id');

            if (!empty($venue_id)) {
                $where['p.venue'] = array(['eq', $venue_id], ['in', $venue_ids], 'and');
            } else {
                $where['p.venue'] = ['in', $venue_ids];
            }
        }
        if (!empty($activity_type_id)) {
            $where['pt.id'] = $activity_type_id;
        }
        if (!empty($order) && $order == 'page_view') {
            $order = 'p.page_view desc';
        } else {
            $order = 'p.id desc';
        }
        $where['p.status'] = 1;
        $where['p.delete_time'] = 0;

        $productModel = new ProductionCollectModel();
        $where['v.status'] = 1;
        //当前页数据
        $product_list = $productModel->alias('p')
            ->join('venue v', 'p.venue =v.id', 'left')
            ->join('production_collect_type pt', 'p.type=pt.id', 'left')
            ->join('collect_production cp','p.id=cp.activity_id','left')
            ->where($where)
         //   ->where(['cp.status'=>1,'cp.deletetime'=>0])
            ->field("p.*,count(distinct cp.userid) as pnumber,count(cp.id) as zuopin_number, v.name venue_name ")
            ->group('p.id')
            ->order($order)
            ->page($page, $len)
            ->select();

        //活动总数
        $total_production_count = $productModel->alias('p')
            ->join('venue v', 'p.venue =v.id', 'left')
            ->join('production_collect_type pt', 'p.type=pt.id', 'left')
            ->where($where)
            ->count();

        if ($product_list->isEmpty()) {
            return $this->output_success('13101', ['list' => []], '作品征集获取失败');
        }

        foreach ($product_list as &$item) {
            $item['thumb'] = cmf_get_image_preview_url($item['thumb']);

            $item['title'] =$item['name'];
            $item['format_start_time'] = date('Y-m-d' , $item['collect_start_time']);
            $item['format_end_time'] = date('Y-m-d' , $item['collect_end_time']);
            //征集状态
            if( $item['collect_start_time'] >time()){
                $item['status']=1;//预告
            }elseif($item['collect_start_time'] <=time() && $item['collect_end_time'] >time()){
                $item['status']=2;//征集中
            }elseif($item['choose_start_time'] <=time() && $item['choose_end_time'] >time()){
                $item['status']=3;//评选中
            }else{
                $item['status']=4;//已结束
            }
        }

        $result['list'] = $product_list;
        $result['count'] = $total_production_count;
        if ($result) {
            return $this->output_success('13101', $result, '作品征集获取成功');
        } else {
            return $this->output_error('13100', '', '作品征集获取失败');
        }
    }

    /**
     * 获取单个征集活动
     * $activity_id 征集活动id
     */
    public function read()
    {
        $id = input('id', 0, 'intval');

        $productionCollectModel =new ProductionCollectModel();
        $production_collect=$productionCollectModel->where(['id'=>$id])->find();
        $production_collect['format_preview_start_time']=date('Y-m-d',$production_collect['preview_start_time']);
        $production_collect['format_preview_end_time']=date('Y-m-d',$production_collect['preview_end_time']);
        $production_collect['format_collect_start_time']=date('Y-m-d',$production_collect['collect_start_time']);
        $production_collect['format_collect_end_time']=date('Y-m-d',$production_collect['collect_end_time']);
        $production_collect['format_choose_start_time']=date('Y-m-d',$production_collect['choose_start_time']);
        $production_collect['format_choose_end_time']=date('Y-m-d',$production_collect['choose_end_time']);
        $production_collect['format_publish_time']=date('Y-m-d',$production_collect['publish_time']);
        $production_collect['thumb']=cmf_get_image_preview_url($production_collect['thumb']);
        $production_collect['content']=$productionCollectModel->getContentAttr($production_collect['content']);
        $production_collect['is_publish']=$production_collect['publish_time']<time()?1:0;
        $production_collect['is_collecting']=$production_collect['collect_start_time']<time()?1:0;

        //征集活动状态
        if(($production_collect['collect_start_time']>time())){
            $production_collect['activity_status']=4;//未开始
        }elseif(($production_collect['collect_start_time']<=time())&&($production_collect['collect_end_time']>=time())){
            $production_collect['activity_status']=3;//征选中
        }elseif(($production_collect['choose_start_time']<=time())&&($production_collect['choose_end_time']>=time())){
            $production_collect['activity_status']=2;//评选中
        }else{
            $production_collect['activity_status']=1;//已结束
        }


        //作品总数
        $collectModel =new CollectProductionModel();
        $zuopin_count= $collectModel->where(['activity_id'=>$id,'status'=>1,'deletetime'=>0])->count();
        $production_collect['zuopin_count']=$zuopin_count;
        $productionCollectModel->where(['id'=>$id])->setInc('page_view');

        if ($production_collect) {
            return $this->output_success('13101', $production_collect, '作品获取成功');
        } else {
            return $this->output_error('13100', '', '作品获取失败');
        }
    }
    /**
     * 参赛作品
     * $activiy_id 征集活动id
     */
     public function attend_productions(){
         $id = input('id', 0, 'intval');
         $order=input('order','created_at','string');


         $collectProduction =new CollectProductionModel();
         $production_collect  =  $collectProduction->alias('cp')
                                                   ->join('user u','cp.userid =u.id','left')
                                                   ->where(['activity_id'=>$id,'cp.status'=>1, 'deletetime'=>0])
                                                   ->field('cp.*,u.user_nickname,u.avatar')
                                                   ->order($order.' desc')
                                                   ->select();

         //作品总数
         $total_production_count =$collectProduction->where(['activity_id'=>$id,'status'=>1, 'deletetime'=>0])->count();

         foreach($production_collect as &$v){
             $v['has_avatar']=!empty($v['avatar'])?1:0;
             $v['format_careated_at'] = date('Y-m-d', $v['created_at']);
             $v['thumb'] = cmf_get_image_preview_url($v['thumb']);
             $v['avatar'] = cmf_get_image_preview_url($v['avatar']);
             $v['production_like']=!empty($production_like)?$production_like:0;

             $vv['has_avatar']=!empty( $vv['avatar'])?1:0;
         }


         $res['total_count']=$total_production_count;
         $res['list']=$production_collect;
         $res['order']=$order;
         if ($production_collect) {
             return $this->output_success('13101', $res, '作品获取成功');
         } else {
             return $this->output_error('13100', '', '作品获取失败');
         }

     }

    /**
     * 获奖公布
     * $activity_id 征集活动id
     */
     public function publish_award(){
             $activity_id =input('id',0,'intval');
             $awardModel=new ProductionCollectAwardModel();
             $award_list=$awardModel->where(['activity_id'=>$activity_id])->find();
             $award_list['award_publicity']=$awardModel->getPostContentAttr($award_list['award_publicity']);
             $award_info=!empty($award_list['award_production'])?unserialize($award_list['award_production']):'';
             if(!empty($award_info)){
                 $award_production=[];
                 foreach ($award_info as $key=>$v){
                     $produciton=explode(',',$v['award_production']);
                     $produciton_list=Db::name('collect_production')
                                     ->alias('cp')
                                     ->join('user u','cp.userid = u.id','left')
                                     ->where(['cp.id'=>['in',$produciton]])
                                     ->field('cp.*,u.user_nickname,u.avatar')
                                     ->select();
                     $produciton_list1=[];
                     foreach($produciton_list as &$vv){
                         $vv['avatar']=cmf_get_image_preview_url($vv['avatar']);
                         $vv['has_avatar']=!empty( $vv['avatar'])?1:0;
                         $vv['thumb']=cmf_get_image_preview_url($vv['thumb']);
                         $produciton_list1[]=$vv;
                     }
                     $award_production[$key]['avard_name']=$v['award_name'];
                     $award_production[$key]['avard_production_list']=$produciton_list1;
                 }
                 $award_list['award_production']=$award_production;
                 if($award_production){
                     return $this->output_success('13101', $award_list, '作品获取成功');
                 }else{
                     return $this->output_error('13101', '作品获取失败');
                 }
             }else{
                 return $this->output_error('13100',  '作品获取失败');
             }


     }

    /**
     *  上传征集作品
     */
    function upload_production()
    {
        $this->check_sign();

        $token = input('post.token');
        $id = input('param.activity_id');
        $user_id = Token::get_user_id($token);
        $name = input('param.zuopin_name');
        $thumb = input('param.thumb');
        $content = input('param.content');

        if (empty($name)) {
            return $this->output_error(13006, '名称不能为空');exit;
        }
        if (empty($thumb)) {
            return $this->output_error(13006, '封面图不能为空');exit;
        }
        if (empty($content)) {
            return $this->output_error(13006, '作品内容不能为空');exit;
        }

        if (empty($user_id)) {
            return $this->output_error(13000, '未登录，请先登录且实名认证后方可上传！');exit;
        } else {
            if (empty($id)) {
                return $this->output_error(13001, '没有该征集活动！');exit;
            } else {
                $user = db('user')->where(['id' => $user_id, 'user_role' =>['in',[1,2]]])->find();
                if (!empty($user)) {

                            $collectProductionModel = new CollectProductionModel();

                            $data['activity_id'] = $id;
                            $data['created_at'] = time();
                            $data['userid'] = $user_id;
                            $data['name'] = $name;
                            $data['thumb'] = $thumb;
                            $data['content'] =$collectProductionModel->setPostContentAttr($content);

                            $res = $collectProductionModel->allowField(true)->data($data)->isUpdate(false)->save();
                            if ($res === false) {
                                return $this->output_error(13002, '上传失败！');exit;
                            }else{
                                return $this->output_success(13002, '', '上传成功！');exit;
                            }

                } else {
                    return $this->output_error(13004, '未认证，请先到用户中心认证');exit;
                }
            }
        }
    }
    /**
     * 作品详情
     */
    public function zuopin_detail(){
        $id =input('param.id');
        $token = input('post.token');
        $user_id = Token::get_user_id($token);
          if(!empty($id)){
              $collectProductionModel =new CollectProductionModel();
              $row=$collectProductionModel->Where(['id'=>$id])->find();
              $row['content']=$collectProductionModel->getPostContentAttr($row['content']);
              if($row){
                  $row['formate_created_at']=date('Y-m-d',$row['created_at']);
                  //是否已收藏
                  $row['is_like']=0;
                  if(!empty($user_id)){
                      $count=Db::name('like')->where(['user_id'=>$user_id,'resource_id'=>$id])->count();
                      if(!empty($count)){
                          $row['is_like']=1;
                      }
                  }
                  return $this->output_success(13004,$row, '作品获取成功');exit;
              }else{
                  return $this->output_error(13004, '没有该作品');exit;
              }
          }else{
              return $this->output_error(13004, '没有该作品');exit;
          }
    }

    /**
     * 评论列表
     */
    public function comments_list(){
        $page = input('param.page', 1);
        $len = input('param.len', 10);
        $zuopin_id = input('param.zuopin_id', 0, 'intval');
        $comments = new ProductionCommentsModel();
        $parent = $comments->alias('pc')->where(['pc.parentid' => 0, 'pc.status' => 1, 'pc.zuopin_id' => $zuopin_id])
            ->page($page, $len)
            ->join('user u', 'u.id =pc.userid')
            ->field('pc.*,u.user_nickname,u.avatar')
            ->order(['pc.create_time' => 'desc'])
            ->select()
            ->toArray();
        $num = $comments->alias('pc')->where(['pc.parentid' => 0, 'pc.status' => 1, 'pc.zuopin_id' => $zuopin_id])
            ->join('user u', 'u.id =pc.userid')
            ->field('pc.*,u.user_nickname')->count(1);
        $res = array();
        foreach ($parent as $value) {
            $id = $value['id'];
            $value['avatar_url'] = cmf_get_image_preview_url($value['avatar']);
            $value['list'] = $comments->alias('pc')->where(['pc.level_pid' => $id])
                ->where(['pc.status' => 1])
                ->join('user d', 'd.id = pc.userid')
                ->field('pc.*,d.user_nickname,d.avatar')
                ->order(['pc.create_time' => 'ASC'])
                ->select();
            $res[] = $value;
        }
        return $this->output_success(11101, ['list' => $res, 'num' => $num], '获取列表成功');
    }
    /**
     * 发表评论
     */
    public function comment(){
        $uid = $this->getuid();
        $id = input('parentid', 0);
        $zuopin_id = input('articleid', 0);
        $content = input('content', '');
        if (empty($content)) {
            return $this->output_error(13006, '评论内容不能为空');
        }
        $data = ['zuopin_id' => $zuopin_id, 'userid' => $uid, 'comment' => $content, 'updatetime' => time(), 'create_time' => time(), 'status' => 0];
        $comment = new ProductionCommentsModel();
        if ($id == 0) {
            $data['parentid'] = 0;
            $data['level_pid'] = 0;
        } else {
            $data['parentid'] = $id;
            $com = $comment->where('id', $id)->find();
            if (empty($com)) {
                return $this->output_error(12000, '评论失败');
            }
            $com = $com->toArray();
            $data['level_pid'] = $com['parentid'] == 0 ? $com['id'] : $com['level_pid'];
        }
        $comment->save($data);
        CollectProductionModel::where(['id' => $zuopin_id])->setInc('comments_count');
        return $this->output_success(12101, [], '评论成功');
    }

    /**
     * 删除评论
     */
    public function delete($param = [])
    {
        $uid = $this->getuid();
        $parentid = input('parentid', 0);
        $comment = new ProductionCommentsModel();
        $res = $comment->where(function ($query) use ($parentid, $uid) {
            $query->where(['id' => $parentid, 'userid' => $uid]);
        })->whereor(function ($query) use ($parentid) {
            $query->where(['parentid' => $parentid]);
        })->delete();

        if ($res) {
            return $this->output_success(11101, $res, '删除成功');
        } else {
            return $this->output_error(11100, $res, '删除失败');
        }

    }
    /**
     * 征集作品收藏
     */
    public function collect()
    {
        $data['user_id'] = $this->getuid();
        $data['article_id'] = input('param.id', 0, 'intval');
        $id = db('collect')->where($data)->value('id');
        if ($id > 0) {
            db('collect')->where('id', $id)->delete();
            $post_collect = CollectProductionModel::where('id', $data['article_id'])->value('production_collect', 0);
            if ($post_collect > 0) {
                CollectProductionModel::where('id', $data['article_id'])->setDec('production_collect');
            }
            return $this->output_success('13101', [], '取消收藏成功');
        } else {
            $data['create_time'] = time();
            db('collect')->insert($data);
            CollectProductionModel::where('id', $data['article_id'])->setInc('production_collect');
            return $this->output_success('13102', [], '收藏成功');
        }
    }
    /**
     * 征集作品点赞
     */
    public function like()
    {
        $data['user_id'] = $this->getuid();
        $data['resource_id'] = input('param.id', 0, 'intval');
        $data['type'] = 5;
        $id = db('like')->where($data)->value('id');
        if ($id > 0) {
            db('like')->where('id', $id)->delete();
            $post_collect = CollectProductionModel::where('id', $data['resource_id'])->value('production_like', 0);
            if ($post_collect > 0) {
                CollectProductionModel::where('id', $data['resource_id'])->setDec('production_like');
            }
            return $this->output_success('13101', [], '取消点赞成功');
        } else {
            $data['create_time'] = time();
            db('like')->insert($data);
            CollectProductionModel::where('id', $data['resource_id'])->setInc('production_like');
            return $this->output_success('13102', [], '点赞成功');
        }
    }
    /**
     * 征集活动类型
     */
    public function production_collect_type(){
          $production_collect_type_model =new ProductionCollectTypeModel();
          $types=$production_collect_type_model->where(['status'=>1])->select();
          if(!empty($types)){
              return $this->output_success('13101', $types, '征集类型获取成功');
          }else{
              return $this->output_error(11100,'删除失败');
          }
    }
}