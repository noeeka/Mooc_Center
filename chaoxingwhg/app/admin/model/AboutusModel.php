<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/9
 * Time: 16:56
 */

namespace app\admin\model;


use think\Model;

class AboutusModel extends Model
{
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