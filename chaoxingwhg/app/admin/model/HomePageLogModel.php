<?php

namespace app\admin\model;

use app\admin\validate\HomePageLogValidate;
use think\Model;

class HomePageLogModel extends Model
{
    /**
     * 添加日志
     * @param $key tablename;field;id
     * @param $value 值
     * @param int $is_default 是否设为默认值
     * @return array|bool
     */
    public function addlog($key, $value, $is_default = 0)
    {
        list($table_name, $field, $table_pk) = explode(';', $key);
        $lastLog = $this->getLastLog($key);

        if ($lastLog == null || $lastLog->value != $value) {
            //需要新增日志
//            if ($is_default == 1) {
//                $this->where(['table_name' => $table_name, 'field' => $field, 'table_pk' => $table_pk])->update(['is_default' => 0, 'update_time' => time()]);
//            }

            $data = [
                'table_name' => $table_name,
                'field' => $field,
                'table_pk' => $table_pk,
                'value' => $value,
                'is_default' => $is_default,
                'uid' => cmf_get_current_admin_id(),
                'create_time' => time(),
                'update_time' => time()
            ];
            return $this->addDb($data);
        } else {

            //重复数据
            return true;
        }
    }

    /**
     * 设置为默认值
     * @param $key tablename;field;id
     * @param $value 值
     * @return array|bool|string
     */
    public function setDefault($key, $value)
    {
        list($table_name, $field, $table_pk) = explode(';', $key);
        $thisNode = $this->getLastLog($key);
        if ($thisNode == null || $thisNode->value != $value) {
            //新增
            $data = [
                'table_name' => $table_name,
                'field' => $field,
                'table_pk' => $table_pk,
                'value' => $value,
                'is_default' => 1,
                'uid' => cmf_get_current_admin_id(),
                'create_time' => time(),
                'update_time' => time()

            ];
            return $this->addDb($data);
        } else {
            $ret = $this->where('id', $thisNode->id)->update(['is_default' => 1, 'uid' => cmf_get_current_admin_id(), 'update_time' => time()]);
            if (false === $ret) {
                return '设置默认值失败';
            } else {
                return true;
            }
        }
    }

    /**
     * 获取默认日志
     * @param $key tablename;field;id
     */
    public function getDefaultLog($key)
    {
        list($table_name, $field, $table_pk) = explode(';', $key);
        return $this->where(['table_name' => $table_name, 'field' => $field, 'table_pk' => $table_pk])->order(['is_default' => 'desc', 'create_time' => 'desc'])->find();
    }

    /**
     *  获取最后一条日志
     * @param $key tablename;field;id
     */
    private function getLastLog($key)
    {
        list($table_name, $field, $table_pk) = explode(';', $key);

        return $this->where(['table_name' => $table_name, 'field' => $field, 'table_pk' => $table_pk])->order('create_time desc')->find();
    }

    /**
     * 插入数据
     * @param $data
     * @return array|bool
     */

    private function addDb($data)
    {
        $validate = new HomePageLogValidate();
        if ($validate->check($data)) {
            $this->insert($data);
            return true;
        } else {
            return $validate->getError();
        }
    }
}