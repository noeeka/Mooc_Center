<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 2018/4/26
 * Time: 16:20
 */

function get_site_name(){
    return db('baseinfo')->where('id', 0)->value('site_title', '文化馆');
}
