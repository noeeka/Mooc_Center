<?php
namespace code;
use think\Config;
use think\Db;
use think\Log;
use redis\Redis;

class Code
{
    public static function create($key,$type){
        $redis=Redis::getRedis();
        $code=self::generate_code();
        $store_time=Config::get('verify_code.store_time');
        $expire_time=Config::get('verify_code.expire_time');

        //1. 存储code
        $result=$redis->setex('code_'.$type.'_'.$key,$store_time,$code);
        //2. 设置发送间隔计时器
        //在计时周期内该记录存在
        $timer=$redis->setex('code_expire_'.$type.'_'.$key,$expire_time,time());

        if($result){
            return ['code'=>$code,'store_time'=>$store_time];
        }else{
            return false;
        }
    }

    public static function check($key,$code,$type){
        $code_server=self::get($key,$type);

        if($code_server){
            if($code===$code_server){
                $data=[
                    'status'=>1,
                ];
            }
            else{
                $data=[
                    'status'=>0,
                    'code'=>1001,
                    'msg'=>'验证码错误'
                ];
            }
        }else{
            $data=[
                'status'=>0,
                'code'=>1002,
                'msg'=>'验证码已过期'
            ];
        }
        return $data;
    }

    /**
     * 重设间隔计时器
     * @param $key
     * @param $type
     * @return bool
     */
    public static function reset_expire_timer($key,$type){
        $redis=Redis::getRedis();
        $expire_time=Config::get('verify_code.expire_time');
        $timer=$redis->setex('code_expire_'.$type.'_'.$key,$expire_time,time());
        if($timer){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 检查是否在code发送间隔期内
     * 方法：判断code_expire_$type_$key 值是否存在
     * @param $key
     * @param $type
     * @return bool
     */
    public static function check_expire_time($key,$type){
        $redis=Redis::getRedis();
        $timer=$redis->get('code_expire_'.$type.'_'.$key);
        if($timer){
            return true;
        }else{
            return false;
        }

    }

    public static function get($key,$type){
        $redis=Redis::getRedis();
        $code=$redis->get('code_'.$type.'_'.$key);
        return $code;
    }

   public static function generate_code($length=6){
       $min = pow(10 , ($length - 1));
       $max = pow(10, $length) - 1;
       return rand($min, $max);
   }
}
