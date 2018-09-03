<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/2/28
 * Time: 16:06
 */

namespace app\admin\model;


use think\Model;

class VenueModel extends Model
{
    static function getOptionList($ids, $selectId = 0)
    {
        $list = self::where(['id' => ['in', $ids]])->select()->toArray();
        $html = '<option value="0">全部</option>';
        foreach ($list as $v) {
            $selected = $selectId == $v['id'] ? ' selected ' : '';
            $html .= '<option value="' . $v['id'] . '"' . $selected . '>' . $v['name'] . '</option>';
        }
        return $html;
    }

    static function getHot($page, $len){
        return db('venue_tongji')->alias('a')
            ->join('__VENUE__ v', 'a.venue_id= v.id')
            ->field('v.*,hot')
            ->where(['v.status' => 1])
            ->order('a.hot desc')
            ->group('v.id')
            ->page($page, $len)
            ->select();
    }


    public function getTrafficAttr($value)
    {
        return cmf_replace_content_file_url(htmlspecialchars_decode($value));
    }


    /**
     * post_content 自动转化
     * @param $value
     * @return string
     */
    public function setTrafficAttr($value)
    {
        return htmlspecialchars(cmf_replace_content_file_url(htmlspecialchars_decode($value), true));
    }
}