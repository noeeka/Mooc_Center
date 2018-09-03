<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/5/9
 * Time: 17:48
 */

namespace app\api\controller;


use app\admin\model\Sfzimg;
use app\admin\model\SfzimgModel;
use app\portal\model\UserModel;
use app\admin\model\AreaModel;

class VolunRegisterController extends Base{

    public function index(){
        $user_id = $this->getuid();

        if(!empty($user_id)){
            $sfzingModel = new SfzimgModel();
            $user_msg = $sfzingModel->where(['user_id'=>$user_id])->find();
//            $user_msg['areasTree']=$areasTree;
            if($user_msg){
                $img = json_decode($user_msg['img'],true);
                if(is_array($img)){
                    $imgs = [];
                    foreach($img as $value){
                        $imgs[] = cmf_get_image_preview_url($value);
                    }
                    $user_msg['imgs'] = $imgs;
                }

                return $this->output_success(12112,$user_msg,'');
            }else{
                $data['user_id'] = $user_id;
                $data['status']  = 3;
                return $this->output_success(12112,$data,'未实名认证');
            }

        }else{
            return $this->output_error(12011,'请先登录');
        }
    }

    /**
     * 检查名称是否合法
     * @param $name
     * @return bool
     */
    private function check_name($name)
    {
        if (empty($name)) {
            return false;
        }
        //检查内容是否合法
        if (check_word($name)) {
            return $name;
        } else {
            return false;
        }

    }

    //注册志愿者
    public function register(){
       // trace('debug--'.json_encode($this->request->param()), true);
        $realname = input('param.realname');
        $uid = $this->getuid(true);
        $sex = input('param.sex');
        $birthday = input('param.birthday','');
        $nation = input('param.nation','');
        $mobile = input('param.mobile');
        $ID = input('param.ID');
        $sfzimg = input('param.sfzimg/a');
        $area = input('param.area','');
        $Speciality = input('param.Speciality','');
        $photos = input('param.photos/a');


        if (!$this->check_name($realname)) {
            return $this->output_error(13007, '姓名包含敏感词');
        }

        if(empty($sex)){
            return $this->output_error(13002, '性别不能为空');
        }

        if(empty($mobile)){
            return $this->output_error(13003, '手机号不能为空');
        }

        if(empty($photos)){
            return $this->output_error(13005, '才艺照片不能为空');
        }

        $count = count($photos);
        if($count < 3){
            return $this->output_error(13005, '才艺照片不能少于三张');
        }

        $sfzModel = new SfzimgModel();
        $sfz_exist = $sfzModel->where('user_id',$uid)->find();

        if(empty($sfz_exist)){
            //未进行实名认证
            if(empty($ID)){
                return $this->output_error(13004, '身份证号不能为空');
            }

            if (!isShenfenzheng($ID)) {
                return $this->output_error(13009, '身份证号格式错误');
            }

            if(empty($sfzimg)){
                return $this->output_error(13005, '身份证证件照不能为空');
            }else if(count($sfzimg) !=2){
                return $this->output_error(13006,'身份证件照数量异常');
            }

            if (empty($realname)) {
                return $this->output_error(13001, '真实姓名不能为空');
            }


            $sfzdata['img'] = json_encode(['img'=>$sfzimg[0],'img1'=>$sfzimg[1]]);
            $sfzdata['user_id'] = $uid;
            $sfzdata['realname'] = $realname;
            $sfzdata['shenfenzheng'] = $ID;
            $sfzdata['apply_time'] = time();

            $resultSave = db('sfzimg')->insert($sfzdata);

            $data['user_realname'] = $realname;
        }

        $data = [
//            'user_realname'=>$realname,
            'sex'=>$sex,
            'birthday'=>strtotime($birthday),
            'mobile'=>$mobile,
            'nation'=>$nation,
            'area'=>$area,
            'speciality'=>$Speciality,
            'volun_skill_imgs'=>json_encode($photos),
            'volun_status' => 1,
            'list_order' => $uid
        ];

        $userModel = new UserModel();
        $result = $userModel->where('id',$uid)->update($data);

        if($result !== false){
            return $this->output_success(13111,[],'志愿者注册信息提交成功');
        }else{
            return $this->output_error(13101,'志愿者注册信息提交失败');
        }

    }
}