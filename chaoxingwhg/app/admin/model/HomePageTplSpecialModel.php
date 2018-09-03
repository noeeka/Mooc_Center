<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/5/22
 * Time: 18:11
 */

namespace app\admin\model;

use think\Model;

class HomePageTplSpecialModel extends Model
{

    public function getOption($selected = 0, $where = [])
    {
        $all = $this->where($where)->select();
        $html = '';
        foreach ($all as $v) {
            $select1 = $selected == $v['id'] ? 'selected' : '';
            $html .= '<option value="' . $v['id'] . '" ' . $select1 . '>' . $v['name'] . '</option>';
        }
        return $html;
    }
}