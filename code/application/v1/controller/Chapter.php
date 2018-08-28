<?php
namespace app\v1\controller;

use app\v1\validate;
use think\Db;

class Chapter extends Base
{
    /**
     * 获取慕课信息
     */
    public function info(){
        $data['center_id']=$this->center_id;
        $data['course_id']=$this->request->param('course_id');
        $data['status']=1;

        $result=Db::query("
                    SELECT course.id, course.course_title, course.cover_img, course.cover_video, course.course_from,course_type.course_type
                    FROM ".$this->table('course')." as course
                    INNER JOIN ".$this->table('center_course')." as center_course ON center_course.center_id=".$data['center_id']." AND center_course.course_id=course.id
                    LEFT JOIN ".$this->table('course_type')." as course_type ON course_type.id=course.course_type_id
                    WHERE course.id=".$data['course_id']."
        ");

        if(empty($result)){
            return $this->fail(21002,'查询内容不存在');
        }else{
            return $this->ok($result,21101,'成功');
        }
    }

    /**
     * 获取所有的慕课列表
     */
    public function all(){
        //文化馆ID
        $center_id=$this->center_id;
        //当前页码
        $page=!empty($this->request->param('page'))&&is_numeric($this->request->param('page'))&&(1<=$this->request->param('page'))?intval($this->request->param('page')):1;
        //请求数量
        $count=!empty($this->request->param('count'))&&is_numeric($this->request->param('count'))&&(1<=$this->request->param('count') && 20>=$this->request->param('count'))?intval($this->request->param('count')):12;
        //排序方式，默认最新（new）
        $order=$this->request->param('order');


        //记录总数
        $total=Db::name('center_course')->where(['center_id'=>$center_id,'status'=>1])->count('id');

        //总页数
        $pages=ceil($total/$count);



        /**
         * 当前分页的记录
         * 创建时间 倒序排列
         * 通过子查询优化limit性能
         * */
        $result=Db::query("
                    SELECT course.id, course.course_title, course.cover_img, course.cover_video, course.course_from,course_type.course_type
                    FROM ".$this->table('course')." as course
                    INNER JOIN ".$this->table('center_course')." as center_course ON center_course.center_id=".$center_id." AND center_course.course_id=course.id
                    LEFT JOIN ".$this->table('course_type')." as course_type ON course_type.id=course.course_type_id
                    WHERE center_course.create_time<=(
                        SELECT center_course_new.create_time
                        FROM ".$this->table('center_course')." as center_course_new
                        WHERE center_course_new.center_id=".$center_id."
                        ORDER BY center_course_new.create_time desc
                        LIMIT ".(($page-1)*$count).",1
                    )
                    AND center_course.status=1
                    ORDER BY center_course.create_time desc
                    LIMIT ".$count."

        ");

        $data=array(
            'data'=>$result,
            'page'=>$page,
            'count'=>$count,
            'pages'=>$pages,
            'total'=>$total,
        );

        return $this->ok($data,21101,'成功');
    }


    /**
     * 创建慕课
     */
    public function add(){
        //$data['mooc_id']            =$this->request->param('mooc_id');
        $data['course_title']       =$this->request->param('course_title');
        $data['course_type_id']  =$this->request->param('course_type_id');
        $data['cover_img']         =$this->request->param('cover_img');
        $data['cover_video']       =$this->request->param('cover_video');
        $data['course_from']      =0;
        $data['content']       =$this->request->param('content');
        $data['status']      =1;

        //老师id
        $course_teacher['teacher_id']=$this->request->param('teacher_id');

        //验证老师信息
        $teacher_info=Db::name('mooc_teacher')->where(['id'=>$course_teacher['teacher_id'],'center_id'=>$this->center_id,'status'=>1])->find();
        if(empty($teacher_info)){
            return $this->fail(21003,'老师ID错误');
        }

        /**  验证课程信息 */
        $result=$this->validate($data,'Course.add');

        if($result!==true){
            return $this->fail(1000,$result,1);
        }else{
            // 启动事务
            Db::startTrans();
            try{
                //添加课程
                Db::name('course')->insert($data);
                $course_id=Db::name('course')->getLastInsID();
                $data['id']=$course_id;
                $course_teacher['course_id']=$course_id;

                //添加课程和文化馆关系
                if($course_id){
                    $center_course_data['center_id']=$this->center_id;
                    $center_course_data['course_id']=$course_id;
                    $center_course_data['status']=1;
                    $center_course_data['create_time']=time();

                    Db::name('center_course')->insert($center_course_data);
                }

                //课程添加老师
                Db::name('course_teacher')->insert($course_teacher);

                // 提交事务
                Db::commit();
                return $this->ok($data,21101,'成功');
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();

                return $this->fail(21001,'失败');

            }
        }

    }



    /**
     * 修改慕课
     * @param course_id
     * @param course_title
     * @param course_type_id
     * @param cover_img
     * @param cover_video
     * @param content
     * @param status
     */
    public function edit(){
        $data=array();
        $data['id']=$this->request->param('course_id');
        if($this->request->param('course_title')){$data['course_title']=$this->request->param('course_title');}
        if($this->request->param('course_type_id')){$data['course_type_id']=$this->request->param('course_type_id');}
        if($this->request->param('cover_img')){$data['cover_img']=$this->request->param('cover_img');}
        if($this->request->param('cover_video')){$data['cover_video']=$this->request->param('cover_video');}
        if($this->request->param('content')){$data['content']=$this->request->param('content');}
        if($this->request->param('status')){$data['status']=$this->request->param('status')==0?0:1;}

        /**  验证课程信息 */
        $result=$this->validate($data,'Course.edit');

        if($result!==true){
            return $this->fail(1000,$result,1);
        }

        /**  验证课程ID */
        $count=Db::name('center_course')->where(['center_id'=>$this->center_id,'course_id'=>$data['id']])->count('id');
        if($count<=0){
            return $this->fail(22006,'非法操作');
        }

        //更新数据
        $result=Db::name('course')->update($data);
        if($result){
            return $this->ok([],20101,'成功');
        }else{
            return $this->fail(22007,"无更新");
        }


    }

    /**
     * 课程单独添加老师
     * @param teacher_id
     * @param course_id
     */
    public function add_teacher(){
        //老师id
        $course_teacher=array();
        $teacher_id=intval($this->request->param('teacher_id'));
        if($teacher_id){
            $course_teacher['teacher_id']=$teacher_id;
            //验证老师信息
            $teacher_info=Db::name('mooc_teacher')->where(['id'=>$course_teacher['teacher_id'],'center_id'=>$this->center_id,'status'=>1])->find();
            if(empty($teacher_info)){
                return $this->fail(21003,'老师ID错误');
            }
        }

        //课程id
        $course_id=intval($this->request->param('course_id'));
        if($course_id){
            $course_teacher['course_id']=$course_id;
            //验证老师信息
            $teacher_info=Db::name('center_course')->where(['course_id'=>$course_id,'center_id'=>$this->center_id,'status'=>1])->find();
            if(empty($teacher_info)){
                return $this->fail(21004,'课程ID错误');
            }
        }

        //添加老师
        $result=Db::name('course_teacher')->insert($course_teacher);
        if($result){
            return $this->ok([],21101,'成功');
        }

    }

    /**
     * 删除老师
     * @param teacher_id
     * @param course_id
     */
    public function del_teacher(){
        $teacher_id=intval($this->request->param('teacher_id'));
        $course_id=intval($this->request->param('course_id'));

        //验证是否能删除该老师
        $center_id=$this->center_id;
        $is_teacher=Db::name('mooc_teacher')->where(['id'=>$teacher_id,'center_id'=>$center_id])->find();
        if(!$is_teacher){
            return $this->fail(22006,'非法操作');
        }

        $result=Db::name('course_teacher')->where(['teacher_id'=>$teacher_id,'course_id'=>$course_id])->delete();
        if($result){
            return $this->ok($result,21101,'成功');
        }else{
            return $this->fail(21001,'失败');
        }
    }

    /**
     * 添加章
     *
     * @param course_id
     * @param chapter_title
     * @param chapter_order
     */
    public  function add_chapter(){
        $data['course_id']=intval($this->request->param('course_id'));
        $data['chapter_title']=$this->request->param('chapter_title');
        $data['chapter_order']=intval($this->request->param('chapter_order'));

        //检查courser_id合法性
        if(!$this->check_course_id($data['course_id'])){
            return $this->fail(22006,'非法操作');
        }

        $result=Db::name('course_chapter')->insert($data);
        if($result){
            $info['chapter_id']=Db::name('course_chapter')->getLastInsID();
            $info['course_id']=$data['course_id'];
            return $this->ok($info,21101,'成功');
        }else{
            return $this->fail(21001,'失败');
        }
    }

    /**
     * 修改章
     */
    public function edit_chapter(){
        $data['course_id']=intval($this->request->param('course_id'));
        $data['id']=$this->request->param('chapter_id');



        //检查courser_id合法性
        if(!$this->check_course_id($data['course_id'])){
            return $this->fail(22006,'非法操作');
        }
    }

    /**
     * 删除章
     *
     * @param course_id
     * @param chapter_id
     */
    public function del_chapter(){
        $data['course_id']=intval($this->request->param('course_id'));
        $data['id']=$this->request->param('chapter_id');

        //检查courser_id合法性
        if(!$this->check_course_id($data['course_id'])){
            return $this->fail(22006,'非法操作');
        }
        $result=Db::name('course_chapter')->where($data)->delete();
        if($result){
            return $this->ok($result,21101,'成功');
        }else{
            return $this->fail(21001,'失败');
        }
    }

    /**
     * 检查course_id合法性
     */
    protected function check_course_id($course_id){
        $result=Db::name('center_course')->where(['center_id'=>$this->center_id,'course_id'=>$course_id])->find();
        if($result){
            return true;
        }else{
            return false;
        }
    }



}
