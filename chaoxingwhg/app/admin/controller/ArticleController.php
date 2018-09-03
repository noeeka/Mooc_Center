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
namespace app\admin\controller;

use app\admin\model\AreaModel;
use app\admin\model\UserModel;
use app\admin\model\VenueModel;
use cmf\controller\AdminBaseController;
use app\admin\model\PortalPostModel;
use app\admin\service\PostService;
use app\admin\model\PortalCategoryModel;
use think\Db;
use app\admin\model\ThemeModel;

class ArticleController extends AdminBaseController
{
    private $allCid = 49;
    /**
     * 文章列表
     * @adminMenu(
     *     'name'   => '文章管理',
     *     'parent' => 'portal/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章列表',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $param = $this->request->param();
        $all_status = $this->request->param('all_status', 0, 'intval');


        $area_id=!empty($param['area_id'])?$param['area_id']:0;
        $venue=!empty($param['venue'])?$param['venue']:0;
        $id = $param['id'];
        $this->allCid = $id;

        if($id == 0){
            return $this->article_lib($param,0);
        }

        $thisNode = PortalCategoryModel::get($id);
        if($thisNode == null || !$thisNode->isArticle()){
            $this->error('文章分类不存在或非文章类栏目');
        }
        $categoryId = $this->request->param('category', 0,'intval');
        $postService = new PostService();

        $portalCategoryModel = new PortalCategoryModel();
        $haveChild = false;
        if(!isset($param['category']) || $param['category'] == 0 || $param['category'] == $id){
            $param['category'] = $id;
            $haveChild = true;
        }
        if(!empty($param['venue'])){
            $param['venue'] = $venue;
        }/*else{
            $param['venue'] = ['in', UserModel::getCurrentVenue()];
        }*/

       

        $data = $postService->adminArticleList($param, $haveChild,$all_status);

        $data->appends($param);

        //区域列表
        $areaModel = new AreaModel();
        $areas = $areaModel->adminAreaTree($area_id);

        //文化馆列表
        $venueModel = new VenueModel();
        $venues = $venueModel->where(['id' => ['in', UserModel::getCurrentVenue2()]])->order(['list_order' => 'asc'])->select()->toArray();
//        var_dump($venues);die;
        $venue_id = isset($param['venue'])?$param['venue']:0;
        $myv = '';
        foreach ($venues as $v) {
            $select = $v['id'] ==$venue_id ? 'selected' : '';
            $myv .= "<option value='" . $v['id'] . "' " . $select . ">" . $v['name'] . "</option>";
        }

        $categoryTree = $portalCategoryModel->adminCategoryTree($categoryId, 0, $id);
        $categoryTree = '<option value="' . $id . '">' . $thisNode->name . '</option>'. $categoryTree;
        $this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('all_status',$all_status);
        $this->assign('articles', $data->items());

        $this->assign('category_tree', $categoryTree);
        $this->assign('category', $categoryId);
        $this->assign('page', $data->render());
        $this->assign('id', $id);
        $this->assign('areas', $areas);

        $this->assign('area_id',isset($param['area_id'])?$param['area_id']:0);
        $this->assign('venues', json_encode($venues));
        $this->assign('venue',isset($param['venue'])?$param['venue']:0);
        $this->assign('my_venue', $myv);

        return $this->fetch();

    }

    /**
     * 添加文章
     * @adminMenu(
     *     'name'   => '添加文章',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加文章',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        $cid = $this->request->param('cid',0, 'intval');
        $themeModel        = new ThemeModel();
        $articleThemeFiles = $themeModel->getActionThemeFiles('portal/Article/index');
        $areaModel = new AreaModel();
        $areas = $areaModel->adminAreaTree();
        $venueModel = new VenueModel();
        $venues = $venueModel->where(['status'=>1, 'id'=>['in', UserModel::getCurrentVenue()]])->order(['list_order'=>'asc'])->select()->toArray();


        $this->assign('areas', $areas);
        $this->assign('venues', json_encode($venues));
        $this->assign('article_theme_files', $articleThemeFiles);
        $this->assign('cid', $cid);
        return $this->fetch();
    }

    /**
     * 添加文章提交
     * @adminMenu(
     *     'name'   => '添加文章提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加文章提交',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            $data   = $this->request->param();
            $post   = $data['post'];
            $result = $this->validate($post, 'AdminArticle');
            if ($result !== true) {
                $this->error($result);
            }

            $portalPostModel = new PortalPostModel();

            if (!empty($data['photo_names']) && !empty($data['photo_urls'])) {
                $data['post']['more']['photos'] = [];
                foreach ($data['photo_urls'] as $key => $url) {
                    $photoUrl = cmf_asset_relative_url($url);
                    array_push($data['post']['more']['photos'], ["url" => $photoUrl, "name" => $data['photo_names'][$key]]);
                }
            }

            if (!empty($data['file_names']) && !empty($data['file_urls'])) {
                $data['post']['more']['files'] = [];
                foreach ($data['file_urls'] as $key => $url) {
                    $fileUrl = cmf_asset_relative_url($url);
                    array_push($data['post']['more']['files'], ["url" => $fileUrl, "name" => $data['file_names'][$key]]);
                }
            }

            $portalPostModel->adminAddArticle($data['post'], $data['post']['categories']);

            $data['post']['id'] = $portalPostModel->id;
            $hookParam          = [
                'is_add'  => true,
                'article' => $data['post']
            ];
            hook('portal_admin_after_save_article', $hookParam);


            $this->success('添加成功!', url('Article/edit', ['id' => $portalPostModel->id, 'cid'=>$post['cid']]));
        }

    }

    /**
     * 编辑文章
     * @adminMenu(
     *     'name'   => '编辑文章',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑文章',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $cid = $this->request->param('cid',0, 'intval');
        $id = $this->request->param('id', 0, 'intval');

        $portalPostModel = new PortalPostModel();
        $post            = $portalPostModel->where('id', $id)->find();
        $postCategories  = $post->categories()->alias('a')->column('a.name', 'a.id');
        $postCategoryIds = implode(',', array_keys($postCategories));

        $themeModel        = new ThemeModel();
        $articleThemeFiles = $themeModel->getActionThemeFiles('portal/Article/index');
        $venueModel = new VenueModel();
        $areaModel = new AreaModel();
        $address = $venueModel->where('id', $post->venue)->value('address');
        $areas = $areaModel->adminAreaTree($address);
        $myVenue = $venueModel->where(['status'=>1, 'address'=>$address, 'id'=>['in', UserModel::getCurrentVenue()]])->select()->toArray();
        $myv = '';
        foreach ($myVenue as $v){
            $select = $v['id'] == $post->venue ? 'selected' : '';
            $myv .= "<option value='".$v['id']."' ".$select.">".$v['name']."</option>";
        }

        $venues = $venueModel->where(['status'=>1,'id'=>['in', UserModel::getCurrentVenue()]])->order(['list_order'=>'asc'])->select()->toArray();
        $this->assign('areas', $areas);
        $this->assign('my_venue', $myv);
        $this->assign('venues', json_encode($venues));
        $this->assign('article_theme_files', $articleThemeFiles);
        $this->assign('post', $post);
        $this->assign('post_categories', $postCategories);
        $this->assign('post_category_ids', $postCategoryIds);
        $this->assign('cid', $cid);
        return $this->fetch();
    }

    /**
     * 编辑文章提交
     * @adminMenu(
     *     'name'   => '编辑文章提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑文章提交',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {

        if ($this->request->isPost()) {
            $data   = $this->request->param();
            $post   = $data['post'];
            $result = $this->validate($post, 'AdminArticle');
            if ($result !== true) {
                $this->error($result);
            }

            $portalPostModel = new PortalPostModel();

            if (!empty($data['photo_names']) && !empty($data['photo_urls'])) {
                $data['post']['more']['photos'] = [];
                foreach ($data['photo_urls'] as $key => $url) {
                    $photoUrl = cmf_asset_relative_url($url);
                    array_push($data['post']['more']['photos'], ["url" => $photoUrl, "name" => $data['photo_names'][$key]]);
                }
            }

            if (!empty($data['file_names']) && !empty($data['file_urls'])) {
                $data['post']['more']['files'] = [];
                foreach ($data['file_urls'] as $key => $url) {
                    $fileUrl = cmf_asset_relative_url($url);
                    array_push($data['post']['more']['files'], ["url" => $fileUrl, "name" => $data['file_names'][$key]]);
                }
            }

            $portalPostModel->adminEditArticle($data['post'], $data['post']['categories']);

            $hookParam = [
                'is_add'  => false,
                'article' => $data['post']
            ];
            hook('portal_admin_after_save_article', $hookParam);

            $this->success('保存成功!');

        }
    }

    /**
     * 文章删除
     * @adminMenu(
     *     'name'   => '文章删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章删除',
     *     'param'  => ''
     * )
     */
    public function delete()
    {

        $param           = $this->request->param();
        $portalPostModel = new PortalPostModel();


        if(isset($param['cid']) && $param['cid'] == 0){
            if (isset($param['id'])) {
                $id           = $this->request->param('id', 0, 'intval');
                $result       = $portalPostModel->where(['id' => $id])->find();
                $data         = [
                    'object_id'   => $result['id'],
                    'create_time' => time(),
                    'table_name'  => 'portal_post',
                    'name'        => $result['post_title'],
                    'user_id'=>cmf_get_current_admin_id()
                ];
                $resultPortal = $portalPostModel->where(['id'=>$id])->delete();
                if ($resultPortal) {
                    Db::name('portal_category_post')->where(['post_id'=>$id])->delete();
                    Db::name('portal_tag_post')->where(['post_id'=>$id])->update(['status'=>0]);
                        Db::name('recycleBin')->insert($data);
                }
                $this->success("删除成功！", '');

            }

            if (isset($param['ids'])) {
                $ids     = $this->request->param('ids/a');
                $recycle = $portalPostModel->where(['id' => ['in', $ids]])->select();
                $result  = $portalPostModel->where(['id' => ['in', $ids]])->delete();
                if ($result) {
                    Db::name('portal_category_post')->where(['post_id' => ['in', $ids]])->delete();
                    Db::name('portal_tag_post')->where(['post_id' => ['in', $ids]])->update(['status'=>0]);
                    foreach ($recycle as $value) {
                        $data = [
                            'object_id'   => $value['id'],
                            'create_time' => time(),
                            'table_name'  => 'portal_post',
                            'name'        => $value['post_title'],
                            'user_id'=>cmf_get_current_admin_id()
                        ];
                        Db::name('recycleBin')->insert($data);
                    }
                    $this->success("删除成功！", '');
                }
            }
        }else{
            if (isset($param['id'])) {
                $id           = $this->request->param('id', 0, 'intval');
                $cid           = $this->request->param('cid', 0, 'intval');
                $result       = $portalPostModel->where(['id' => $id])->find();
                $data         = [
                    'object_id'   => $result['id'],
                    'create_time' => time(),
                    'table_name'  => 'portal_post',
                    'name'        => $result['post_title'],
                    'user_id'=>cmf_get_current_admin_id()
                ];
//
                $portalCategoryModel = new PortalCategoryModel();
                $categoryArr = $portalCategoryModel->getTreeIds($cid);
                array_unshift($categoryArr, $cid);
                Db::name('portal_category_post')->where('post_id',$id)->where('category_id','in',$categoryArr)->delete();
                $is_null= Db::name('portal_category_post')->where('post_id',$id)->select();
                if(count($is_null)==0){
                    Db::name('portal_post')->where('id',$id)->update(['delete_time'=>time()]);
                }
                Db::name('recycleBin')->insert($data);
//
                $this->success("删除成功！", '');

            }

            if (isset($param['ids'])) {
                $ids     = $this->request->param('ids/a');
                $cid           = $this->request->param('cid', 0, 'intval');
                $recycle = $portalPostModel->where(['id' => ['in', $ids]])->select();
//                $result  = $portalPostModel->where(['id' => ['in', $ids]])->update(['delete_time' => time()]);
                if (count($recycle)!=0) {
                    $portalCategoryModel = new PortalCategoryModel();
                    $categoryArr = $portalCategoryModel->getTreeIds($cid);
                    array_unshift($categoryArr, $cid);
                    Db::name('portal_category_post')->where(['post_id' => ['in', $ids]])->where('category_id','in',$categoryArr)->delete();
                    foreach ($ids as $vo){
                        $is_null= Db::name('portal_category_post')->where('post_id',$vo)->select();
                        if(count($is_null)==0){
                            Db::name('portal_post')->where('id',$vo)->update(['delete_time'=>time()]);
                        }
                    }

                    Db::name('portal_tag_post')->where(['post_id' => ['in', $ids]])->update(['status'=>0]);
                    foreach ($recycle as $value) {
                        $data = [
                            'object_id'   => $value['id'],
                            'create_time' => time(),
                            'table_name'  => 'portal_post',
                            'name'        => $value['post_title'],
                            'user_id'=>cmf_get_current_admin_id()
                        ];
                        Db::name('recycleBin')->insert($data);
                    }
                    $this->success("删除成功！", '');
                }
            }
        }


    }

    public function recovery(){
        $param           = $this->request->param();
        $portalPostModel = new PortalPostModel();

        if (isset($param['id'])) {
            $id           = $this->request->param('id', 0, 'intval');
            $result       = $portalPostModel->where(['id' => $id])->find();
            $data         = [
                'object_id'   => $result['id'],
                'create_time' => time(),
                'table_name'  => 'portal_post',
                'name'        => $result['post_title'],
                'user_id'=>cmf_get_current_admin_id()
            ];
            $resultPortal = $portalPostModel
                ->where(['id' => $id])
                ->update(['delete_time' => 0]);
            if ($resultPortal) {
                Db::name('portal_category_post')->where(['post_id'=>$id])->update(['status'=>1]);
                Db::name('portal_tag_post')->where(['post_id'=>$id])->update(['status'=>1]);
//                Db::name('recycleBin')->insert($data);
            }
            $this->success("恢复成功！", '');

        }

        if (isset($param['ids'])) {
            $ids     = $this->request->param('ids/a');
            $recycle = $portalPostModel->where(['id' => ['in', $ids]])->select();
            $result  = $portalPostModel->where(['id' => ['in', $ids]])->update(['delete_time' => 0]);
            if ($result) {
                Db::name('portal_category_post')->where(['post_id' => ['in', $ids]])->update(['status'=>1]);
                Db::name('portal_tag_post')->where(['post_id' => ['in', $ids]])->update(['status'=>1]);
                foreach ($recycle as $value) {
                    $data = [
                        'object_id'   => $value['id'],
                        'create_time' => time(),
                        'table_name'  => 'portal_post',
                        'name'        => $value['post_title'],
                        'user_id'=>cmf_get_current_admin_id()
                    ];
//                    Db::name('recycleBin')->insert($data);
                }
                $this->success("恢复成功！", '');
            }
        }

    }

    /**
     * 文章发布
     * @adminMenu(
     *     'name'   => '文章发布',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章发布',
     *     'param'  => ''
     * )
     */
    public function publish()
    {
        $param           = $this->request->param();
        $portalPostModel = new PortalPostModel();

        if (isset($param['ids']) && isset($param["yes"])) {
            $ids = $this->request->param('ids/a');

            $portalPostModel->where(['id' => ['in', $ids]])->update(['post_status' => 1, 'published_time' => time()]);

            $this->success("发布成功！", '');
        }

        if (isset($param['ids']) && isset($param["no"])) {
            $ids = $this->request->param('ids/a');

            $portalPostModel->where(['id' => ['in', $ids]])->update(['post_status' => 0]);

            $this->success("取消发布成功！", '');
        }

    }

    /**
     * 文章置顶
     * @adminMenu(
     *     'name'   => '文章置顶',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章置顶',
     *     'param'  => ''
     * )
     */
    public function top()
    {
        $param           = $this->request->param();
        $portalPostModel = new PortalPostModel();

        if (isset($param['ids']) && isset($param["yes"])) {
            $ids = $this->request->param('ids/a');
            $portalCategoryModel = new PortalCategoryModel();
            $cids = $portalCategoryModel->getTreeIds($this->allCid);
            $checkedId = $portalPostModel
                ->alias('a')
                ->join('__PORTAL_CATEGORY_POST__ b', 'a.id=b.post_id')
                ->where(['is_top' => 1, 'b.category_id'=>['in', $cids], 'delete_time'=>0])
                ->group('a.id')->column('a.id');
            $updateIds = array_flip($checkedId)+array_flip($ids);
            $updateIds = array_keys($updateIds);
            if(count($updateIds) > 2){
                $this->error("最多置顶两条！", '');
            }else{
                $portalPostModel->where(['id' => ['in', $updateIds]])->update(['is_top' => 1]);

                $this->success("置顶成功！", '');
            }

        }

        if (isset($_POST['ids']) && isset($param["no"])) {
            $ids = $this->request->param('ids/a');

            $portalPostModel->where(['id' => ['in', $ids]])->update(['is_top' => 0]);

            $this->success("取消置顶成功！", '');
        }
    }

    /**
     * 文章推荐
     * @adminMenu(
     *     'name'   => '文章推荐',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章推荐',
     *     'param'  => ''
     * )
     */
    public function recommend()
    {
        $param           = $this->request->param();
        $portalPostModel = new PortalPostModel();

        if (isset($param['ids']) && isset($param["yes"])) {
            $ids = $this->request->param('ids/a');

            $portalPostModel->where(['id' => ['in', $ids]])->update(['recommended' => 1]);

            $this->success("推荐成功！", '');

        }
        if (isset($param['ids']) && isset($param["no"])) {
            $ids = $this->request->param('ids/a');

            $portalPostModel->where(['id' => ['in', $ids]])->update(['recommended' => 0]);

            $this->success("取消推荐成功！", '');

        }
    }

    /**
     * 文章排序
     * @adminMenu(
     *     'name'   => '文章排序',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章排序',
     *     'param'  => ''
     * )
     */
    public function listOrder()
    {
        parent::listOrders(Db::name('portal_category_post'));
        $this->success("排序更新成功！", '');
    }

    public function move()
    {

    }

    public function copy()
    {

    }
    public function area_venues(){
         $area_id=$this->request->param('area_id',0,'intval');
         $userModel= new UserModel();
         $venues=$userModel->area_venues($area_id);
         if(!empty($venues)){
             echo json_encode(['status'=>1,'data'=>$venues,'message'=>'场馆获取成功']);exit;
         }else{
             echo json_encode(['status'=>1,'data'=>'','message'=>'无场馆']);exit;
         }
    }


    /**
     * 文章库
     */
    public function article_lib($param,$id){

        $categoryId = $this->request->param('category', 0,'intval');
        $all_status = $this->request->param('all_status', 0,'intval');
        $postService = new PostService();
        $portalCategoryModel = new PortalCategoryModel();

//        $haveChild = false;
//        if(!isset($param['category']) || $param['category'] == 0 || $param['category'] == $id){
//            $param['category'] = $id;
//        }


        $data = $postService->ArticleList($param,false,$all_status);

        $categoryTree = $portalCategoryModel->adminCategoryTree($categoryId, 0, $id);

        //区域列表
        $areaModel = new AreaModel();
        $area_sid = isset($param['area_id'])?$param['area_id']:'';
        $areas = $areaModel->adminAreaTree($area_sid);

        $venueModel = new VenueModel();
        $venues = $venueModel->where(['id' => ['in', UserModel::getCurrentVenue2()]])->order(['list_order' => 'asc'])->select()->toArray();
        $venue_id = isset($param['venue'])?$param['venue']:0;
        $myv = '';
        foreach ($venues as $v) {
            $select = $v['id'] ==$venue_id ? 'selected' : '';
            $myv .= "<option value='" . $v['id'] . "' " . $select . ">" . $v['name'] . "</option>";
        }

        $page = $data->render();
        $this->assign('all_status', $all_status);
        $this->assign('areas', $areas);
        $this->assign('venues', json_encode($venues));
        $this->assign('page',$page);
        $this->assign('articles',$data);
        $this->assign('id', $id);
        $this->assign('category_tree',$categoryTree);
        $this->assign('venue_sid',json_encode(isset($param['venue'])?$param['venue']:''));
        $this->assign('my_venue', $myv);

        return $this->fetch();
    }

}
