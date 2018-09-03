<?php

namespace app\admin\controller;

use app\admin\model\MapsetModel;
use app\admin\model\PortalCategoryModel;
use cmf\controller\AdminBaseController;

class MapController extends AdminBaseController
{
      public function index(){
          $map_set = new MapsetModel();

          if($this->request->isPost()){
              $post   = $this->request->post();
              $post['map_cat']=json_encode($post['map_cat']);

              $map_set ->where('id',1)->data($post)->update();
              $this->success("保存成功！");

          }else{
              $map_info=$map_set->find();

              //需要在文化地图上显示的栏目
              $portal_cat_model= new PortalCategoryModel();
              $map_cat=$portal_cat_model->where(['map_is_show'=>1,'status'=>1,'parent_id'=>0])->field('id,name')->select();
              //选中的栏目
              $selected_map_cat = json_decode($map_info['map_cat']);

              $this->assign('map_info',$map_info);
              $this->assign('map_cat',$map_cat);
              $this->assign('selected_map_cat',$selected_map_cat);
              return $this->fetch();
          }

      }
}

