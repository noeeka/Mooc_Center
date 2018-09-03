<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <zxxjjforever@163.com>
// +----------------------------------------------------------------------

namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use app\admin\model\CommentModel;

class CommentController extends AdminBaseController
{
    /*
     * 分类列表
     */
    public function index(){
         //获取分类列表
//        $comment=new CommentModel();
//        $commentlist=$comment->order('updatetime DESC')->select();
//        var_dump($comment);die;
          return $this->fetch();


    }
}