<?php
/**
 * @Author: Tony Gu
 * @Date:   2017-11-23 15:20:01
 * @Last Modified time: 2017-12-01 16:57:31
 */

namespace app\api\controller;

use app\admin\model\ActivityModel;
use app\admin\model\DiscussionModel;
use app\admin\model\OptionModel;
use app\admin\model\PerformModel;
use app\admin\model\PortalCategoryModel;
use app\admin\model\PortalPostModel;
use app\admin\model\RoomModel;
use think\captcha\Captcha;
use think\Db;

class SiteController extends Base
{

    /**
     * 查询
     * @author Tony Gu
     * @param string $kv 关键词
     * @param integer $page 页数
     * @param integer $len 数量
     * @return [type] [description]
     */
    public function search()
    {
        $keyword = input('param.kv', '', 'trim');
        $page = input('param.page', 1, 'intval');
        $len = input('param.len', 10, 'intval');
        $client_type = input('param.client_type', 0, 'intval');
        $articleSql = "SELECT id,CONCAT('/portal/category/read/?id=',id) AS pc_url,CONCAT('/wx/category/read/?id=',id) AS mb_url,'article' AS source,post_title AS title,post_excerpt AS abstract,more AS thumb,create_time AS time FROM cxtj_portal_post where post_status=1 and delete_time=0 and link=''";
        $videoSql = "SELECT id, CONCAT('/portal/activity/read?id=', id) AS pc_url, CONCAT('/wx/activity/read?id=', id) AS mb_url, '培训预约' AS source, title, abstract, thumb, published_time as time FROM cxtj_activity where status=1 and delete_time=0";
        $journalSql = "SELECT id, url as pc_url, type AS mb_url, 'diandan' AS source, title, '' AS abstract, thumb, create_time AS time FROM cxtj_perform where status=1 and delete_time=0";
        $offset = ($page - 1) * $len;
        if (strlen($keyword) > 0) {
            $count = Db::query("select count(1)as num from ({$articleSql} union all ({$videoSql}) union all ({$journalSql}))a where a.title like :kv ", ['kv' => "%{$keyword}%"])[0]['num'];
            $all = Db::query("select * from ({$articleSql} union all ({$videoSql}) union all ({$journalSql}))a where a.title like :kv order by a.time desc limit :offset, :len", ['kv' => "%{$keyword}%", 'offset' => $offset, 'len' => $len]);
            $portalCategoryModel = new PortalCategoryModel();
            $mukeIds = $portalCategoryModel->getTreeIds(19);
            $infoIds = $portalCategoryModel->getTreeIds(8);
            $portalCategoryPost = db('portal_category_post');
            foreach ($all as $id => &$value) {
                //图片处理
                $thumb = json_decode($value['thumb'], true);
                if (is_array($thumb) && isset($thumb['thumbnail'])) {
                    $value['thumb'] = $thumb['thumbnail'];
                }
                $value['thumb'] = cmf_get_image_preview_url($value['thumb']);
                //文章处理
                if ($value['source'] == 'article') {
                    $value['source'] = '文章';
                    $value['mb_url'] = '/wx/category/read/?id=' . $value['id'];
//                    $category_id = $portalCategoryPost->where(['post_id'=>$value['id']])->value('category_id', null);
//                    if ($category_id != null) {
//                        if (in_array($category_id, $mukeIds)) {
//                            $value['source'] = '慕课平台';
//                        } elseif (in_array($category_id, $infoIds)) {
//                            $value['source'] = '信息展示';
//                        } else {
////                            echo 1;
//                            unset($all[$id]);
//                            $count--;
//                            continue;
//                        }
//                        $value['mb_url'] = '/wx/category/read/?id=' . $value['id'];
//                    } else {
//                        unset($all[$id]);
////                        echo 2;
//                        $count--;
//                    }
                } elseif ($value['source'] == 'diandan') {
                    //perform处理
                    if ($value['mb_url'] == 1) {
                        $value['pc_url'] = '/portal/culture/read/?id=' . $value['id'];
                        $value['source'] = '文化点单';
                    } else {
                        $value['source'] = '资源库';
                    }
//                    } else {
//                        unset($all[$id]);
////                        echo 3;
//                        $count--;
//                        continue;
//                    }
                    $value['mb_url'] = '/wx/category/read/?id=' . $value['id'];
                } elseif ($value['source'] == '培训预约') {
                    $value['mb_url'] = '/wx/activity/read?id=' . $value['id'];
                }

            }
            if (!empty($all)) {
                return $this->output_success(19101, ['list' => array_values($all), 'num' => $count], '搜索成功');
            }
        }
        return $this->output_error(19001, '查询结果为空');
    }


//    五个资源库的搜索
    public function z_search()
    {
        $keyword = input('param.kv', '', 'trim');
        $page = input('param.page', 1, 'intval');
        $len = input('param.len', 10, 'intval');
        $type = input('param.type',0,'intval');
        $offset = ($page - 1) * $len;
        $post = new PortalPostModel();
        if (strlen($keyword) > 0) {
            if ($type==0){
                $all = $post->alias('p')
                    ->join('portal_category_post c','c.post_id=p.id')
                    ->where(['p.post_status'=>1,'p.delete_time'=>0])
                    ->where('p.post_title','like',"%$keyword%")
                    ->where('c.category_id','between','177,186')
                    ->field('p.post_excerpt as abstract,p.id,CONCAT(\'/portal1/category/read/?id=\',p.id) as pc_url,\'文章\' as source,p.more as thumb,p.published_time as time,p.post_title as title')
                    ->order('p.published_time')
                    ->select()
                    ->toArray();
                $count = $post->alias('p')
                    ->join('portal_category_post c','c.post_id=p.id')
                    ->where(['p.post_status'=>1,'p.delete_time'=>0])
                    ->where('p.post_title','like',"%$keyword%")
                    ->where('c.category_id','between','177,186')
                    ->field('p.post_excerpt as abstract,p.id,CONCAT(\'/portal1/category/read/?id=\',p.id) as pc_url,\'文章\' as source,p.more as thumb,p.published_time as time,p.post_title as title')
                    ->order('p.published_time')
                    ->count();
            }elseif ($type==1){
                $all = $post->alias('p')
                    ->join('portal_category_post c','c.post_id=p.id')
                    ->where(['p.post_status'=>1,'p.delete_time'=>0])
                    ->where('p.post_title','like',"%$keyword%")
                    ->where('c.category_id','between','187,194')
                    ->field('p.post_excerpt as abstract,p.id,CONCAT(\'/portal2/category/read/?id=\',p.id) as pc_url,\'文章\' as source,p.more as thumb,p.published_time as time,p.post_title as title')
                    ->order('p.published_time')
                    ->select()
                    ->toArray();
                $count = $post->alias('p')
                    ->join('portal_category_post c','c.post_id=p.id')
                    ->where(['p.post_status'=>1,'p.delete_time'=>0])
                    ->where('p.post_title','like',"%$keyword%")
                    ->where('c.category_id','between','187,194')
                    ->field('p.post_excerpt as abstract,p.id,CONCAT(\'/portal2/category/read/?id=\',p.id) as pc_url,\'文章\' as source,p.more as thumb,p.published_time as time,p.post_title as title')
                    ->order('p.published_time')
                    ->count();
            }elseif ($type==2){
                $all = $post->alias('p')
                    ->join('portal_category_post c','c.post_id=p.id')
                    ->where(['p.post_status'=>1,'p.delete_time'=>0])
                    ->where('p.post_title','like',"%$keyword%")
                    ->where('c.category_id','between','195,198')
                    ->field('p.post_excerpt as abstract,p.id,CONCAT(\'/portal2/category/read/?id=\',p.id) as pc_url,\'文章\' as source,p.more as thumb,p.published_time as time,p.post_title as title')
                    ->order('p.published_time')
                    ->select()
                    ->toArray();
                $count = $post->alias('p')
                    ->join('portal_category_post c','c.post_id=p.id')
                    ->where(['p.post_status'=>1,'p.delete_time'=>0])
                    ->where('p.post_title','like',"%$keyword%")
                    ->where('c.category_id','between','195,198')
                    ->field('p.post_excerpt as abstract,p.id,CONCAT(\'/portal2/category/read/?id=\',p.id) as pc_url,\'文章\' as source,p.more as thumb,p.published_time as time,p.post_title as title')
                    ->order('p.published_time')
                    ->count();
            }


            foreach ($all as $id => &$value) {
                //图片处理
                $thumb = json_decode($value['thumb'], true);
                if (is_array($thumb) && isset($thumb['thumbnail'])) {
                    $value['thumb'] = $thumb['thumbnail'];
                }
                $value['thumb'] = cmf_get_image_preview_url($value['thumb']);
                //文章处理
                if ($value['source'] == 'article') {
                    $value['source'] = '文章';
                    $value['mb_url'] = '/wx/category/read/?id=' . $value['id'];

                } elseif ($value['source'] == 'diandan') {
                    //perform处理
                    if ($value['mb_url'] == 1) {
                        $value['pc_url'] = '/portal/culture/read/?id=' . $value['id'];
                        $value['source'] = '文化点单';
                    } else {
                        $value['source'] = '资源库';
                    }

                    $value['mb_url'] = '/wx/category/read/?id=' . $value['id'];
                } elseif ($value['source'] == '培训预约') {
                    $value['mb_url'] = '/wx/activity/read?id=' . $value['id'];
                }

            }
            if (!empty($all)) {
                return $this->output_success(19101, ['list' => array_values($all), 'num' => $count], '搜索成功');
            }
        }
        return $this->output_error(19001, '查询结果为空');
    }

    function checkImg()
    {
        $code = input('param.code');
        $captcha = new Captcha();
        if ($captcha->check($code)) {
            return $this->output_success(20101, [], '验证码正确');
        } else {
            return $this->output_error(20001, '验证码错误');
        }
    }

    public function tongji(){
        $dataList = [];
        //信息资讯
        $info = $this->getArticleCount(8);
        $this->sum_venue($dataList, $info, 'info');
        //资源展示
        $resource = $this->getArticleCount(12);
        $this->sum_venue($dataList, $resource, 'resource');
        //活动报名
        $activity = ActivityModel::where(['venue'=>['neq', 0]])->field('venue,sum(page_view) as num')->group('venue')->select()->toArray();
        $this->sum_venue($dataList, $activity, 'activity');
        //节目点单
        $culture  = PerformModel::where(['type'=>1, 'venue'=>['neq', 0]])->field('venue,sum(page_view) as num')->group('venue')->select()->toArray();
        $this->sum_venue($dataList, $culture, 'culture');
        //民意征集
        $opinion = DiscussionModel::where(['venue_id'=>['neq', 0]])->field('venue_id as venue,sum(views_count) as num')->group('venue')->select()->toArray();
        $this->sum_venue($dataList, $opinion, 'opinion');
        //场馆预约
        $room = RoomModel::where(['venue'=>['neq', 0]])->field('venue,sum(page_view) as num')->group('venue')->select();
        $this->sum_venue($dataList, $room, 'room');
        $model = db('venue_tongji');
        $venueIdArr = $model->column('venue_id');
        foreach ($dataList as $venue_id => $value){
            if(in_array($venue_id, $venueIdArr)){
                unset($value['create_time']);
                $model->where('venue_id', $venue_id)->update($value);
            }else{
                $model->where('venue_id', $venue_id)->insert($value);
            }
        }
    }

    private function sum_venue(&$res, $arr, $key){
        foreach ($arr as $k => $v){
            if(isset($res[$v['venue']])){
                $venue_item = $res[$v['venue']];
                $value = isset($venue_item[$key]) ? $venue_item[$key] : 0;
                $res[$v['venue']][$key] = $v['num'] +$value;
                $res[$v['venue']]['hot'] = $res[$v['venue']]['hot'] + $v['num'];
            }else{
                $res[$v['venue']][$key] = $v['num'];
                $res[$v['venue']]['hot'] = $v['num'];
                $res[$v['venue']]['venue_id'] = $v['venue'];
                $res[$v['venue']]['create_time'] = time();
                $res[$v['venue']]['update_time'] = time();
            }
        }
    }
    private function getArticleCount($cid){
        $ids = (new PortalCategoryModel)->getTreeIds($cid);
        $ids[] = $cid;
        $portalPostModel = new PortalPostModel();
        return $portalPostModel->alias('a')
            ->join('__PORTAL_CATEGORY_POST__ p', 'a.id=p.post_id')
            ->where(['a.venue'=>['neq', 0], 'p.category_id'=>['in', $ids]])
            ->field('venue,sum(post_hits) as num')
            ->group('venue')
            ->select()
            ->toArray();
    }
}