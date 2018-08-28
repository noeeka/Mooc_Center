<?php

namespace app\v1\behavior;
use think\response\Json as JsonResponse;
use think\Request;

class Auth
{
    public function appInit(&$params)
    {
        dump($params);
    }

    public function appEnd(&$params)
    {

    }

    public function moduleInit(&$params)
    {
        /*dump($params->param('token'));


        $json=new JsonResponse();

        $data=array(
            'code'=>500,
            'msg'=>'aaa',
        );

        $json_response=new JsonResponse($data,200);
        //$json_response->send();

        //echo json_encode($data);

        //die;*/
    }


}