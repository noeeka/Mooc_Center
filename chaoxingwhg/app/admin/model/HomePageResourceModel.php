<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/5/22
 * Time: 16:27
 */

namespace app\admin\model;

use think\Model;

class HomePageResourceModel extends Model
{

    public function getOption($selected = 0)
    {
        $all = $this->select();
        trace('debug-'.json_encode($all), 'debug');
        $html = '';
        foreach ($all as $v) {
            $select = $v['id'] == $selected ? 'selected' : '';
            $html .= '<option value="' . $v['id'] . '" ' . $select . '>' . $v['name'] . '</option>';
        }
        return $html;
    }
}