<?php
include "vendor/workerman/workerman/Autoloader.php";
include "vendor/autoload.php";

use \Workerman\Worker;
use \Workerman\Lib\Timer;


class myRedis {
    static $_instance; //存储对象
    public $handler;
    public function __construct(){
        $this->handler = new \Redis();
        $this->handler->connect('127.0.0.1','6379' , '1');
        //$this->handler->auth('Lideshun123.');
    }
    public static function getInstance()
    {
        if (FALSE == (self::$_instance instanceof self)) {
            //var_dump('实例化了一次');
            self::$_instance = new self;
        }
        return self::$_instance;
    }
}

class log {
    public $logger;
    public $logPath = __DIR__;
    public $logName;
    public function __construct()
    {
        $this->createLog();
        $this->logName = date('Y-m-d') . '.log';
    }

    private function createLog() {
        $this->logPath = $this->logPath.DIRECTORY_SEPARATOR.'log'.DIRECTORY_SEPARATOR;
        if(!is_dir($this->logPath))
            mkdir($this->logPath,0755,true);
    }

    public function logMessage($content,$type = "INFO") {
        $this->logName = date('Y-m-d') . '.log';
        $content = sprintf("log:%s:%s:%s\r\n" , $type , time() , $content);
	var_dump($this->logName);
        file_put_contents($this->logPath.$this->logName, $content , FILE_APPEND);
    }
}


$weedVolume = "http://192.168.1.231:9334";

$worker = new Worker();
$worker->count = 10;
$worker->name = "times";
$worker->onWorkerStart = function($worker) use ($weedVolume ) {


    $time_interval = 2;
    $log = new log();
    Timer::add($time_interval, function() use ($weedVolume , $worker , $log)
    {
      $redis = myRedis::getInstance();
      $weedMaster = new WeedPhp\Client('http://localhost:9333');
      $key = "queue:files";
      $hashKey = $redis->handler->lPop($key);


      if(empty($hashKey))
          return;

      $taskData = $redis->handler->hGetAll($hashKey);
      //var_dump($taskData);

      if(empty($taskData) || !isset($taskData['file_store_key']) || !isset($taskData['filePath'])) {
          var_dump("参数有问题");
          return;
      }

      if(!file_exists($taskData['filePath'])) {
          var_dump("文件不存在");
          return;
      }

      try{
          //先判断图片是否可以上传
          $weedResult = $weedMaster->store($weedVolume , $taskData['file_store_key'] , new \CurlFile( $taskData['filePath']) );
          $weedResult = json_decode($weedResult ,1);
          if(empty($weedResult)) { //上传出错
              $result = array();
              $redis['key'] = $hashKey;
              $redis->handler->rPush("queue:failed:files" , $hashKey);
              $log->logMessage( json_encode($result), "WARNING");
          } else {
              $redis->handler->del($hashKey);
              $weedResult['key'] = $hashKey;
              $log->logMessage(json_encode($weedResult));
          }

      } catch (Exception $e) {
          $result = array();
          $redis['key'] = $hashKey;
          $redis->handler->rPush("queue:failed:files" , $hashKey);
          $log->logMessage( json_encode($result), "WARNING");
      }

      return;
    });
};
Worker::runAll();



