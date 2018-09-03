<?php
/**
 * Created by PhpStorm.
 * User: 12563
 * Date: 2018/6/11
 * Time: 15:05
 */

namespace app\admin\model;

use think\Model;

class HomePageLinkModel extends Model
{

    public function typename()
    {
        return $this->hasOne('HomePageLinkTypeModel', 'id', 'type')->field('name');
    }
}