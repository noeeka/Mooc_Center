<?php
namespace app\admin\validate;

use think\Validate;

class HomePageLogValidate extends Validate{
    protected $rule = [
        'table_name|表名'=>'require',
        'field|字段名'=>'require',
        'table_pk|唯一标识'=>'require',
        'value|值'=>'require',
    ];
}