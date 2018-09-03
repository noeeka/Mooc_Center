<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use app\admin\model\AboutusModel;
use think\db;


class AboutusController extends AdminBaseController
{

    /**
     * 关于我们
     */
    public function index()
    {
        $aboutusModel=new AboutusModel();
         if($this->request->isPost()){
             $post=$this->request->param();
             $post['content'] = $aboutusModel->setPostContentAttr($post['content']);
             $post['id'] = 1;
             $aboutusModel->allowField(true)->isUpdate(true)->save($post);
             $this->success('修改成功');
         }

         $aboutus=$aboutusModel->where(['id'=>1])->find();
         $aboutus['content']=$aboutusModel->getPostContentAttr($aboutus['content']);
         $this->assign('aboutus',  $aboutus);

         return $this->fetch();
    }



}