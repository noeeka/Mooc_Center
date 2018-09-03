<?php

namespace app\admin\model;

use think\Model;

class OpinionIdeaModel extends Model
{
    /**
     * 获取idea的分页
     *
     * @param array $params[] where条件
     * @param int $paginate 分页跨度
     *
     * @return array
     */
    public function getOpinionIdeaList(array $params, $paginate = 10)
    {
        $where = [];
        $where['is_delete'] = 1;

        if (isset($params['opinion_id']) && !empty($params['opinion_id']))
            $where['opinion_id'] = $params['opinion_id'];

        $ideaList  = $this->where($where)->order('create_time DESC')->paginate($paginate);
        // 获取分页显示
         //var_dump($this->getLastSql());
        return $ideaList;
    }
}
