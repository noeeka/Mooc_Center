<?php
namespace app\v1\controller;

class Index extends Base
{
    public function index()
    {
        return $this->ok(['vere'=>'1.0.0'],200,'正常');
    }

    public function test(){
        return $this->fail(20001,'failed');
    }
}
