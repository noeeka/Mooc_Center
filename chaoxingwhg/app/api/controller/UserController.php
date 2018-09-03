<?php

namespace app\api\controller;


use app\admin\model\UserModel;
use app\api\controller\Base;
use think\Config;
use think\Db;
use think\Loader;
use code\Code;
use token\Token;

class UserController extends Base
{
    public function read()
    {
        //登录检查
        $this->check_sign();

        $token = input('param.token');

        $token_info = db('token')->where('token', $token)->field('user_id')->find();
        $user_id = $token_info['user_id'];

        $user_info = UserModel::get($user_id);

        if ($user_info) {
            $user_info->avatar_url = cmf_get_image_preview_url($user_info->avatar);
            return $this->output_success(13101, $user_info, '信息获取成功');
        } else {
            return $this->output_error(13001, '信息不存在');
        }
    }

    /**
     * 更新用户信息
     * @return array
     */
    public function update()
    {
        //登录检查
        $this->check_sign();

        $token = input('param.token');

        $email = urldecode(input('param.email'));
        $nickname = input('param.nickname');
        $avatar = input('param.avatar');
        $sex = input('param.sex');
        $user_id = Token::get_user_id($token);

        $data = [];
        if (!empty($email)) {
            if (isEmail($email)) {
                $data['user_email'] = $email;
            } else {
                return $this->output_error(13002, 'Email格式错误');
            }
        }
        if (!empty($nickname)) {
            if ($this->check_name($nickname)) {
                $data['user_nickname'] = $nickname;
            } else {
                return $this->output_error(13003, '昵称包含敏感词');
            }
        }
        if (!empty($avatar)) {
            $data['avatar'] = $avatar;
        }
        if (!empty($sex)) {
            if (in_array($sex, [0, 1, 2])) {
                $data['sex'] = $sex;
            } else {
                return $this->output_error(13004, '性别设置错误');
            }
        }

        $data['id'] = $user_id;
        UserModel::update($data);
        return $this->output_success(13102, [], '更新成功');
    }

    /**
     * 真实姓名，身份证认证
     * @return array
     * @throws \think\Exception
     */
    public function auth_real_user()
    {
        //登录检查
        $this->check_sign();
        $token = input('param.token');
        $user_id = Token::get_user_id($token);
        $realname = input('param.realname');
        $shenfenzheng = input('param.shenfenzheng');
        $shenfenzheng_img = input('param.sfz_img/a');

        /*$user_id=1;
        $realname="张山1";
        $shenfenzheng='51132119850501245X';
        $shenfenzheng_img=['2017/12312313.png','3456/233434.gif'];*/


        if (empty($realname)) {
            return $this->output_error(13006, '真实姓名不能为空');
        }
        if (!$this->check_name($realname)) {
            return $this->output_error(13007, '姓名包含敏感词');
        }
        if (empty($shenfenzheng)) {
            return $this->output_error(13008, '身份证号不能为空');
        }
        if (!isShenfenzheng($shenfenzheng)) {
            return $this->output_error(13009, '身份证号格式错误');
        }
        if (empty($shenfenzheng_img)) {
            return $this->output_error(13010, '请上传身份证正反面照片');
        }

        $user_is_exsited = db('sfzimg')->where('user_id', $user_id)->field('id')->find();

        if ($user_is_exsited) {
            $data = [
                'id' => $user_is_exsited['id'],
                'user_id' => $user_id,
                'realname' => $realname,
                'shenfenzheng' => $shenfenzheng,
                'img' => json_encode($shenfenzheng_img),
                'status' => 0,
            ];

            $result = db('sfzimg')->update($data);
            return $this->output_success(13103, [], '身份认证提交成功');
        } else {
            $data = [
                'user_id' => $user_id,
                'realname' => $realname,
                'shenfenzheng' => $shenfenzheng,
                'img' => json_encode($shenfenzheng_img),
                'apply_time' => time(),
            ];

            $result = db('sfzimg')->insert($data);
            return $this->output_success(13103, [], '身份认证提交成功');
        }
    }

    public function auth_read()
    {
        $this->check_sign();
        $token = input('param.token');
        $user_id = Token::get_user_id($token);
        $data = db('sfzimg')->where('user_id', $user_id)->find();
        if (empty($data)) {
            return $this->output_error(13011, '未认证用户');
        } else {
            $data['img'] = json_decode($data['img'], true);
            foreach ($data['img'] as $value) {
                $data['server_img'][] = cmf_get_image_preview_url($value);
            }
            return $this->output_success(13104, $data, '身份信息获取成功');
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

    //更新密码
    public function savePassword()
    {

        $mobile = input('post.mobile', '', 'trim');
        $yzm = input('post.yzm', '', 'trim');
        if ($mobile == '') {
            $this->check_sign();
            $token = input('post.token');
            $user_id = Token::get_user_id($token);
        } else {
            $user = passport_userinfo_by_mobile($mobile);
            if($user['result'] === false){
                return $this->output_error(13015, $user['mes']);
            }else{
                $user_id = passport_get_self_uid($user['phone'], '', $user['uid']);
            }
            $code = Code::check($mobile, $yzm, 'reset_psd');
            if ($code['status'] == 0) {
                return $this->output_error(13015, $code['msg']);
            }
        }

        $password = input('post.password', '', 'trim');
        $repeat = input('post.repeat', '', 'trim');
        if ($password == '') {
            return $this->output_error(13011, '密码不能为空');
        }
        if ($repeat != $password) {
            return $this->output_error(13012, '两次密码不一致');
        }
        if (preg_match('/^\w{10,15}$/', $password) !== 1) {
            return $this->output_error(13013, '密码只接受长度至少10位的字母数字下划线');
        }
        $xuexitong_uid = UserModel::where('id', $user_id)->value('xuexitong_uid', 0);
        if($xuexitong_uid > 0){
            $res = passport_reset_pwd($xuexitong_uid, $password);
            if($res['result']){
                return $this->output_success(13110, [], '密码修改成功');
            }
        }
        return $this->output_error(13014, '密码修改失败');
    }

//    //更新密码
//    public function savePassword()
//    {
//
//        $mobile = input('post.mobile', '', 'trim');
//        $yzm = input('post.yzm', '', 'trim');
//        if ($mobile == '') {
//            $this->check_sign();
//            $token = input('post.token');
//            $user_id = Token::get_user_id($token);
//        } else {
//            $user = UserModel::get(['mobile' => $mobile, 'user_type'=>2]);
//            $code = Code::check($mobile, $yzm, 'reset_psd');
//            if ($code['status'] == 0) {
//                return $this->output_error(13015, $code['msg']);
//            }
//            if ($user == null) {
//                return $this->output_error(13015, '用户不存在');
//            }
//            $user_id = $user->id;
//        }
//
//        $password = input('post.password', '', 'trim');
//        $repeat = input('post.repeat', '', 'trim');
//        if ($password == '') {
//            return $this->output_error(13011, '密码不能为空');
//        }
//        if ($repeat != $password) {
//            return $this->output_error(13012, '两次密码不一致');
//        }
//        if (preg_match('/^\w{8,}$/', $password) !== 1) {
//            return $this->output_error(13013, '密码只接受长度至少8位的字母数字下划线');
//        }
//        $data = [
//            'id' => $user_id,
//            'user_pass' => cmf_password($password),
//        ];
//        UserModel::update($data);
//        return $this->output_success(13110, [], '密码修改成功');
//    }
    //志愿者个人资料
    public function  volun_profile(){
        //登录检查
        $this->check_sign();
        $token = input('param.token');

        $user_id = Token::get_user_id($token);
        $user_info = Db::name('user')->find($user_id);
        $user_info['format_birthday']=date('Y-m-d',$user_info['birthday']);

        $imgs = json_decode($user_info['volun_skill_imgs'],true);
        if(is_array($imgs)){
            $count = count($imgs);
            $volun_img = [];
            foreach($imgs as $k=>$v){
                $volun_img[]=cmf_get_image_preview_url($v);
            }
            $user_info['img'] = $volun_img;
            $user_info['img_count'] = $count;
        }


        //真实姓名
        $user_realname = Db::name('sfzimg')->where(['user_id'=>$user_id])->value('realname');
        $user_info['user_realname'] = $user_realname;

        //排名
        $userlist=Db::name('user')->where(['user_role'=>2])->order('score desc')->select();
        $rank=0;
        foreach ($userlist as $key=>$user){
            if($user['id'] ==$user_id){
                $rank=$key;
                break;
            }
        }
        $user_info['rank'] =$rank+1;

        //活动区域
        $area_id=$user_info['area'];
        $area = Db::name('area')->where(['id'=>['in',$area_id]])->field('id,name')->select();
        $user_info['area'] =$area;

        //才艺照片
        $user_info['photos'] =!empty($user_info['more'])?json_decode( $user_info['more']):'';

        if ($user_info) {
            $user_info['avatar_url'] = cmf_get_image_preview_url($user_info['avatar']);
            $user_info['speciality_html'] = parseTextArea($user_info['speciality']);
            return $this->output_success(13101, $user_info, '信息获取成功');
        } else {
            return $this->output_error(13001, '信息不存在');
        }
    }

    //更新志愿者资料
    public function modify_volun_profie(){

        //登录检查
        $this->check_sign();
        $token = input('param.token');
        $user_id = Token::get_user_id($token);

//        $sex = input('param.sex', -1, 'intval');
        $birthday = input('param.birthday', -1);
        $nation = input('param.nation', -1);
        $mobile = input('param.tel', -1);
        $area = input('param.area', -1);
        $volun_skill_imgs = input('param.photos', -1);
        $speciality = input('param.speciality', -1);
        $limit_count = 60;

//        if ($sex != -1) {
//            $data['sex'] = $sex;
//        }
        if ($birthday != -1) {
            $data['birthday'] = strtotime($birthday);
        }
        if ($nation != -1) {
            $data['nation'] = $nation;
        }
        if ($mobile != -1) {
            $data['mobile'] = $mobile;
        }
        if ($area != -1) {
            $data['area'] = $area;
        }
        if ($volun_skill_imgs != -1) {
            $data['volun_skill_imgs'] = $volun_skill_imgs == '' ? '' : json_encode(explode(',', $volun_skill_imgs));
            $img_count = count($data['volun_skill_imgs']);
            if($img_count > $limit_count){
                return $this->output_error(13201,'上传数量超过限制');
            }
        }
        if ($speciality != -1) {
            $data['speciality'] = $speciality;
        }

        Db::name('user')->where(['id'=>$user_id])->update($data);
        return $this->output_success(13101, [], '更新个人资料成功');
    }

    //参与历史
    public function play_history(){
        //登录检查
        $this->check_sign();
        $token = input('param.token');
        $user_id = Token::get_user_id($token);



        $page=input('page',1,'intval');
        $len=input('len',10,'intval');
        $where['v.status'] =1;
        $list=Db::name('activity_baoming')->alias('ab')
             ->join('activity a','ab.activity_id = a.id','left')
             ->join('venue v','a.venue = v.id','left')
             ->where(['a.type'=>0,'ab.user_id'=>$user_id,'a.end_time'=>['<',time()],'ab.status'=>1,'a.delete_time'=>0])
             ->order('a.start_time desc')
             ->page($page, $len)
             ->field('ab.*,v.name as vname,a.start_time,a.end_time,a.title')
             ->select();
        $baoming_info=Db::name('activity_baoming')->alias('ab')
            ->join('activity a','ab.activity_id = a.id','left')
            ->where(['a.type'=>0,'ab.user_id'=>$user_id,'a.end_time'=>['<',time()],'ab.status'=>1,'a.delete_time'=>0])
            ->join('venue v','a.venue = v.id','left')
            ->field('sum(ab.score) as  total_score,count(ab.id) as total_count')
            ->find();
        $result['list']=$list;
        $result['count']=!empty($baoming_info['total_count'])?$baoming_info['total_count']:0 ;
        $result['total_score']=$baoming_info['total_score'];

        if ($list) {
            return $this->output_success(13101, $result, '参与历史获取成功');
        } else {
            return $this->output_error(13001, '参与历史不存在');
        }
    }

    //报名结果
    public function baoming_info(){
        //登录检查
        $this->check_sign();
        $token = input('param.token');
        $user_id = Token::get_user_id($token);

        $status=input('status');

        $where=[];
        $where['ab.user_id']=$user_id;
        $where['a.type']=0;//活动招募
        $where['a.delete_time']=0;
        if(isset($status) and $status!=3){
            $where['ab.status']=$status;
        }
        $where['v.status'] = 1;
        $page=input('page',1,'intval');
        $len=input('len',10,'intval');
        $list=Db::name('activity_baoming')->alias('ab')
            ->join('activity a','ab.activity_id = a.id','left')
            ->join('venue v','a.venue = v.id','left')
            ->where($where)
            ->order('ab.created_at desc')
            ->page($page, $len)
            ->field('ab.*,v.name as vname,a.title,a.start_time,a.end_time')
            ->select();
        $count=Db::name('activity_baoming')->alias('ab')
            ->join('activity a','ab.activity_id = a.id','left')
            ->join('venue v','a.venue = v.id','left')
            ->where($where)
            ->count();

        $result['list'] =$list;
        $result['count'] =$count;

        if ($list) {
            return $this->output_success(13101, $result, '报名结果获取成功');
        } else {
            return $this->output_error(13001, '报名结果不存在');
        }
    }
}
