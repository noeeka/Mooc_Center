<?php

/**
 * passport登陆
 */
function passport_login($phone, $pwd)
{
    $url = config('xuexitong.login_url');
    $payload = [
        'name' => $phone,
        'pwd' => $pwd
    ];
    return https_get($url, $payload);
}

/**
 * passport注册
 * @param $phone
 * @param $pwd
 * @return mixed|string
 */
function passport_register($phone, $pwd)
{

    $url = config('xuexitong.register_url');

    $key = config('xuexitong.register_key');
    $time = date('Y-m-d');
    $enc = md5("$phone$time$key");
    $payload = [
        'schoolId' => '0',
        'phone' => $phone,
        'pwd' => $pwd,
        'enc' => $enc,
        'uname' => ""
    ];
    return https_get($url, $payload);
}

function passport_userinfo($uid)
{
    $url = config('xuexitong.userinfo_url');
    $key = config('xuexitong.register_key');
    $enc = md5("$uid$key");
    $payload = [
        'uid' => $uid,
        'enc' => $enc,
        'industry ' => 1
    ];
    return https_get($url, $payload);
}

function passport_userinfo_by_mobile($uname)
{
    $url = config('xuexitong.userinfo_url');
    $key = config('xuexitong.register_key');
    $schoolid = 0;
    $enc = md5("$uname$key$schoolid");
    $payload = [
        'schoolid' => $schoolid,
        'enc' => $enc,
        'uname' => $uname
    ];
    return https_get($url, $payload);
}

function passport_reset_pwd($uid, $pwd){
    $url = config('xuexitong.reset_pwd_url');
    $key = config('xuexitong.register_key');
    $time = date('Y-m-d');
    $enc = md5("$uid$time$key");
    $payload = [
        'id' => $uid,
        'pwd' => $pwd,
        'enc ' => $enc
    ];
    return https_get($url, $payload);
}

/**
 * 通过学习通UID获取本站用户ID
 * 不存在则新增用户
 */
function passport_get_self_uid($phone, $password, $xuexitong_uid)
{
    $user = db('user')->where(['xuexitong_uid' => $xuexitong_uid, 'user_type' => 2])->find();
    if ($user) {
        $data['mobile'] = $phone;
        $data['user_pass'] = cmf_password($password);
        if($user['user_nickname'] == ''){
            $data['user_nickname'] = substr_replace($phone, '****', 3, 4);
        }
        $data['user_login'] = $phone;
        $data['last_login_time'] = time();
        db('user')->where('id', $user['id'])->update($data);
        return $user['id'];
    } else {
        db('user')->insert([
            'mobile' => $phone,
            'user_pass' => cmf_password($password),
            'user_nickname' => substr_replace($phone, '****', 3, 4),
            'last_login_time' => time(),
            'create_time' => time(),
            'user_status' => 1,
            'user_login' => $phone,
            'volun_skill_imgs' => '',
            'xuexitong_uid' => $xuexitong_uid,
            'user_type' => 2]);
        return db('user')->getLastInsID();
    }
}

/**
 * passport端自动登陆
 */
function passport_auto_login($xuexitong_uid)
{
    $token = cookie('token');
    //本平台无登陆token或者登陆token无效,表示需要自动登陆
    if (empty($token) || is_login($token)) {
        //获取用户信息成功则进行自动登陆，否则执行退出登陆操作
        $userinfo = passport_userinfo($xuexitong_uid);
        if ($userinfo['result'] == true) {
            $uid = passport_get_self_uid($userinfo['phone'], '', $xuexitong_uid);
            $user_nickname = db('user')->where('id', $uid)->value('user_nickname', null);
            $tokeninfo = token\Token::get($uid);
            session('user.id', $uid);
            cookie('token', $tokeninfo['token'], $tokeninfo['expire_time']);
            cookie('uid', $user_nickname, $tokeninfo['expire_time']);
            cookie('salt', $tokeninfo['salt'], $tokeninfo['expire_time']);
        }else{
            session('user.id', null);
            cookie('token', null);
            cookie('uid', null);
            cookie('salt', null);
            token\Token::delete($token);
        }
    }
}

function get_forget_url(){
    return  config('xuexitong.forget_url') . '?refer=' . config('server_address') . 'portal/login/login';
}

/**
 * 是否在本平台登陆
 * @param $token
 * @return bool
 */
function is_login($token)
{
    $uid = token\Token::get_user_id($token);
    return $uid > 0;
}

/**
 * 发送https get请求
 */
function https_get($url, $payload)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
    $output = curl_exec($ch);
    if (curl_errno($ch)) {
        return curl_error($ch);
    } else {
        return json_decode($output, true);
    }
}

function parseTextArea($text){
    $text = str_replace(' ','&nbsp;', $text);
    $dataArr = explode(PHP_EOL, $text);
    $html = '';
    foreach($dataArr as $v){
      $html .= '<p>'.$v.'</p>';
    }
    return $html;
}