<?php
/**
 * Created by PhpStorm.
 * User: 12563
 * Date: 2018/6/11
 * Time: 15:05
 */

namespace app\admin\model;

use think\Model;

class HomePageLinkTypeModel extends Model
{

    public static function option($sid = 0)
    {
        $all = self::all();
        $html = '';
        foreach ($all as $v) {
            $selected = $v['id'] == $sid ? ' selected' : '';
            $html .= '<option value="' . $v['id'] . '"' . $selected . '>' . $v['name'] . '</option>';
        }
        return $html;
    }
}