<?php
namespace app\admin\controller;

use app\admin\model\PortalCategoryModel;
use app\admin\model\UserModel;
use app\admin\model\VenueModel;
use app\admin\validate\WenhuaMangeValidate;
use cmf\controller\AdminBaseController;
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 2018/5/7
 * Time: 11:25
 */
class WenhuaManageController extends AdminBaseController{

    public function index(){
        $list = VenueModel::where(['id'=>['in', UserModel::getCurrentVenue()]])->order(['publish_time'=>'desc', 'map_is_show'=>'desc'])->paginate(20);
        $this->assign([
            'list'=>$list->items(),
            'page'=>$list->render()
        ]);
        return $this->fetch();
    }

    public function edit()
    {
        $id = input('param.id', '0');
        $venue = VenueModel::get($id);
        if($venue){
            //文化点类型
            $map_type = db('venue_type')->select()->toArray();
            foreach ($map_type as $key => $value){
                $map_type[$key]['img'] = cmf_get_image_preview_url($value['img']);
            }
            //所选类型和图片
            $example_icon = $map_type[0]['img'];
            $map_types = '';
            foreach ($map_type as $value){
                if($value == $venue->map_type){
                    $select = ' selected ';
                    $example_icon = $value['img'];
                }else{
                    $select = '';
                }
                $map_types .= '<option value="'.$value['id'].'"'.$select.'>'.$value['name'].'</option>';
            }
            //所有功能区
            $funcs = PortalCategoryModel::where(['parent_id'=>0, 'map_is_show'=>1])->field('id,name')->select();
            if(!empty($funcs)){
                $funcs = $funcs->toArray();
            }
            //地图层数
            $max_level = db('mapset')->where('id', 1)->value('map_index', 1);
            $max_level ++;
            $venue = $venue->toArray();
            $this->assign($venue);
            $this->assign('map_type_options', $map_types);
            $this->assign('map_types', json_encode($map_type));
            $this->assign('example_icon', $example_icon);
            $this->assign('funcs', $funcs);
            $this->assign('max_level', $max_level);
            return $this->fetch();
        }else{
            $this->error('场馆不存在');
        }
    }

    public function editPost(){
        $id = $this->request->param('id', 0);
        $data['map_is_show'] = $this->request->param('map_is_show', 0);
        $data['map_is_diy_img'] = $this->request->param('map_is_diy_img', 0);
        $data['map_img'] = $this->request->param('map_img', '');
        $data['map_location'] = $this->request->param('map_location', '');
        $data['map_subject_is_open'] = $this->request->param('map_subject_is_open', 0);
        $data['map_subject_url'] = $this->request->param('map_subject_url', '');
        $data['map_nav_is_open'] = $this->request->param('map_nav_is_open', 0);
        $data['map_real_location'] = $this->request->param('map_real_location', '');
        $data['map_func'] = $this->request->param('map_func/a', []);
        $venue = VenueModel::get($id);
        if($venue == null){
            $this->error('文化点不存在');
        }
        if($data['map_is_diy_img'] == 1 && $data['map_img'] == ''){
            $this->error('独立图标必须上传');
        }
        if($data['map_location'] == ''){
            $this->error('文化点位置必须选择');
        }
        if(!$this->checkLocation($data['map_location'])){
            $this->error('文化点位置异常');
        }
        if($data['map_subject_is_open'] == 1 && $data['map_subject_url'] == ''){
            $this->error('开启专题必须上传链接');
        }
        if($data['map_nav_is_open'] == 1){
            if($data['map_real_location'] == ''){
                $this->error('实际导航位置必须选择');
            }
            if(!$this->checkLocation($data['map_real_location'])){
                $this->error('实际导航位置异常');
            }
        }
        $data['map_func'] = implode(',', $data['map_func']);
        if(false === $venue->save($data)){
           $this->error('编辑失败');
        }else{
            $this->success('编辑成功');
        }
    }

    private function checkLocation($str){
        list($lng, $lat) = explode(',', $str);
        if(($lng>= -180 && $lng <= 180) && ($lat >= -90 && $lat <= 90)){
            return true;
        }else{
            return false;
        }
    }
}