<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use app\admin\model\RouteModel;
use think\Model;
use think\Db;

class ActivityModel extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    /**
     * post_content 自动转化
     * @param $value
     * @return string
     */
    public function getContentAttr($value)
    {
        return cmf_replace_content_file_url(htmlspecialchars_decode($value));
    }
    /**
     * post_content 自动转化
     * @param $value
     * @return string
     */
    public function setContentAttr($value)
    {
        return htmlspecialchars(cmf_replace_content_file_url(htmlspecialchars_decode($value), true));
    }

    /**
     * published_time 自动完成
     * @param $value
     * @return false|int
     */
    public function setPublishedTimeAttr($value)
    {
        return strtotime($value);
    }
    /**
     * start_time 自动完成
     * @param $value
     * @return false|int
     */
    public function setStartTimeAttr($value)
    {
        return strtotime($value);
    }
    /**
     * end_time 自动完成
     * @param $value
     * @return false|int
     */
    public function setEndTimeAttr($value)
    {
        return strtotime($value);
    }
    /**
     * baoming_start_time 自动完成
     * @param $value
     * @return false|int
     */
    public function setBaomingStartTimeAttr($value)
    {
        return strtotime($value);
    }
    /**
     * baoming_end_time 自动完成
     * @param $value
     * @return false|int
     */
    public function setBaomingEndTimeAttr($value)
    {
        return strtotime($value);
    }


    /**
     * baoming_start_time 自动完成
     * @param $value
     * @return false|int
     */
    public function setSignStartTimeAttr($value)
    {
        return strtotime($value);
    }
    /**
     * baoming_end_time 自动完成
     * @param $value
     * @return false|int
     */
    public function setSignEndTimeAttr($value)
    {
        return strtotime($value);
    }

    /**
     * delete_time 自动完成
     * @param $value
     * @return false|int
     */
    public function setDeleteTimeAttr($value)
    {
        return strtotime($value);
    }

    /**
     * 后台管理添加活动
     * @param array $data 文章数据
     * @param array|string $categories 文章分类 id
     * @return $this
     */
    public function adminAddActivity($data)
    {

        if (!empty($data['thumb'])) {
            $data['thumb'] = cmf_asset_relative_url($data['thumb']);
        }

        if (!empty($data['sign_qrcode'])) {
            $data['sign_qrcode'] = cmf_asset_relative_url($data['sign_qrcode']);
        }

        if($data['max_num'] == ''){
            unset($data['max_num']);
        }


        $this->allowField(true)->data($data, true)->isUpdate(false)->save();
        return $this;
    }

    /**
     * 后台管理编辑活动
     * @param array $data 活动数据
     * @return $this
     */
    public function adminEditActivity($data)
    {

        if (!empty($data['thumb'])) {
            $data['thumb'] = cmf_asset_relative_url($data['thumb']);
        }

        $this->allowField(true)->isUpdate(true)->data($data, true)->save();
        return $this;
    }


    public function getTrafficAttr($value) {
        return cmf_replace_content_file_url(htmlspecialchars_decode($value));
    }

    public function setTrafficAttr($value)
    {
        return htmlspecialchars(cmf_replace_content_file_url(htmlspecialchars_decode($value), true));
    }
    //删除活动发送消息
    static function sendmsg2($ids){
        $id = cmf_get_current_admin_id();
        $activity = new ActivityModel();
        $sysmsg = new SysMessageModel();
        $act_list = $activity->alias('a')->join('activity_baoming b','b.activity_id = a.id')->where('a.id','in',$ids)->field('a.title,a.id,b.user_id')->select()->toArray();
        if (count($act_list)!=0){
            $new_data = [];
            foreach ($act_list as $key => $value){
                $title ='您报名的活动《'.$value['title'].'》已被删除';
                $data = ['title'=>$title,'to_id'=>$value['user_id'],'from_id'=>$id,'create_time'=>time(),'type'=>3,'content'=>'','url'=>'','is_read'=>0];
                $num = $sysmsg->where( ['title'=>$title,'to_id'=>$value['user_id'],'from_id'=>$id,'type'=>3,'content'=>'','url'=>'','is_read'=>0])->count();
                if ($num ==0){
                    $new_data[] =$data;
                }
            }
            $res = $sysmsg->insertAll($new_data);

        }
    }

}
