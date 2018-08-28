<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 2018/6/26
 * Time: 12:19
 */
namespace app\v1\model;

use think\Model;

class BaseModel extends Model {
    /**
     * @param $data
     * @param \think\Validate $validator
     * @return mixed
     */
    function saveAndValidate($data, $validator){
        if($validator->check($data)){
            if(false === $this->save($data)){
                return '编辑失败';
            }else{
                return true;
            }
        }else{
            return $validator->getError();
        }
    }
}