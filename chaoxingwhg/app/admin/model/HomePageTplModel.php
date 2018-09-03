<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/5/22
 * Time: 15:21
 */

namespace app\admin\model;

use think\Model;

class HomePageTplModel extends Model{

    public function getOption($selected = 0,$type=0)
    {
        $all = $this->where(['type'=>$type])->select();
        $html = '';
        foreach ($all as $v) {
            $select = $v['id'] == $selected ? 'selected' : '';
            $html .= '<option value="' . $v['id'] . '" ' . $select . '>' . $v['name'] . '</option>';
        }
        return $html;
    }

}