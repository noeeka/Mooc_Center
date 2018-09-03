<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/14
 * Time: 10:18
 */

namespace app\admin\model;


use think\Model;
use think\Db;

class MienModel extends Model
{
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

    public function setImgsAttr($value){
        return json_encode($value);
    }

    public function getImgsAttr($value){
        $ret = json_decode($value, true);
        return is_array($ret) ? $ret : [];
    }

    /**
     * published_time 自动完成
     * @param $value
     * @return false|int
     */
    public function setPublishTimeAttr($value)
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
}