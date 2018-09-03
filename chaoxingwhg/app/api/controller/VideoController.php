<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/5
 * Time: 16:57
 */

namespace app\api\controller;


use app\admin\model\AreaModel;
use app\admin\model\CollectModel;
use app\admin\model\LikeModel;
use app\admin\model\PortalCategoryModel;
use app\admin\model\PortalPostModel;
use app\admin\model\RoomApplyModel;
use app\admin\model\RoomModel;

class VideoController extends Base
{

    public function index()
    {
        $sort = input('param.sort', '');
        $cid = input('param.cid', 0);
        $vid = input('param.venue', 0);
        $aid = input('param.area', 0);
        $page = input('param.page', 1, 'intval');
        $len = input('param.len', 10, 'intval');
        $field = 'a.id,a.post_title as title,a.published_time as time,a.more,a.is_top,a.post_hits as hits,a.link,a.post_like as likes,a.post_excerpt as abstract, a.post_source as source';
        $childIds = (new PortalCategoryModel())->getTreeIds($cid);
        $childIds[] = $cid;
        $join = [['__PORTAL_CATEGORY_POST__ c', 'c.post_id=a.id']];
        $where = [
            'a.delete_time' => 0,
            'c.category_id' => ['in', $childIds],
            'a.post_status' => 1
        ];
        $order = ['a.is_top' => 'desc'];
        if ($sort == 'hot') {
            $order['a.post_hits'] = 'desc';
        } else {
            $order['a.published_time'] = 'desc';
        }
        if ($vid != -1) {
            if ($vid != 0) {
                $where['v.id'] = $vid;
            }
            $field .= ',v.name as venue_name';
            $join[] = ['__VENUE__ v', 'v.id=a.venue'];
        }
        if ($aid != 0) {
            $aids = (new AreaModel())->getTreeIds($aid);
            $aids[] = $aid;
            $where['v.address'] = ['in', $aids];
        }
        $portalPostModel = new PortalPostModel();
        $posts = $portalPostModel
            ->alias('a')
            ->join($join)
            ->field($field)
            ->where($where)
            ->order($order)
            ->group('a.id')
            ->page($page, $len)
            ->select();
        $num = $portalPostModel
            ->alias('a')
            ->join($join)
            ->where($where)
            ->group('a.id')
            ->count('a.id');
        foreach ($posts as &$v) {
            $thumbInDb = isset($v->more['thumbnail']) ? $v->more['thumbnail'] : '';
            $v['thumb'] = $thumbInDb == '' ? '' : cmf_get_image_preview_url($thumbInDb);
            unset($v['more']);
            $v['is_like'] = LikeModel::have($v['id'], 1);
            $v['is_collect'] = CollectModel::have($v['id']);
        }
        return $this->output_success('13101', ['list' => $posts, 'num' => $num], '文章获取成功');
    }

    function collect_list()
    {
        $uid = $this->getuid();
        $page = input('param.page', 1, 'intval');
        $len = input('param.len', 10, 'intval');
        $portalPostModel = new PortalPostModel();
        $articleIds = CollectModel::where(['user_id' => $uid])->order(['create_time' => 'desc'])->column(['article_id']);
        if (!empty($articleIds)) {
            $list = $portalPostModel
                ->alias('a')
                ->join('__VENUE__ v', 'a.venue = v.id')
                ->where(['delete_time' => 0, 'a.id' => ['in', $articleIds], 'a.post_status' => 1])
                ->field('a.id,post_title,v.name as venue_name,post_collect as collect_num,a.more,a.published_time')
                ->page($page, $len)
                ->select();
            $count = $portalPostModel->where(['delete_time' => 0, 'id' => ['in', $articleIds]])->count(1);
            foreach ($list as &$v) {
                $thumb = $v->more;
                if (is_array($thumb) && isset($thumb['thumbnail'])) {
                    $v['thumb'] = cmf_get_image_preview_url($thumb['thumbnail']);
                } else {
                    $v['thumb'] = '';
                }
            }
            return $this->output_success(13108, ['list' => $list, 'count' => $count], '获取收藏列表成功');
        } else {
            return $this->output_error(13008, '获取收藏列表失败');
        }
    }

    public function read()
    {
        $id = input('param.id', 0);
        $portalPostModel = new PortalPostModel();
        $post = $portalPostModel
            ->alias('a')
            ->join('__PORTAL_CATEGORY_POST__ c', 'c.post_id=a.id')
            ->join('__VENUE__ v', 'v.id=a.venue', 'left')
            ->field('a.id,a.post_title as title,a.post_collect as collect,v.name as venue_name,a.comment_status,a.comment_count,c.category_id as cid,a.published_time as time,a.more,a.post_excerpt as abstract, a.post_source as source, a.post_content as content, a.is_top,a.post_hits as hits, a.post_like as likes')
            ->where('a.id', $id)
            ->find();
        if ($post == null) {
            return $this->output_error('13001', '文章获取失败');
        } else {
            $files = isset($post->more['files']) ? $post->more['files'] : '';
            $thumb = isset($post->more['thumbnail']) ? $post->more['thumbnail'] : '';

            $post->files = $files == '' ? '' : cmf_get_image_preview_url($files);
            $post->thumb = $thumb == '' ? '' : cmf_get_image_preview_url($thumb);

            $portalPostModel->where('id', $id)->setInc('post_hits');
            $post->top_cid = (new PortalCategoryModel())->getTopPid($post->cid);
            $post->is_like = LikeModel::have($post->id, 1);
            $post->is_collect = CollectModel::have($post->id);
            $portalCatetoryModel = new PortalCategoryModel();
            $infoIds = $portalCatetoryModel->getTreeIds(8);
            $resourceIds = $portalCatetoryModel->getTreeIds(12);
            $ids = array_flip($infoIds) + array_flip($resourceIds);
            $post->categorys = (new PortalCategoryModel())
                ->alias('c')
                ->join('__PORTAL_CATEGORY_POST__ p', 'c.id = p.category_id')
                ->where(['p.post_id' => $id, 'p.status' => '1', 'p.category_id' => ['in', array_keys($ids)]])
                ->field('c.id,c.name')
                ->select();
            return $this->output_success('13101', $post, '文章获取成功');
        }
    }

    public function like()
    {
        $data['user_id'] = $this->getuid();
        $data['resource_id'] = input('param.id', 0, 'intval');
        $data['type'] = 1;
        $id = db('like')->where($data)->value('id');
        if ($id > 0) {
            db('like')->where('id', $id)->delete();
            return $this->output_success('13101', [], '取消赞成功');
        } else {
            $data['create_time'] = time();
            db('like')->insert($data);
            return $this->output_success('13101', [], '点赞成功');
        }
    }

    public function collect()
    {
        $data['user_id'] = $this->getuid();
        $data['article_id'] = input('param.id', 0, 'intval');
        $id = db('collect')->where($data)->value('id');
        if ($id > 0) {
            db('collect')->where('id', $id)->delete();
            $post_collect = PortalPostModel::where('id', $data['article_id'])->value('post_collect', 0);
            if ($post_collect > 0) {
                PortalPostModel::where('id', $data['article_id'])->setDec('post_collect');
            }
            return $this->output_success('13101', [], '取消收藏成功');
        } else {
            $data['create_time'] = time();
            db('collect')->insert($data);
            PortalPostModel::where('id', $data['article_id'])->setInc('post_collect');
            return $this->output_success('13102', [], '收藏成功');
        }
    }
}