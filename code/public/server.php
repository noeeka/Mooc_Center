<?php
function https_get($url, $payload)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
    $output = curl_exec($ch);
    curl_close($ch);die;
    if (curl_errno($ch)) {
        return curl_error($ch);
    } else {
        return json_decode($output, true);
    }
}
/**
 * 耗时异步操作
 * @param $url   模板      模块名称/控制器/方法?参数名=参数值
 * */
function  asyncronous($url){
    if(empty($url)){
        return array("flag"=>false,"msg"=>"参数不正确");
    }
    if(strpos($url, "/")==0){
        return array("flag"=>false,"msg"=>"参数格式错误");
    }
    $server=$_SERVER['HTTP_HOST'];
    $fp = fsockopen($server,80,$errno,$errstr,5); 
        if(!$fp){
            return array("flag"=>false,"msg"=>"$errstr ($errno)");
         }
    $out = "GET /$url  / HTTP/1.1\r\n";
    $out .= "Host: $server\r\n";
    $out .= "Connection: Close\r\n\r\n";
    fwrite($fp, $out);
   //忽略执行结果
  //while (!feof($fp)) { echo fgets($fp, 128); }
    fclose($fp);
    return array("flag"=>true,"msg"=>"异步调用成功！");
}
//var_dump(https_get('http://mooc.com/test.php',$_GET));
$a = isset($_GET['a'])? $_GET['a'] : 0;
var_dump(asyncronous('http://mooc.com/test.php?a='.$a));


