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
namespace app\admin\service;

use app\admin\model\PortalCategoryModel;
use app\admin\model\PortalPostModel;
use app\admin\model\UserModel;

class PostService
{

    public function adminArticleList($filter, $haveChild = false,$all_status)
    {
        return $this->adminPostList($filter, false, $haveChild,$all_status);
    }

    public function ArticleList($filter, $haveChild = false,$all_status)
    {
        return $this->PostList($filter, false, $haveChild,$all_status);
    }



    public function adminPageList($filter)
    {
        return $this->adminPostList($filter, true);
    }

    public function adminPostList($filter, $isPage = false, $haveChild = false,$all_status)
    {

        $where = [
            'a.create_time' => ['>=', 0],
            'a.delete_time' => 0
        ];

        $join = [
            ['__USER__ u', 'a.user_id = u.id']
        ];

        $field = 'a.*,u.user_login,u.user_nickname,u.user_email';

        $category = empty($filter['category']) ? 0 : intval($filter['category']);
        if (!empty($category)) {
            if ($haveChild == true) {
                $portalCategoryModel = new PortalCategoryModel();
                $categoryArr = $portalCategoryModel->getTreeIds($category);
                array_unshift($categoryArr, $category);
                $where['b.category_id'] = ['in', $categoryArr];
            } else {
                $where['b.category_id'] = $category;
            }
        }
        array_push($join, [
            '__PORTAL_CATEGORY_POST__ b', 'a.id = b.post_id'
        ]);
        array_push($join, [
            '__VENUE__ v', 'v.id = a.venue'
        ]);
        $field = 'a.*,b.id AS post_category_id,b.list_order,b.category_id,u.user_login,u.user_nickname,u.user_email,v.name as venue_name,v.status as vstatus';
        if (isset($filter['venue'])) {
            if ($filter['venue'] == 0) {
                $where['a.venue'] = ['in', UserModel::getCurrentVenue2()];
            } else {
                $where['a.venue'] = $filter['venue'];
            }
        }

        $startTime = empty($filter['start_time']) ? 0 : strtotime($filter['start_time']);
        $endTime = empty($filter['end_time']) ? 0 : strtotime($filter['end_time']);
        if (!empty($startTime) && !empty($endTime)) {
            $where['a.published_time'] = [ ['>= time', $startTime], ['<= time', $endTime]];
        } else {
            if (!empty($startTime)) {
                $where['a.published_time'] = ['>= time', $startTime];
            }
            if (!empty($endTime)) {
                $where['a.published_time'] = ['<= time', $endTime];
            }
        }
        if ($all_status==1){
            $where['v.status'] =1;
        }elseif ($all_status ==2){
            $where['v.status'] =0;
        }
        $keyword = empty($filter['keyword']) ? '' : $filter['keyword'];
        if (!empty($keyword)) {
            $where['a.post_title'] = ['like', "%$keyword%"];
        }

        if ($isPage) {
            $where['a.post_type'] = 2;
        } else {
            $where['a.post_type'] = 1;
        }
        $portalPostModel = new PortalPostModel();
        $articles = $portalPostModel->alias('a')->field($field)
            ->join($join)
            ->where($where)
            ->order(['is_top' => 'DESC', 'published_time' => 'DESC'])
            ->group('a.id')
            ->paginate(10);
//        echo $portalPostModel->getLastSql();die;
        return $articles;
    }

    public function PostList($filter, $isPage = false, $haveChild = false,$all_status)
    {
        $where = [];
        $whereor ='';
        $join = [
            ['__USER__ u', 'a.user_id = u.id','left']
        ];


        $category = empty($filter['category']) ? 0 : intval($filter['category']);

        if(!empty($filter['category'])){
                $portalCategoryModel = new PortalCategoryModel();
                $categoryArr = $portalCategoryModel->getTreeIds($category);
                array_unshift($categoryArr, $category);
                $where['b.category_id'] = ['in', $categoryArr];
        }

        array_push($join, [
            '__PORTAL_CATEGORY_POST__ b', 'a.id = b.post_id'
        ]);
        array_push($join, [
            '__VENUE__ v', 'v.id = a.venue','left'
        ]);
        if ($all_status==1){
            $where['v.status']=1;

        }elseif ($all_status ==2){
            $where['v.status']=0;
        }
        $field = 'a.*,b.id AS post_category_id,b.list_order,b.category_id,u.user_login,u.user_nickname,u.user_email,v.name as venue_name,v.status as vstatus';

        if (isset($filter['venue'])) {
            if ($filter['venue'] == 0) {
                $where['a.venue'] = ['in', UserModel::getCurrentVenue2()];
            } else {
                $where['a.venue'] = $filter['venue'];
            }
        }

        $startTime = empty($filter['start_time']) ? 0 : strtotime($filter['start_time']);
        $endTime = empty($filter['end_time']) ? 0 : strtotime($filter['end_time']);
        if (!empty($startTime) && !empty($endTime)) {
            $where['a.published_time'] = [ ['>= time', $startTime], ['<= time', $endTime]];
        } else {
            if (!empty($startTime)) {
                $where['a.published_time'] = ['>= time', $startTime];
            }
            if (!empty($endTime)) {
                $where['a.published_time'] = ['<= time', $endTime];
            }
        }

        $keyword = empty($filter['keyword']) ? '' : $filter['keyword'];
        if (!empty($keyword)) {
            $where['a.post_title'] = ['like', "%$keyword%"];
        }

        if ($isPage) {
            $where['a.post_type'] = 2;
        } else {
            $where['a.post_type'] = 1;
        }

        $portalPostModel = new PortalPostModel();
        $articles = $portalPostModel->alias('a')
            ->field($field)
            ->join($join)
            ->where($where)
            ->order(['is_top' => 'DESC', 'update_time' => 'DESC'])
            ->group('a.id')
            ->paginate(10);

        return $articles;
    }

    public function publishedArticle($postId, $categoryId = 0)
    {
        $portalPostModel = new PortalPostModel();

        if (empty($categoryId)) {

            $where = [
                'post.post_type' => 1,
                'post.published_time' => [['< time', time()], ['> time', 0]],
                'post.post_status' => 1,
                'post.delete_time' => 0,
                'post.id' => $postId
            ];

            $article = $portalPostModel->alias('post')->field('post.*')
                ->where($where)
                ->find();
        } else {
            $where = [
                'post.post_type' => 1,
                'post.published_time' => [['< time', time()], ['> time', 0]],
                'post.post_status' => 1,
                'post.delete_time' => 0,
                'relation.category_id' => $categoryId,
                'relation.post_id' => $postId
            ];

            $join = [
                ['__PORTAL_CATEGORY_POST__ relation', 'post.id = relation.post_id']
            ];
            $article = $portalPostModel->alias('post')->field('post.*')
                ->join($join)
                ->where($where)
                ->find();
        }


        return $article;
    }

    //上一篇文章
    public function publishedPrevArticle($postId, $categoryId = 0)
    {
        $portalPostModel = new PortalPostModel();

        if (empty($categoryId)) {

            $where = [
                'post.post_type' => 1,
                'post.published_time' => [['< time', time()], ['> time', 0]],
                'post.post_status' => 1,
                'post.delete_time' => 0,
                'post.id ' => ['<', $postId]
            ];

            $article = $portalPostModel->alias('post')->field('post.*')
                ->where($where)
                ->order('id', 'DESC')
                ->find();

        } else {
            $where = [
                'post.post_type' => 1,
                'post.published_time' => [['< time', time()], ['> time', 0]],
                'post.post_status' => 1,
                'post.delete_time' => 0,
                'relation.category_id' => $categoryId,
                'relation.post_id' => ['<', $postId]
            ];

            $join = [
                ['__PORTAL_CATEGORY_POST__ relation', 'post.id = relation.post_id']
            ];
            $article = $portalPostModel->alias('post')->field('post.*')
                ->join($join)
                ->where($where)
                ->order('id', 'DESC')
                ->find();
        }


        return $article;
    }

    //下一篇文章
    public function publishedNextArticle($postId, $categoryId = 0)
    {
        $portalPostModel = new PortalPostModel();

        if (empty($categoryId)) {

            $where = [
                'post.post_type' => 1,
                'post.published_time' => [['< time', time()], ['> time', 0]],
                'post.post_status' => 1,
                'post.delete_time' => 0,
                'post.id' => ['>', $postId]
            ];

            $article = $portalPostModel->alias('post')->field('post.*')
                ->where($where)
                ->order('id', 'ASC')
                ->find();
        } else {
            $where = [
                'post.post_type' => 1,
                'post.published_time' => [['< time', time()], ['> time', 0]],
                'post.post_status' => 1,
                'post.delete_time' => 0,
                'relation.category_id' => $categoryId,
                'relation.post_id' => ['>', $postId]
            ];

            $join = [
                ['__PORTAL_CATEGORY_POST__ relation', 'post.id = relation.post_id']
            ];
            $article = $portalPostModel->alias('post')->field('post.*')
                ->join($join)
                ->where($where)
                ->order('id', 'ASC')
                ->find();
        }


        return $article;
    }

    public function publishedPage($pageId)
    {

        $where = [
            'post_type' => 2,
            'published_time' => [['< time', time()], ['> time', 0]],
            'post_status' => 1,
            'delete_time' => 0,
            'id' => $pageId
        ];

        $portalPostModel = new PortalPostModel();
        $page = $portalPostModel
            ->where($where)
            ->find();

        return $page;
    }

}