<?php
/**
 * Created by PhpStorm.
 * User: zyf
 * Date: 2018/6/11
 * Time: 15:07
 */
namespace app\admin\model;

use think\Model;

class ProductionCollectAwardModel extends Model{

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

}