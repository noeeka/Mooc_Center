<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/8/23
 * Time: 15:27
 */

namespace app\v1\controller;

use tree\Tree;

class Filter extends Base
{
    public function index()
    {
        $type = input('param.type',3,'intval'); //1 分类  2栏目 3 专题

        if($type == 1){
            //获取分类
            $typeList = $this->getTypeList();
            return $typeList;
        }else if($type == 2){
            //获取栏目
            $columnList = $this->getColumnList();
            return $columnList;
        }else{
            //获取专题
            $speList = $this->getSpeList();
            return $speList;
        }
    }

    public function getTypeList()
    {
        $center_id = input('param.center_id',1,'intval');

        //获取分类的树形结构
        $column_tree = $this->adminTypeTree($center_id);

        if ($column_tree) {
            return $column_tree;
        } else {
            return $this->fail(11000, '获取分类失败');
        }
    }

    private function adminTypeTree($center_id)
    {
        //获取数据
        $typeModel = new \app\v1\model\CourseType();
        $where = [];
        $where['t.status'] = 1;
        $where['t.center_id'] = $center_id;

        $typeList = $typeModel->alias('t')->where($where)->where(['t.parent_id'=>0])->order('t.center_id asc')->field('t.id,t.course_type,t.parent_id')->select();
        if ($typeList) {
            $typeList = \collection($typeList)->toArray();
            $AllTypeList = $typeModel->alias('t')->where($where)->order('t.center_id asc')->field('t.id,t.course_type,t.parent_id')->select(); //所有分类
            $AllTypeList = \collection($AllTypeList)->toArray();
        } else {
            return $this->fail(11001, '获取分类失败');
        }

        //生成树型结构数组
        $tree = new Tree();
        $tree->init($AllTypeList);
        foreach($typeList as $key =>$item){
            $columnArray = $tree->getTreeArray($item['id']);
            $typeList[$key]['children'] = array_values($columnArray);
        }

        return $this->ok($typeList,11102,'获取分类成功');
    }

    /**
     * 栏目列表
     * @param center_token 场馆令牌
     * @param title  标题
     * @param create_time  时间
     * @param page 页数
     * @param len  长度
     * @return array|bool 树形数组
     */
    public function getColumnList()
    {
        $center_id = input('param.center_id',0,'intval');

        //获取栏目的树形结构
        $column_tree = $this->adminColumnTree($center_id);
        if($column_tree['status'] == 0){
            return $column_tree;
        }

        if ($column_tree) {
            return $column_tree;
        } else {
            return $this->fail(11000, '获取栏目失败');
        }
    }

    public function adminColumnTree($center_id)
    {
        $tree = new Tree();
        $columnModel = new \app\v1\model\Column();

        $where = [];
        $where['center_id'] = $center_id;

        //获取父级进行分页
        $columnList = $columnModel->alias('c')->join('mooc_center mc','mc.id=c.center_id')->where(['c.parent_id'=>0])->order(['c.center_id asc','c.list_order asc'])->field('c.id,c.title,c.parent_id')->select();

        if ($columnList) {
            $columnList = \collection($columnList)->toArray();
            $AllColumnList = $columnModel->alias('c')->join('mooc_center mc','mc.id=c.center_id')->order(['c.center_id asc','c.list_order asc'])->field('c.id,c.title,c.parent_id')->select();
            $AllColumnList = \collection($AllColumnList)->toArray();
        } else {
            return $this->fail(11001, '获取栏目失败');
        }

        $tree->init($AllColumnList);

        foreach($columnList as $key =>$item){
            $columnArray = $tree->getTreeArray($item['id']);
            $columnList[$key]['children'] = $columnArray;
        }

        return $this->ok($columnList,11102,'获取栏目成功');
    }

    /**
     * 专题列表
     * @param center_token 场馆令牌
     * @param title  标题
     * @param create_time  时间
     * @param page 页数
     * @param len  长度
     * @return array|bool
     */
    public function getSpeList()
    {
        $center_id = input('param.center_id',1,'intval');

        $where = [];
        if (!empty($title)) {
            $where['s.title'] = ['like', "%$title%"];
        }

        if (!empty($create_time)) {
            $where['s.create_time'] = ['>=', $create_time];
        }

        $where['s.center_id'] = $center_id;

        //数据获取
        $spe_sub = new \app\v1\model\SpecialSubject();
        $join[] = ['mooc_center mc', 'mc.id=s.center_id'];
        $field = 's.id,s.title';
        $speList = $spe_sub->alias('s')->where($where)->field($field)->select();
        
        return $this->ok($speList, 11111, '获取专题成功');

    }
}
