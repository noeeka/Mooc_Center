<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/2/28
 * Time: 10:35
 */

namespace app\admin\controller;


use cmf\controller\AdminBaseController;

use think\Db;

class BaseinfoController extends AdminBaseController
{
    public function baseinfo()
    {
        if($this->request->isPost()){
            $post   = $this->request->post();
            $venue_start = strtotime($post['venue_start']);

            if($venue_start <= 0){
                $this->error("开馆时间错误");
            }

            $data = ['ewm'=>json_encode($post['ewm']),'home_page_logo'=> $post['home_page_logo'], 'second_page_logo'=> $post['second_page_logo'], 'site_title'=>$post['site_title'],'venue_tel'=>$post['venue_tel'],'venue_addr'=>$post['venue_addr'],'copyright'=>$post['copyright'],'pcaccess_count'=>$post['pcaccess_count'],'wxaccess_count'=>$post['wxaccess_count'], 'venue_start'=> $venue_start];

            $result = Db::name('baseinfo')->where('id', 0)->update($data);

            if ($result !== false) {
                $this->success("保存成功！", 'baseinfo');
            }else{
                $this->error("操作失败！");
            }

        }else{
            $site_info = Db::name('baseinfo')
                ->order(["id" => "desc"])
                ->limit(1)->find();
            $site_info['ewm'] = json_decode($site_info['ewm'], true);
            $this->assign([
                'site_info' => $site_info,
            ]);


        }
        return $this->fetch();
    }

}