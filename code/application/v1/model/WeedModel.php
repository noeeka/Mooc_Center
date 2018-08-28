<?php
namespace app\v1\model;

use think\Exception;
use think\Model;
use WeedPhp;

class WeedModel extends Model
{
    public $weedMaster;
    public $weedVolume = "http://192.168.1.231:9334";
    public $redis;
    public function __construct()
    {
        parent::__construct();
        $this->weedMaster = new WeedPhp\Client('http://192.168.1.231:9333');
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1','6379' , '1');
    }

    /**
     * 获取weed服务器的key值
     *
     * @param void
     *
     * @return string key
     */
    public function getFileKey()
    {
        $fileKey = $this->weedMaster->assign();
        file_put_contents("test_ipload",serialize($fileKey),FILE_APPEND);
        $fileKey =  json_decode($fileKey ,1);

        if(empty($fileKey))
            return "";

        $fileKey = $fileKey['fid'];
        return $fileKey;
    }

    /**
     * 创建一个hash结构体储存队列需要的参数
     *
     * @param string $key  结构体的key和队列的key一致
     * @param array  $data
     *
     * @return bool 是否储存成功
     */
    public function createTask($key, array $data)
    {
        $key = "file:".$key;
        $result =  $this->redis->hMset($key , $data);
        if($result)
            return $this->pushQueue($key);

        throw new \LogicException('创建任务失败，请检查是否能链接到图片服务器');
    }

    /**
     * 取出hash结构体
     *
     * @param string $key
     *
     * @return array hash array
     */
    public function getTask($key)
    {
        return $this->redis->hGetAll($key);
    }

    /**
     * 把任务放入队列
     *
     * @param string $hashKey
     *
     * @return bool
     */
    public function pushQueue($hashKey)
    {
        return $this->redis->rPush("queue:files" , $hashKey);
    }

    /**
     * 从队列取出任务
     *
     * @param void
     *
     * @return string hashKey 在用hashkey 拿到结构体找到任务执行
     */
    public function popQueue()
    {
        $key = "queue:files";
        $hashKey = $this->redis->rPop($key);
        return $hashKey;
    }

    /**
     * 获取任务和相关信息
     *
     * @param string $method
     * @param array  $args
     *
     * @return array|bool 如果false代表队列没有任务可以执行，array是需要执行的任务
     */
    public function popQueueWithTask()
    {
        $key = "queue:files";
        $hashKey = $this->redis->lPop($key);

        if($hashKey)
            return $this->redis->hGetAll($hashKey);

        return false;
    }

    public function storeFile($file_store_key , $filePath) {

        if(!file_exists($filePath))
            throw new \LogicException("file not exist");

        try {
            $result = $this->weedMaster->store($this->weedVolume, $file_store_key, new \CurlFile($filePath));
            return $result;
        } catch (Exception $exception) {
            throw new \LogicException($exception->getMessage());
        }
    }

}