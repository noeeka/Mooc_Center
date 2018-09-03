<?php
namespace app\admin\model;

use think\Db;
use think\Model;

class CollectProductionModel extends Model {
    /**
     * post_content 自动转化
     * @param $value
     * @return string
     */
    public function getPostContentAttr($value)
    {
        return cmf_replace_content_file_url(htmlspecialchars_decode($value));
    }

    /**
     * post_content 自动转化
     * @param $value
     * @return string
     */
    public function setPostContentAttr($value)
    {
        return htmlspecialchars(cmf_replace_content_file_url(htmlspecialchars_decode($value), true));
    }
    /**
     * 作品评论量
     */
    public function comments_count($id){
        return Db::name('production_comments')->where(['zuopin_id'=>$id,'status'=>1,'level_pid'=>0])->count();
    }
    /**
     * 作品点赞量
     */
    public function production_like_count($id){
        return Db::name('like')->where(['resource_id'=>$id,'type'=>5])->count();
    }

}
