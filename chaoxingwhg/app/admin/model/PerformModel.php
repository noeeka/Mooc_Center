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

class PerformModel extends Model
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

    public function setCreateTimeAttr($value){
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

        return $this->validate(true)->allowField(true)->data($data, true)->isUpdate(false)->save();
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

}
