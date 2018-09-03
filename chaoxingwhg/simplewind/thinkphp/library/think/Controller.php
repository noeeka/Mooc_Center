<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think;

use think\exception\ValidateException;
use traits\controller\Jump;
use redis\Redis;

Loader::import('controller/Jump', TRAIT_PATH, EXT);

class Controller
{
    use Jump;

    /**
     * @var \think\View 视图类实例
     */
    protected $view;

    /**
     * @var \think\Request Request 实例
     */
    protected $request;

    /**
     * @var bool 验证失败是否抛出异常
     */
    protected $failException = false;

    /**
     * @var bool 是否批量验证
     */
    protected $batchValidate = false;

    /**
     * @var array 前置操作方法列表
     */
    protected $beforeActionList = [];

    /**
     * 构造方法
     * @access public
     * @param Request $request Request 对象
     */
    public function __construct(Request $request = null)
    {
        $this->view = View::instance(Config::get('template'), Config::get('view_replace_str'));
        $this->request = is_null($request) ? Request::instance() : $request;

        // 控制器初始化
        $this->_initialize();

        // 前置操作方法
        if ($this->beforeActionList) {
            foreach ($this->beforeActionList as $method => $options) {
                is_numeric($method) ?
                    $this->beforeAction($options) :
                    $this->beforeAction($method, $options);
            }
        }
    }

    /**
     * 初始化操作
     * @access protected
     */
    protected function _initialize()
    {
    }

    /**
     * 前置操作
     * @access protected
     * @param  string $method  前置操作方法名
     * @param  array  $options 调用参数 ['only'=>[...]] 或者 ['except'=>[...]]
     * @return void
     */
    protected function beforeAction($method, $options = [])
    {
        if (isset($options['only'])) {
            if (is_string($options['only'])) {
                $options['only'] = explode(',', $options['only']);
            }

            if (!in_array($this->request->action(), $options['only'])) {
                return;
            }
        } elseif (isset($options['except'])) {
            if (is_string($options['except'])) {
                $options['except'] = explode(',', $options['except']);
            }

            if (in_array($this->request->action(), $options['except'])) {
                return;
            }
        }

        call_user_func([$this, $method]);
    }

    /**
     * 加载模板输出
     * @access protected
     * @param  string $template 模板文件名
     * @param  array  $vars     模板输出变量
     * @param  array  $replace  模板替换
     * @param  array  $config   模板参数
     * @return mixed
     */
    protected function fetch($template = '', $vars = [], $replace = [], $config = [])
    {
        return $this->view->fetch($template, $vars, $replace, $config);
    }

    /**
     * 渲染内容输出
     * @access protected
     * @param  string $content 模板内容
     * @param  array  $vars    模板输出变量
     * @param  array  $replace 替换内容
     * @param  array  $config  模板参数
     * @return mixed
     */
    protected function display($content = '', $vars = [], $replace = [], $config = [])
    {
        return $this->view->display($content, $vars, $replace, $config);
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param  mixed $name  要显示的模板变量
     * @param  mixed $value 变量的值
     * @return $this
     */
    protected function assign($name, $value = '')
    {
        $this->view->assign($name, $value);

        return $this;
    }

    /**
     * 初始化模板引擎
     * @access protected
     * @param array|string $engine 引擎参数
     * @return $this
     */
    protected function engine($engine)
    {
        $this->view->engine($engine);

        return $this;
    }

    /**
     * 设置验证失败后是否抛出异常
     * @access protected
     * @param bool $fail 是否抛出异常
     * @return $this
     */
    protected function validateFailException($fail = true)
    {
        $this->failException = $fail;

        return $this;
    }

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @param  mixed        $callback 回调方法（闭包）
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate($data, $validate, $message = [], $batch = false, $callback = null)
    {
        if (is_array($validate)) {
            $v = Loader::validate();
            $v->rule($validate);
        } else {
            // 支持场景
            if (strpos($validate, '.')) {
                list($validate, $scene) = explode('.', $validate);
            }

            $v = Loader::validate($validate);

            !empty($scene) && $v->scene($scene);
        }

        // 批量验证
        if ($batch || $this->batchValidate) $v->batch(true);
        // 设置错误信息
        if (is_array($message)) $v->message($message);
        // 使用回调验证
        if ($callback && is_callable($callback)) {
            call_user_func_array($callback, [$v, &$data]);
        }

        if (!$v->check($data)) {
            if ($this->failException) {
                throw new ValidateException($v->getError());
            }

            return $v->getError();
        }

        return true;
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

//            var_dump($request_uri);
            trace('debug--'.$token .'|'. $salt .'|' . $request_uri  .'|'. $timestamp .'|'.$sign, 'debug');
            $json = $this->output_error(10004, '请求认证失败');
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}
