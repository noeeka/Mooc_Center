<?php
namespace app\v1\controller;

class Error
{
    public function index()
    {
        return $this->_empty();
    }
    public function _empty(){
        return array(
            'code'=>400,
            'status'=>0,
            'data'=>[
                'ver'=>'1.0.0'
            ],
            'msg'=>''
        );
    }
}
