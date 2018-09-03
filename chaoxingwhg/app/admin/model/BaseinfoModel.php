<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/2/28
 * Time: 13:23
 */

namespace app\admin\model;


use think\Model;
class BaseinfoModel extends Model
{

    /**
     * post_content 自动转化
     * @param $value
     * @return string
     */
    public function getAboutAttr($value)
    {
        return cmf_replace_content_file_url(htmlspecialchars_decode($value));
    }

    /**
     * post_content 自动转化
     * @param $value
     * @return string
     */
    public function setAboutAttr($value)
    {
        return htmlspecialchars(cmf_replace_content_file_url(htmlspecialchars_decode($value), true));
    }

}