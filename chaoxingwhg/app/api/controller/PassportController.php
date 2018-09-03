<?php
namespace app\api\controller;

use app\admin\model\UserModel;
use think\Config;
use think\Cookie;
use token\Token;
use code\Code as MobileCode;
use think\Db;

class PassportController extends Base
{
    /**
     * 用户注册
     * @return array
     */
    public function regist()
    {
        $mobile = input('param.mobile');                  //手机号
        $password = input('param.password');              //密码
        $confirm_psd = input('param.confirm_psd');         //确认密码
        $verify_code = input('param.verify_code');          //验证码
        $agreement = input('param.agreement');           //用户协议

        //检查输入数据
        if (!isMobile($mobile)) {
            return $this->output_error(11005, '手机号不能为空');
        }
        if (empty($password)) {
            return $this->output_error(11006, '密码不能为空');
        }
        if (preg_match('/^\w{8,15}$/', $password) != 1) {
            return $this->output_error(11011, '密码格式不正确');
        }
        if (empty($confirm_psd)) {
            return $this->output_error(11007, '确认密码不能为空');
        }
        if ($password !== $confirm_psd) {
            return $this->output_error(11008, '密码与确认密码不等');
        }
        if (empty($verify_code)) {
            return $this->output_error(11009, '验证码不能为空');
        }
        if (!$agreement) {
            return $this->output_error(11010, '请同意用户协议');
        }

        //检查验证码
        $code_status = MobileCode::check($mobile, $verify_code, 'regist');
        if ($code_status['status'] == 0 && $code_status['code'] == 1001) {
            return $this->output_error(11011, '验证码错误');
        } elseif ($code_status['status'] == 0 && $code_status['code'] == 1002) {
            return $this->output_error(11012, '验证码已过期');
        }

        $res = passport_register($mobile, $password);

        if ($res['status'] == 'success') {
            passport_get_self_uid($mobile, $password, $res['uid']);
            return $this->output_success(11104, [], '用户注册成功');
        } else {
            return $this->output_error(11013, $res['mes']);
        }
    }

    /**
     * 用户登录
     * @input $user_id,$passworde
     * @return json
     */
    public function login()
    {
        //获取登录信息
        $user_id = input('param.user_id');        //用户ID：mobile 或email
        $password = input('param.password');   //密码
        if (empty($user_id) || empty($password)) {
            return $this->output_error(11003, '用户名/密码不能为空');
        }

        //是否被拉黑
        $user_info=Db::name('user')->where(['mobile'=>$user_id])->field('id,user_pass,user_status')->find();
        if(!empty($user_info) && $user_info['user_status'] ==0){
            return $this->output_error(11011,'你已被拉黑，如需咨询请到意见反馈！');
        }

        $res = passport_login($user_id, $password);

        if (is_array($res)) {
            if ($res['result'] == true) {
                $token_info = $this->selfLogin($user_id, $password, $res['uid']);
                return $this->output_success(11101, $token_info, '登录成功');
            } else {
                return $this->output_error(11002, $res['errorMsg']);
            }
        } else {
            return $this->output_error(11001, '登录失败');
        }
    }

    private function selfLogin($phone, $password, $xuexitong_uid)
    {
        $uid = passport_get_self_uid($phone, $password, $xuexitong_uid);
        $token_info = Token::get($uid);
        session('user.id', $uid);
        return $token_info;
    }
//    /**
//     * 用户注册
//     * @return array
//     */
//    public function regist(){
//        $mobile               =input('param.mobile');                  //手机号
//        $password           =input('param.password');              //密码
//        $confirm_psd       =input('param.confirm_psd');         //确认密码
//        $verify_code         =input('param.verify_code');          //验证码
//        $agreement         =input('param.agreement');           //用户协议
//
//        //检查输入数据
//        if(!isMobile($mobile)){
//            return $this->output_error(11005,'手机号不能为空');
//        }
//        if(empty($password)){
//            return $this->output_error(11006,'密码不能为空');
//        }
//        if(empty($confirm_psd)){
//            return $this->output_error(11007,'确认密码不能为空');
//        }
//        if($password!==$confirm_psd){
//            return $this->output_error(11008,'密码与确认密码不等');
//        }
//        if(empty($verify_code)){
//             return $this->output_error(11009,'验证码不能为空');
//       }
//        if(!$agreement){
//            return $this->output_error(11010,'请同意用户协议');
//        }
//
//        //检查验证码
//        $code_status=MobileCode::check($mobile,$verify_code,'regist');
//        if($code_status['status']==0&&$code_status['code']==1001){
//            return $this->output_error(11011,'验证码错误');
//        }elseif($code_status['status']==0&&$code_status['code']==1002){
//            return $this->output_error(11012,'验证码已过期');
//        }
//
//        $user_exsited=db('user')->where(['mobile'=>$mobile, 'user_type'=>2])->find();
//        //用户已存在
//        if($user_exsited)
//        {
//            return $this->output_error(11013,'该帐号已被其他用户注册');
//        }
//        //新用户注册
//        else
//        {
//            $user=new UserModel();
//            $login = str_replace(substr($mobile,3,4),"****",$mobile);
//            $data=[
//                'mobile'=>$mobile,
//                'user_pass'=>cmf_password($password),
//                'user_nickname'=>$login,
//                'last_login_time'=>time(),
//                'create_time'=>time(),
//                'user_status'=>1,
//                'user_login'=>$mobile,
//                'user_type' => 2,
//                'volun_skill_imgs' => ''
//            ];
//            $user->insert($data);
//            return $this->output_success(11104,[],'用户注册成功');
//        }
//    }
//
//    /**
//     * 用户登录
//     * @input $user_id,$passworde
//     * @return json
//     */
//    public function login(){
//        //获取登录信息
//        $where = array();
//        $user = new UserModel();
//        $user_id = input('param.user_id');        //用户ID：mobile 或email
//        $password = input('param.password');   //密码
//        $where['user_type'] = 2;
//
//        if(empty($user_id)||empty($password))
//            return $this->output_error(11003,'用户名/密码不能为空');
//
//        //登录ID是否为手机号
//        if(isMobile($user_id)) {
//            $where = [
//                'mobile' => $user_id,
//                'user_status' => 1,
//                'user_type' => 2
//            ];
//            $user_info = $user->where($where)->field('id,user_pass')->find();
//            if( empty($user_info)) return $this->output_error(11001,'手机号尚未注册');
//        } elseif(isEmail($user_id)){  //登录ID是否为email
//            $where = [
//                'user_email' => $user_id,
//                'user_status' => 1,
//                'user_type' => 2
//            ];
//        } else{  //登录ID是否为email
//            return $this->output_error(11002,'登录格式错误');
//        }
//
//        //验证用户登录信息
//        $user_info = $user->where($where)->field('id,user_pass')->find();
//        //登录成功
//        if($user_info && cmf_compare_password($password, $user_info->user_pass)) {
//            //产生token
//            $token_info=Token::get($user_info->id);
//            session('user.id', $user_info->id);
//            return $this->output_success(11101,$token_info,'登录成功');
//        } else {         //登录失败
//            return $this->output_error(11001,'登录失败');
//        }
//    }

    /**
     * 退出登录
     * @return array
     */
    public function logout(){
        $this->check_sign();
        $token=input('param.token');
        Token::delete($token);
        Cookie('token',null);
        Cookie('uid',null);
        Cookie('salt',null);
        return $this->output_success(11102,[],'退出成功');
    }

    /**
     * 刷新token
     */
    public function refresh_token(){
        $this->check_sign();
        $token=input('param.token');

        $where=[
            'token'=>$token,
            'status'=>1,
            'update_time'=>['GT',(time()-Config::get('token.expire_time'))],
        ];
        $token_info=db('token')->where($where)->field('id')->find();
        if($token_info){
            $result=Token::refresh($token_info['id'],$token);

            if($result){
                $data=[
                    'token'=>$token,
                    'expire_time'=>Config::get('token.expire_time'),
                ];
                //刷新成功
                return $this->output_success('11103',$data,'Token刷新成功');
            }
        }

        //刷新失败
        return $this->output_error('11004','Token刷新失败');
    }
}
