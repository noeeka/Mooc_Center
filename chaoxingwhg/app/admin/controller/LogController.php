<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use app\admin\model\UserModel;
use think\Db;



class LogController extends AdminBaseController
{


    public function index()
    {
        $where = [];
//        /**搜索条件**/
        $user_name = $this->request->param('user_login');
        $user_role = $this->request->param('user_role' );
        $start_time = $this->request->param('start_time' );
        $end_time = $this->request->param('end_time' );
        $object = $this->request->param('object');

        if ($user_name) {
            $where['user_name'] = ['like', "%".$user_name."%"];
        }

        if ($user_role) {
            $where['user_role'] = $user_role;
        }

        if($object) {
            $where['object'] = ['like', "%".$object."%"];
        }

        if($start_time && $end_time) {
            $startTime = empty($start_time) ? 0 : strtotime($start_time);
            $endTime = empty($end_time) ? 0 : strtotime($end_time);
            $where['last_visit_time'] = [
                ['>=', $startTime],
                [ '<=', $endTime]
            ];
        }

        $users = Db::name('user_action_log')
            ->where($where)
            ->order("id DESC")
            ->paginate(10);


       // halt($users->toArray());

        $users->appends(
           ['user_name' => $user_name ,
               "user_role" => $user_role ,
               "start_time" => $start_time,
               "end_time" => $end_time,
               "object" => $object
           ]
        );


        // 获取分页显示
        $page = $users->render();
        if ($users->isEmpty())
            $users = [];
        else
            $users = $users->toArray()['data'];


        foreach ($users as $key => $user) {
            $users[$key]['ip_addr'] = $this->getIpAddr($user['ip']);

        }

        $roles = Db::name('role')->where('status' , 1)->select()->toArray();

        $this->assign("page", $page);
        $this->assign('roles' , $roles);
        $this->assign('start_time', !empty($start_time) ? $start_time : '');
        $this->assign('end_time', !empty($end_time) ? $end_time : '');
        $this->assign('user_role' , empty($user_role) ? 0: $user_role);
        $this->assign("users", $users);
        return $this->fetch();
    }

    public function getIpAddr($ip)
    {
        try{
            if (cache($ip) ) return cache($ip);
            $url = "http://ip.taobao.com/service/getIpInfo.php?ip=".$ip;
            $data = file_get_contents($url);
            $data = json_decode($data,1);
            if(is_array($data) && isset($data['data']['region']) && isset($data['data']['isp'])) {

                cache($ip , $data['data']['region'].$data['data']['isp']);
                return $data['data']['region'].$data['data']['isp'];
            } else
                return "未知";
        } catch (\Exception $e) {
            return "未知";
        }
    }
}