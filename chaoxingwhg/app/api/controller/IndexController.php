<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/1
 * Time: 14:40
 */

namespace app\api\controller;

use app\admin\model\ActivityModel;
use app\admin\model\BannerModel;
use app\admin\model\LiveBroadcastModel;
use app\admin\model\PerformModel;
use app\admin\model\PortalCategoryModel;
use app\portal\model\PortalPostModel;
use think\Db;

class IndexController extends Base
{
    public function getIndexData()
    {
        $data['banner'] = $this->getBanner();
        $data['article'] = $this->getArticle(161 , 4);
        $data['culture'] = $this->getCulture();
        $data['activity'] = $this->getActivity();
        $data['live'] = $this->getLive();
        $data['muke'] = $this->getArticle(19, 3);
        Db::table('cxtj_baseinfo')->where('id', 0)->setInc('wxaccess_count');
        Db::table('cxtj_baseinfo')->where('id', 0)->setInc('wxtrue_count');
        return $this->output_success('200', $data);

    }

    /**
     * 获取banner
     *
     * @return array
     */
    private function getBanner()
    {
        $indexResult = action('api/Banner/index', ['id' => 5]);
        if (isset($indexResult['status']) && $indexResult['status'] == 1) {
            return $indexResult['data']->toArray();
        } else {
            return [];
        }
    }

    /**
     * 获取文章列表
     *
     * @return array
     */
    private function getArticle($cid , $limit = 3)
    {
        $childIds = (new PortalCategoryModel())->getTreeIds($cid);
        array_unshift($childIds, $cid);

        $order = ['a.is_top' => 'desc', 'a.published_time' => 'desc'];
        $where = [
            'a.delete_time' => 0,
            'c.category_id' => ['in', $childIds]
        ];
        $portalPostModel = new PortalPostModel();
        $posts = $portalPostModel
            ->alias('a')
            ->join('__PORTAL_CATEGORY_POST__ c', 'c.post_id=a.id')
            ->join('__VENUE__ v', 'v.id=a.venue')
            ->field('a.id,a.post_title as title,a.link,a.published_time as time,a.more,a.is_top,a.post_hits as hits,a.post_like as likes,a.post_excerpt as abstract, v.name as source ,a.published_time')
            ->order($order)
            ->where($where)
            ->group('a.id')
            ->page(0, $limit)
            ->select();

        return $posts->isEmpty() ? [] : $posts->each(function ($item, $key) {
            $thumbInDb = isset($item['more']['thumbnail']) ? $item['more']['thumbnail'] : '';
            $item['thumb'] = $thumbInDb == '' ? '' : cmf_get_image_preview_url($thumbInDb);
            unset($item['more']);
        })->toArray();
    }

    /**
     * 获取文化点单列表
     *
     * @return array
     */
    private function getCulture()
    {
        $where = ['delete_time' => 0, 'type' => 1];
        $order['p.create_time'] = 'desc';
        $performModel = new PerformModel();
        $cultures = $performModel
            ->alias('p')
            ->where($where)
            ->join('venue v', 'v.id = p.venue')
            ->order($order)
            ->field('p.title,p.org,p.thumb,from_unixtime(p.create_time,\'%Y-%m-%d\') as acreate_time,p.id,v.name as vname')
            ->page(0, 8)
            ->select();

        if ($cultures->isEmpty())
            return [];
        else
            return $cultures->each(function ($item, $key) {
                $item['thumb'] = cmf_get_image_preview_url($item['thumb']);
            })->toArray();
    }

    /**
     * 获取培训预约列表
     *
     * @return array
     */
    private function getActivity()
    {
        //排序方式
        // $order = input('order','published_time');
        $order['published_time'] = 'desc';
        $where['a.status'] = 1;
        $where['volun_type'] = 0;
        $where['a.delete_time'] = 0;
        $where['v.id']=['gt',0];
        $activityModel = new ActivityModel();

        $activity_list = $activityModel->alias('a')
            ->join('venue v', 'a.venue =v.id', 'left')
            ->join('activity_type at', 'a.type = at.id', 'left')
            ->where($where)
            ->field("a.*,v.name venue_name,from_unixtime(a.start_time,'%Y-%m-%d %h:%i') as format_start_time,from_unixtime(a.end_time,'%Y-%m-%d %h:%i') as format_end_time")
            ->order($order)
            ->page(0, 4)
            ->select();

        if ($activity_list->isEmpty())
            return [];

        return $activity_list->each(function ($item, $key) {
            $item['thumb'] = cmf_get_image_preview_url($item['thumb']);
        })->toArray();
    }

    private function getLive()
    {
        $where = '';
        $roomModel = new LiveBroadcastModel();
        $rooms = $roomModel
            ->alias('r')
            ->join('__VENUE__ v', 'r.venueid = v.id')
            ->where($where)
            ->order('r.id desc')
            ->page(0, 3)
            ->select();

        if ($rooms->isEmpty())
            return [];

        return $rooms->each(function ($item, $key) {
            $item['img'] = cmf_get_image_preview_url($item['img']);

            $item['liveingStatus'] = 0;

            if(time() >= $item['start_time'] && time() <= $item['end_time']) {
                $item['liveingStatus'] = 1; //正在直播
            }

            if(time() >= $item['start_time'] && time() >= $item['end_time']) {
                $item['liveingStatus'] = 2; //结束
            }
            if(time() <= $item['start_time'] && time() <= $item['end_time']) {
                $item['liveingStatus'] = 3; //未开始
            }
        })->toArray();
    }
    public function text(){

        return $this->output_success('200', $data);
    }

}