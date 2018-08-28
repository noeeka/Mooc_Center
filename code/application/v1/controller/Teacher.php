<?php
namespace app\v1\controller;

use app\v1\validate;
use think\Db;

class Teacher extends Base
{
    /**
     * 获取老师信息
     *
     *必选参数
     * 单ID：
     * id/1
     * 多ID(以逗号区隔)：
     * id/1,2,3
     *
     *可选参数
     * 状态：
     * status：0
     * status：1
     * 无status
     */
    public function info(){

        $id_str=$this->request->param('id');
        $ids=explode(',',$id_str);

        if(is_numeric($this->request->param('status'))&&$this->request->param('status')>=0){
            $where['status']=$this->request->param('status')==0?0:1;
        }

        $where['id']=['in',$ids];
        $where['center_id']=$this->center_id;


        $result=Db::name('mooc_teacher')->where($where)->field('id,teacher_name,teacher_title,department,center_id,avatar,status')->select();

        if(empty($result)){
            return $this->fail(21002,'查询内容不存在');
        }else{
            return $this->ok($result,21101,'成功');
        }
    }

    /**
     * 获取所有的老师列表
     * 可选参数
     * page：页码 1..
     * count：记录数
     * status：老师状态
     */
    public function all(){
        //文化馆ID
        $center_id=$this->center_id;
        //当前页码
        $page=!empty($this->request->param('page'))&&is_numeric($this->request->param('page'))&&(1<=$this->request->param('page'))?intval($this->request->param('page')):1;
        //请求数量
        $count=!empty($this->request->param('count'))&&is_numeric($this->request->param('count'))&&(1<=$this->request->param('count') && 20>=$this->request->param('count'))?intval($this->request->param('count')):12;

        $where['center_id']=$center_id;

        if(is_numeric($this->request->param('status'))&&$this->request->param('status')>=0){
            $where['status']=$this->request->param('status')==0?0:1;
        }


        //记录总数
        $total=Db::name('mooc_teacher')->where($where)->count('id');
        //总页数
        $pages=ceil($total/$count);


        /**
         * 当前分页的记录
         * 创建时间 倒序排列
         * */

        $result=Db::name('mooc_teacher')->where($where)->field('id,teacher_name,teacher_title,department,center_id,avatar,status')->limit(($page-1)*$count,$count)->order(['id'=>'desc'])->select();

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
     * 创建老师
     * @param teacher_name
     * @param teacher_title
     * @param department
     * @param avatar
     * @param teacher_user_id
     * @param teacher_password
     */
    public function add(){
        $data['teacher_name']      =$this->request->param('teacher_name');
        $data['teacher_title']         =$this->request->param('teacher_title');
        $data['department']         =$this->request->param('department');
        $data['center_id']              =$this->center_id;
        $data['avatar']                   =$this->request->param('avatar');

        $data['teacher_user_id']      =$this->request->param('teacher_user_id');
        $data['teacher_password']      =$this->request->param('teacher_password');
        $data['create_time']           =time();
        $data['status']                    =1;


        /**  验证老师信息 */
        $result=$this->validate($data,'MoocTeacher.add');
        if($result!==true){
            return $this->fail(1000,$result,1);
        }

        $data['teacher_password']=sha1($data['teacher_password']);
        $result=Db::name('mooc_teacher')->insert($data);
        if($result){
            $data['id']=Db::name('mooc_teacher')->getLastInsID();
            return $this->ok($data,20101,'成功');
        }

    }


    /**
     * 修改老师信息
     * @param id
     * @param teacher_name
     * @param teacher_title
     * @param department
     * @param avatar
     * @param status
     */
    public function edit(){
        $data['id']=$this->request->param('id');
        if($data['id']<=0){
            return $this->fail(22003,"老师ID错误");
        }
        if($this->request->param('teacher_name')){$data['teacher_name']=$this->request->param('teacher_name');}
        if($this->request->param('teacher_title')){$data['teacher_title']=$this->request->param('teacher_title');}
        if($this->request->param('department')){$data['department']=$this->request->param('department');}
        if($this->request->param('avatar')){$data['avatar']=$this->request->param('avatar');}
        if($this->request->param('status')){$data['status']=$this->request->param('status')==0?0:1;}



        /**  验证老师信息 */
        $result=$this->validate($data,'MoocTeacher.edit');

        if($result!==true){
            return $this->fail(1000,$result,1);
        }

        $result=Db::name('mooc_teacher')->where(['center_id'=>$this->center_id,'id'=>$data['id']])->update($data);

        if($result){
            return $this->ok([],20101,'成功');
        }else{
            return $this->fail(24004,"该用户不存在");
        }

    }

    /**
     * 修改老师密码
     * @param id
     * @param teacher_password
     */
    public function change_password(){
        $data['id']=$this->request->param('id');
        if($data['id']<=0){
            return $this->fail(24003,"老师ID错误");
        }

        $data['center_id']=$this->request->param('center_id');
        if(empty($data['center_id'])||$data['center_id']<=0){
            return $this->fail(24006,"场馆ID错误");
        }

        $data['teacher_password']=$this->request->param('teacher_password');
        if(!$this->checkPassword($data['teacher_password'])){
            return $this->fail(24005,'密码长度必须是6-20位');
        }

        if($this->user_type<=2){
            return $this->fail(20002,'越权操作');
        }

        Db::name('mooc_teacher')->where(['id'=>$data['id'],'center_id'=>$this->center_id])->update($data);
        return $this->ok([],20101,'成功');
    }

    protected function checkPassword($password){
        $len=strlen($password);
        if($len<6||$len>20){
            return false ;
        }else{
            return true;
        }
    }

    

}
