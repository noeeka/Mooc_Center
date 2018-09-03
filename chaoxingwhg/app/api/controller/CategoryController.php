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

use app\admin\model\ActivityModel;
use app\admin\model\BannerModel;
use app\admin\model\BaseinfoModel;
use app\admin\model\DiscussionModel;
use app\admin\model\LiveBroadcastModel;
use app\admin\model\PerformModel;
use app\admin\model\PortalCategoryModel;
use app\admin\model\PortalPostModel;
use app\admin\model\RoomModel;
use app\admin\model\VenueModel;

class CategoryController extends Base
{
    public function getdata()
    {
        $id = input('param.id', 0);
        $page = input('param.start', 1, 'intval');
        $len = input('param.len', 10, 'intval');
        $list = [];
        $thisNode = PortalCategoryModel::get($id);

        if($thisNode == null){
            return $this->output_error(18001, '获取列表失败');
        }
        if($thisNode->type == 2 || $thisNode->type == 3){
            $list = $this->getArticle($id, $page, $len);
        }elseif ($id == 6) {
            //轮播
            $list = $this->getBanner($page, $len);
        } elseif ($id == 8 || $id == 12) {
            //文章
            $list = $this->getArticle($id, $page, $len, 0);
        } elseif ($id == 16) {
            //活动报名
            $list = $this->getActivity($page, $len);
        } elseif ($id == 36) {
            //直播
            $list = $this->getLive($page, $len);
            foreach($list as $k => $v){
                $list[$k]['target'] = 1;
            }
        }elseif($id == 43){
            $list = $this->getVolunteer($page, $len);
        } elseif ($id == 31) {
            //文化点单
            $list = $this->getCulture($page, $len);
        } elseif ($id == 19) {
            //慕课
            $list = $this->getArticle($id, $page, $len, 1);
            foreach($list as $k => $v){
                $list[$k]['target'] = 1;
            }
        } elseif ($id == 17) {
            //场馆预约
            $list = $this->getRoom($page, $len);
        } elseif ($id == 18) {
            //全景平台
            $list = $this->getArticle($id, $page, $len, 1);
            foreach($list as $k => $v){
                $list[$k]['target'] = 1;
            }
        } elseif($id == 20){
            $list = $this->getAbout();
        }
        elseif($id == 85){
            $list = $this->getVenue($page, $len);
        }
        return $this->output_success(18101, $list, '获取列表成功');
    }

    private function getAbout(){
        $about = BaseinfoModel::get(0)->value('about');
        return cmf_replace_content_file_url(htmlspecialchars_decode($about));
    }

    public function wxAbout(){
        $baseinfo = BaseinfoModel::get(0)->toArray();
        $data['tel'] = $baseinfo['venue_tel'];
        $data['about'] = cmf_replace_content_file_url(htmlspecialchars_decode($baseinfo['about']));
        $data['head_img'] = '';
        $more = PortalCategoryModel::where('id', 20)->value('more');
        if(!empty($more)){
            $more = json_decode($more, true);
            if(is_array($more) && isset($more['about_img'])){
                $data['head_img'] = cmf_get_image_preview_url($more['about_img']);
            }
        }
        return $this->output_success(18101, $data, '获取列表成功');;
    }
    private function getVolunteer($page, $len){
        $activityModel = new ActivityModel();
        $where = [];
        $where['a.volun_type'] = ['neq', 0];
        $where['a.type'] = 0;
        $where['a.delete_time']=0;
        $where['a.status'] = 1;
        $where['v.status'] = 1;
//        $volun = $activityModel->alias('a')->join('venue v','v.id=a.venue')->where(['a.status' => 1, 'a.delete_time' => 0,'v.status'=>1])->field('a.id, a.title,a.thumb, a.abstract,a.published_time as time ,a.need_baoming,a.start_time,a.end_time,a.baoming_start_time,a.baoming_end_time,v.name as venue_name')->where(['a.volun_type','neq',0])->order(['a.is_top' => 'desc', 'a.published_time' => 'desc'])->limit($page, $len)->select();
        $volun = $activityModel->alias('a')->join('venue v', 'v.id = a.venue')->join('volun_type vt', 'vt.id =a.volun_type')->where($where)->field('a.id, a.title,a.thumb, a.abstract,a.published_time as time,v.name as venue_name')->order('a.is_top desc,a.published_time desc,a.id desc')->limit($page, $len)->select();
        foreach ($volun as &$v) {
            $v['thumb'] = cmf_get_image_preview_url($v['thumb']);
            $v['url'] = 'portal/volunteer/reportsread?id=' . $v['id'];
        }

        return $volun;
    }

    private function getRoom($page, $len)
    {
        $activity = (new RoomModel())
            ->alias('r')
            ->join('venue v', 'v.id=r.venue')
            ->where(['r.status'=>1,'v.status'=>1])
            ->field('r.id,r.name as title,r.thumb,r.abstract,r.abstract,r.publish_time as time,v.name')
            ->order(['r.publish_time' => 'desc'])->limit($page, $len)->select();;
        foreach ($activity as &$v) {
            $v['thumb'] = cmf_get_image_preview_url($v['thumb']);
            $v['url'] = '/portal/room/read/?id=' . $v['id'];
            $v['mb_url'] = 'wx2/venue/read?id='.$v['id'];
        }
        return $activity;
    }
    private function getVenue($page, $len)
    {
        $venue = VenueModel::where(['status' => 1])->field('id, name as title, thumb,venue_addr,publish_time as time,page_view,hit_like')->order(['publish_time' => 'desc'])->limit($page, $len)->select();
        foreach ($venue as &$v) {
            $v['thumb'] = cmf_get_image_preview_url($v['thumb']);
            $v['url'] = '/portal/total/read/?id=' . $v['id'];
        }
        return $venue;
    }

    private function getCulture($page, $len)
    {
        $perform = new PerformModel();
        $activity = $perform->alias('p')->join('venue v','p.venue = v.id')->where(['p.delete_time' => 0, 'p.type' => 1,'v.status'=>1])->field('p.id, p.title, p.thumb,"" as abstract,p.create_time as time')->order(['p.create_time' => 'desc'])->limit($page, $len)->select();
        foreach ($activity as &$v) {
            $v['thumb'] = cmf_get_image_preview_url($v['thumb']);
            $v['url'] = '/portal/culture/read/?id=' . $v['id'];
            $v['mb_url'] = '/wx2/culture/read/?id='.$v['id'];
        }
        return $activity;
    }

    private function getBanner($page = 0, $len = 4)
    {
        $banner = BannerModel::where(['type' => 1, 'status' => 1])->field('id, title,img as thumb, description as abstract, publish_time as time, url')->order(['list_order' => 'asc'])->limit($page, $len)->select();

        foreach ($banner as &$v) {
            $v['thumb'] = cmf_get_image_preview_url($v['thumb']);
        }
        return $banner;
    }

    private function getActivity($page, $len)
    {
        $activityModel = new ActivityModel();
        $activity = $activityModel->alias('a')->join('venue v','v.id=a.venue')->where(['a.status' => 1, 'a.delete_time' => 0,'v.status'=>1])->field('a.id, a.title,a.thumb, a.abstract,a.published_time as time ,a.need_baoming,a.start_time,a.end_time,a.baoming_start_time,a.baoming_end_time,v.name as venue_name')->where(['a.volun_type'=>0])->order(['a.is_top' => 'desc', 'a.published_time' => 'desc'])->limit($page, $len)->select();
        foreach ($activity as &$v) {
            $v['thumb'] = cmf_get_image_preview_url($v['thumb']);
            $v['url'] = '/portal/activity/read?id=' . $v['id'];
            $v['mb_url'] = '/wx2/activity/read?id='.$v['id'];
        }
        
        return $activity;
    }

    private function getLive($page, $len)
    {
        $activity = (new LiveBroadcastModel())->alias('l')->join('venue v','l.venueid = v.id')->where('v.status = 1')->field('l.id, l.live_name as title,l.img as thumb,"" as abstract,l.start_time as time, l.playback_link, l.live_link, l.start_time, l.end_time')->order(['l.id' => 'desc'])->limit($page, $len)->select();
        foreach ($activity as &$v) {
            $v['thumb'] = cmf_get_image_preview_url($v['thumb']);
            if ($v['start_time'] > time()) {
                //即将开始
                $v['url'] = 'javascript:getdialog("直播还未开始");';
            } elseif ($v['end_time'] < time()) {
                //已结束
                if ($v['playback_link'] == '') {
                    $v['url'] = 'javascript:getdialog("直播已结束，暂无回放链接");';
                } else {
                    $v['url'] = $v['playback_link'];
                }
            } else {
                $v['url'] = $v['live_link'];
            }
            unset($v['playback_link']);
            unset($v['live_link']);
            unset($v['start_time']);
            unset($v['end_time']);
        }
        return $activity;
    }

    private function getArticle($id, $page, $len, $flag = 0)
    {
        $ids = (new PortalCategoryModel())->getTreeIds($id);
        $ids[] = $id;
        $article = (new PortalPostModel())
            ->alias('a')
            ->join('__PORTAL_CATEGORY_POST__ b', 'a.id=b.post_id')
            ->join('cxtj_venue c', 'a.venue = c.id')
            ->where(['b.category_id' => ['in', $ids], 'a.delete_time' => 0, 'a.post_status' => 1,'c.status'=>1])
            ->order(['is_top' => 'desc', 'published_time' => 'desc'])
            ->field('a.venue,a.id,post_title as title,more as thumb,post_excerpt as abstract,published_time as time,is_top, a.link, a.post_source,c.name')
            ->limit($page, $len)
            ->group('a.id')
            ->select();

        foreach ($article as &$v) {
            $thumb = json_decode($v['thumb'], true);
            if (is_array($thumb) && isset($thumb['thumbnail'])) {
                $v['thumb'] = cmf_get_image_preview_url($thumb['thumbnail']);
            } else {
                $v['thumb'] = '';
            }
            if ($flag == 1) {
                $v['url'] = $v['link'];
                $v['abstract'] = $v['post_source'];
            } else {
                $v['url'] = '/portal/category/read/?id=' . $v['id'] . '&cid='.$id;
                $v['mb_url'] = '/wx2/category/read/?id='. $v['id'];
            }
            unset($v['link']);
            unset($v['post_source']);
        }
        return $article;
    }

    /**ink
     * 链接列表
     * @param array $param
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function index($param = [])
    {
        $portalCategoryModel = new PortalCategoryModel();

        //返回的数据必须是数据集或数组,item里必须包括id,name,如果想表示层级关系请加上 parent_id
        $res = $portalCategoryModel->getList();
        if ($res) {
            return $this->output_success(18101, $res, '获取列表成功');
        } else {
            return $this->output_error(18001, '无视频');
        }
    }

    public function child()
    {
        $id = input('param.id', 0, 'intval');
        $max_depth = input('param.max_depth', 1, 'intval');
        $portalCategoryModel = new PortalCategoryModel();

        //返回的数据必须是数据集或数组,item里必须包括id,name,如果想表示层级关系请加上 parent_id
        $res = $portalCategoryModel->getChild($id, $max_depth);
        //获取父级分类信息
        //$parent=$portalCategoryModel->where()->field('id,name,parent_id,list_order,type,_level')->find();
        return $this->output_success(18101, $res, '获取列表成功');
    }

    public function menu()
    {
        $portalCategoryModel = new PortalCategoryModel();

        //返回的数据必须是数据集或数组,item里必须包括id,name,如果想表示层级关系请加上 parent_id
        $res = $portalCategoryModel->menu();
        return $this->output_success(18101, $res, '获取列表成功');
    }

    public function wxmenu()
    {
        $portalCategoryModel = new PortalCategoryModel();

        //返回的数据必须是数据集或数组,item里必须包括id,name,如果想表示层级关系请加上 parent_id
        $res = $portalCategoryModel->menu(1);
        return $this->output_success(18101, $res, '获取列表成功');
    }

    public function read(){
        $id = input('param.id', 0, 'intval');
        $thisNode = PortalCategoryModel::get($id);
        if($thisNode){
            return $this->output_success(18101, $thisNode, '获取列表成功');
        }else{
            return $this->output_error(18001, '获取列表失败');
        }
    }
}
