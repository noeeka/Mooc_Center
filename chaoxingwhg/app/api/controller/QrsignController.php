<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/2/28
 * Time: 15:00
 */

namespace app\api\controller;

use think\Db;
use token\Token;

class QrsignController extends Base
{
    //生成uuid
    function generate_sign()
    {
        session('qrcode_timestamp',time());
        $qruuid =substr(md5(uniqid(mt_rand(), true)), 0, 15);//生成uuid
        return $this->output_success('13100', $qruuid, '获取成功');
    }

    //接受处理返回的api
    public function qruser()
    {
        $qruuid = input('qruuid', '', 'string');
        $uid = input('uid', 0, 'intval');

        if (empty($qruuid)) {
            return $this->output_error(18001, '密钥为空');
        }
        if (empty($uid)) {
            return $this->output_error(18002, '用户uid为空');
        }

        $row = Db::name('qrlogin')->where(['qruuid' => $qruuid])->find();
        if ($row) {
            if ($row['status'] == 1) {
                return $this->output_success('13101', [], '已扫描');
            } else {
                $qrexpire=config('qrexpire');//二维码过期时间
                if(($row['addtime']+$qrexpire) > time()){
                    $res = Db::name('qrlogin')->where(['qruuid' => $qruuid])->data(['uid' => $uid, 'status' => 1])->update();
                    if ($res) {
                        $xxt_user = passport_userinfo($uid);
                        $xxt_phone = $xxt_user['phone'];
                        $user_id=passport_get_self_uid($xxt_phone, '', $uid);
                        session('uid',$user_id);
                        return $this->output_success('13101', [], '绑定成功'.$qrexpire);
                    }
                }else{
                    return $this->output_error(18003, '二维码过期'.$qrexpire);
                }
            }
        } else {
            return $this->output_error(18005, '密钥错误');
        }
    }

    //扫码点击登录请求api
    public function api_login()
    {
        $qruuid = input('qruuid', '', 'string');
        $uid = input('uid', 0, 'intval');

        if (empty($qruuid)) {
            return $this->output_error(18001, '密钥为空');
        }
        if (empty($uid)) {
            return $this->output_error(18002, '用户uid为空');
        }

        $row = Db::name('qrlogin')->where(['qruuid' => $qruuid, 'uid' => $uid, 'status' => 1])->find();

        if ($row) {
            $qrexpire=config('qrexpire');//二维码过期时间
            if(($row['addtime']+$qrexpire) > time()){
                Db::name('qrlogin')->where(['qruuid' => $qruuid, 'uid' => $uid, 'status' => 1])->data(['is_login'=>1])->update();

                //记录用户登录足迹
                $yaoqingma='';
                $whgname='';

                $payload=['yaoqingma'=>$yaoqingma,'whgname'=>$whgname];
                https_get('https://demo-szwhg.chaoxing.com/api/footprint',$payload);


                return $this->output_success('13102', [], '绑定成功1');
            }else{
                return $this->output_error(18003, '二维码已过期');
            }
        } else {
            return $this->output_error(18005, '未绑定');
        }
    }
    //检测是否已登录
    public function check_login()
    {
        $qruuid = session('qruuid');
        if (empty($qruuid)) {
            return $this->output_error(18001, '密钥已过期');
        } else {
            $uid = Db::name('qrlogin')->where(['qruuid' => $qruuid])->value('uid');
            if (!empty($uid)) {
                $user_id=Db::name('user')->where(['xuexitong_uid'=>$uid])->value('id');
                $token_info = Token::get($user_id);
                $is_login= Db::name('qrlogin')->where(['qruuid' => $qruuid, 'uid' => $uid, 'status' => 1])->value('is_login');
                $token_info['is_login']=$is_login;
                $xxt_user = passport_userinfo($uid);
                $token_info['phone']=$xxt_user['phone'];
                return $this->output_success('13101', $token_info, '登录成功');
            } else {
                return $this->output_error(18001, '未绑定');
            }
        }
    }

    //生成不带logo二维码
    public function qrcode(){

        $qruuid =input('param.qruuid');
        session('qruuid', $qruuid);


        if(!empty($qruuid)){
            Db::name('qrlogin')->data(['qruuid' => $qruuid,'addtime'=>time()])->insert();

            vendor("phpqrcode.phpqrcode");
            $value = config('server_address').'qrcode.html?sign=' . $qruuid ; //二维码内容
            $errorCorrectionLevel = 'L';//容错级别
            $matrixPointSize = 6;//生成图片大小
            //生成二维码图片
            \QRcode::png($value,false, $errorCorrectionLevel, $matrixPointSize, 2);
        }else{
            return $this->output_error(18001, 'sign不存在');
        }

    }

    //生成带logo二维码
    public function qrcode_logo()
    {
        $qruuid =input('param.qruuid');
        session('qruuid', $qruuid);
        Db::name('qrlogin')->data(['qruuid' => $qruuid])->insert();

        vendor("phpqrcode.phpqrcode");
        $value = 'https://demo-szwhg.chaoxing.com/qrcode.html?sign=' . $qruuid ; //二维码内容
        $errorCorrectionLevel = 'L';//容错级别
        $matrixPointSize = 6;//生成图片大小
        //生成二维码图片
        \QRcode::png($value, 'qrcode.png', $errorCorrectionLevel, $matrixPointSize, 2);
        $logo = 'logo.png';//准备好的logo图片
        $QR = 'qrcode.png';//已经生成的原始二维码图

        if ($logo !== FALSE) {
            $QR = imagecreatefromstring(file_get_contents($QR));
            $logo = imagecreatefromstring(file_get_contents($logo));
            $QR_width = imagesx($QR);
            $QR_height = imagesy($QR);
            $logo_width = imagesx($logo);
            $logo_height = imagesy($logo);
            $logo_qr_width = $QR_width / 5;
            $scale = $logo_width / $logo_qr_width;
            $logo_qr_height = $logo_height / $scale;
            $from_width = ($QR_width - $logo_qr_width) / 2;
            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
        }
        ob_start();
        // 输出图像
        imagepng($QR);
        $content = ob_get_clean();
        imagedestroy($QR);

        return response($content, 200, ['Content-Length' => strlen($content)])->contentType('image/png');
    }
}