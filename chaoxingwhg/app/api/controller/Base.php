<?php

namespace app\api\controller;

use redis\Redis;
use think\Config;
use think\Controller;
use think\Request;
use token\Token;

//use think\Request;
//use think\Config;
//use redis\Redis;
class Base extends Controller
{
    /**
     * 设置请求头
     */
    public function __construct()
    {
        header('Access-Control-Allow-Origin:*');
        header("Access-Control-Allow-Methods", "POST, PUT, OPTIONS");
        header('Content-Type:application/json; charset=utf-8');
    }

    /**
     * 空方法处理
     * @return json
     */
    public function _empty()
    {
        return $this->output_error(10001, '请求不存在');
    }

    /**
     * 权限检查
     */
    protected function check_auth()
    {

    }

    protected function getuid($isSign = true)
    {
        if($isSign){
            $this->check_sign();
        }
        $token = input('param.token');
        return Token::get_user_id($token);
    }

    /**
     * 检查sign是否正常
     * @return mixed
     */
    protected function check_sign()
    {
        //判读请求类型是否为post
        /*if($_SERVER['REQUEST_METHOD']!="POST"){
            $json=$this->output_error(10003,'请求类型错误');
            echo json_encode($json,JSON_UNESCAPED_UNICODE);
            exit;
        };*/
        $token = input('param.token');
        $sign = input('param.sign');
        $timestamp = input('param.timestamp');
        //1. 验证参数是否为空
        //===============
        if (empty($token)) {
            $json = $this->output_error(10005, 'token不能为空');
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
            exit;
        }
        if (empty($sign)) {
            $json = $this->output_error(10006, '签名不能为空');
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
            exit;
        }
        if (empty($timestamp)) {
            $json = $this->output_error(10007, '时间戳不能为空');
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
            exit;
        }

        //2. 验证时间戳是否超时
        //===============
        //2.1 验证请求时间与服务器时间的误差
        $time_now = time();
        $min_time = $time_now - (Config::get('request.over_expire_time')) / 2;
        $max_time = $time_now + (Config::get('request.over_expire_time')) / 2;
        //时间不合法
        if ($timestamp > $max_time || $timestamp < $min_time) {
            $json = $this->output_error(10008, '时间戳错误');
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
            exit;
        }
        //2.2 验证登录状态是否过期
        $redis = Redis::getRedis();
        $token_server_info = $redis->hGetAll('uid_' . $token);
        if ($token_server_info) {
            if (($token_server_info['update_time'] + Config::get('token.expire_time')) < $time_now) {
                $json = $this->output_error(10010, '登录过期');
                echo json_encode($json, JSON_UNESCAPED_UNICODE);
                exit;
            }
        } else {
            $json = $this->output_error(10010, '登录过期');
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
            exit;
        }


        //3. 验证sign是否合法
        //==============
        //3.1 获取sign_server
        $module = strtolower(Request::instance()->module());
        $controller = strtolower(Request::instance()->controller());
        $action = strtolower(Request::instance()->action());
        $request_uri = '/' . $module . '/' . $controller . '/' . $action;
        $salt = $token_server_info['salt'];
        $sign_server = sha1($token . $salt . $request_uri . $timestamp);

        //3.2 查询是否存在相同的sign
        //签名sign过期时间应该等于token过期时间，保证在token生命周期内不会有重复的sign
        $redis = Redis::getRedis();
        $sign_exist = $redis->get('sign_' . $sign);
        if ($sign_exist) {
            $json = $this->output_error(10009, '签名异常');
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
            exit;
        }

        //3.3 对比客户端和服务器端签名是否一致
        if ($sign === $sign_server) {
            //存储sign,方便3.2验证
            $redis->setex('sign_' . $sign, Config::get('token.expire_time'), 1);
            return $token;
        } else {

            var_dump($request_uri);
            trace('debug--'.$token .'|'. $salt .'|' . $request_uri  .'|'. $timestamp .'|'.$sign, 'debug');
            $json = $this->output_error(10004, '请求认证失败');
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    /**
     * 消息输出处理
     * @param $status
     * @param $code
     * @param array $data
     * @param string $msg
     * @return array
     */
    protected function output($status, $code, $data = array(), $msg = '')
    {
        $msg = empty(Config::get('message.' . $code)) ? $msg : Config::get('message.' . $code);
        switch ($status) {
            //error
            case 0:
                $json = [
                    'status' => 0,
                    'code' => $code,
                    'msg' => $msg,
                ];
                break;
            //success
            case 1:
                $json = [
                    'status' => 1,
                    'code' => $code,
                    'data' => $data,
                    'msg' => $msg,
                ];
                break;
            default:
                $json = [
                    'status' => 0,
                    'code' => 10002,
                    'msg' => $msg,
                ];
        }
        return $json;
    }

    /**
     * 成功消息输出
     * @param $code
     * @param array $data
     * @param string $msg
     * @return array
     */
    protected function output_success($code, $data = array(), $msg = '')
    {
        //$msg=empty(Config::get('message.'.$code))?$msg:Config::get('message.'.$code);

        $json = [
            'status' => 1,
            'code' => $code,
            'data' => $data,
            'msg' => $msg,
        ];

        return $json;
    }

    /**
     * 失败消息输出
     * @param $code
     * @param string $msg
     * @return array
     */
    protected function output_error($code, $msg = '')
    {
        // $msg=empty(Config::get('message.'.$code))?$msg:Config::get('message.'.$code);
        $json = [
            'status' => 0,
            'code' => $code,
            'msg' => $msg,
        ];
        return $json;
    }

    function pagination($curpage, $count, $eachpage)
    {
        $retData = array();

        $retData['first_row'] = ($curpage - 1) * $eachpage;
        $retData['end_row'] = $retData['first_row'] + $eachpage;
        $pages = ceil($count / $eachpage);
        $retData['total_pages'] = (int)$pages;

        $html = ' <li><a href="#" data-page="1" aria-label="Previous">首页 </a></li>';
        $page_start = ($curpage == 1) ? $curpage : ($curpage - 1);
        $page_end = $curpage + 5;
        if ($page_end > $pages)
            $page_end = $pages;

        if ($page_start > 1) {
            $html .= "<li  data-page='0' class='disabled' ><a class='disable' data-page='0'>...</a></li>";
        }

        for ($i = $page_start; $i <= $page_end; $i ++) {
            $html = $html."<li ";
            if ($i == $curpage)
                $html .= "class='active'";
            $html .= "><a  href=''  data-page='" . $i . "'>" . $i . "</a></li>";
        }

        if (($curpage + 1) < $pages) {
            $html .= "<li  data-page='0' class='disabled' ><a class='disable' data-page='0'>...</a></li>";
        }

        $html .= '<li><a href="#" data-page="' . $pages . '" aria-label="Next"> 末页 </a></li>';

        $retData['html'] = $html;
        return $retData;

    }
}
