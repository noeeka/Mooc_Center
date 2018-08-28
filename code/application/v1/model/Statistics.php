<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/8/13
 * Time: 11:23
 */

namespace app\v1\model;

use think\Model;

class Statistics extends Model
{
    /**
     * 获取慕课列表或数量
     * @param bool $isCount true 获取数量 false 获取列表
     * @return array|int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCourseList($centerId,$isCount = false)
    {
//        $centerId = input('param.centerIid', 0, 'intval');     //真实场馆id
        $center_id = input('param.center_id', 0, 'intval');  //超星馆下筛选条件 场馆id
        $creator_id = input('param.creator_id', 0, 'intval');
        $creator_type = input('param.creator_type', 0, 'intval');
        $type = input('param.type', 0, 'intval');
        $title = input('param.title', '');
        $page = input('param.page', 0, 'intval');
        $len = input('param.len', 10, 'intval');
        $has_delete = input('param.has_delete', 0, 'intval');
        $status = input('param.status', -1, 'intval');  //前台必传1
        $recommend = input('param.recomment', 0, 'intval');

        $where = [];


        if ($centerId && $centerId != 1) $where = ['cc.center_id' => $centerId];
        if ($status != -1) $where = ['cc.status' => $status];
        if ($center_id != 0) $where['cc.center_id'] = $center_id;
        if ($creator_id != 0) {
            $where['c.type'] = $creator_type;
            $where['c.creator_id'] = $creator_id;
        }
        if ($type != 0) $where['ct.other_id'] = $type;
        if ($title != '') $where['c.course_title'] = ['like', '%' . $title . '%'];
        if ($has_delete == 0) $where['c.delete_time'] = 0;
        if ($recommend) $where['recomment'] = 1;
        $query = $this->_query($where);
        if ($isCount) {
            return $query->count(1);
        } else {
            $result = $query->order(['cc.recommend' => 'desc', 'cc.create_time' => 'desc'])->page($page, $len)->select();

            if ($result) {
                return \collection($result)->toArray();
            } else {
                return [];
            }
        }
    }

    /**
     * 构造慕课查询器
     * @param $where
     * @return CourseModel
     */
    private function _query($where)
    {
        //场馆ID关联时
        return ( new Course())
            ->alias('c')
            ->join('__CENTER_COURSE__ cc', 'c.id=cc.course_id')
            ->join('__COURSE_RELA__ ct', 'c.id=ct.course_id and ct.type = 1 and ct.center_id = cc.center_id','left')
            ->join('__COMMENT__ cm', 'cc.course_id=cm.course_id and cm.center_id = cc.center_id', 'left')
            ->field('c.*,cc.id as real_id,cc.recommend,cc.create_time as add_time,cc.center_id,cc.play_num,cc.status, ct.other_id,count(cm.id) as comment_num,avg(cm.practical_score+cm.concise_score+cm.clear_score)/3 as score')
            ->where($where)
            ->group('cc.id');

    }

    /**
     * 查询结果整理
     * @param $data
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function prepareData($data,$detail=false)
    {
        $data['course_id'] = $data['id'];
        $data['id'] = $data['real_id'];
        unset($data['real_id']);
        $data['cover_img'] = get_image_url($data['cover_img']);
        $data['cover_video'] = get_image_url($data['cover_video']);
        $data['create_time'] = $data['add_time'];
        unset($data['add_time']);
        $data['content'] = $data['content'] ? $data['content'] : '';
        $center = MoocCenter::get($data['center_id']);
        $data['center_name'] = $center ? $center['center_name'] : '';
        $data['score'] = $data['score'] == null ? 0 : $data['score'];
        $type = CourseType::get($data['other_id']);
        $data['course_type'] = $type ? $type['course_type'] : '';
        if ($data['type'] == 1) {
            $data['creator'] = MoocCenter::where(['id' => $data['creator_id']])->value('center_name', '');
            $data['creator_center_id'] = $data['creator_id'];
        } else {
            $user = MoocUser::where(['id' => $data['creator_id']])->find();
            if ($user) {
                $data['creator'] = $user['nick_name'];
                $data['creator_center_id'] = $user['center_id'];
            } else {
                $data['creator'] = '';
                $data['creator_center_id'] = 0;
            }
        }
        //查找课程对应报名数量
        $data['baoming_num'] = (new Baoming())->where(['course_id'=>$data['course_id'],'center_id'=>$data['center_id']])->count(1);
        //查找课程对应的老师数量
        $data['teachers'] =(new CourseRela())->where(['course_id'=>$data['course_id'],'center_id'=>$data['center_id'],'type'=>3])->count(1);
        //获取课程章节数量
        $data['chapter_num'] = (new Chapter())->where(['course_id'=>$data['course_id']])->count(1);
        //问答数
        $question_id= (new Question())->where(['course_id'=>$data['course_id'],'center_id'=>$data['center_id']])->column('id');
        $answer_num = (new Answer())->where(['question_id'=>['in',$question_id]])->count(1);
        $data['qa_num'] = count($question_id)+$answer_num;
        //笔记数
        $note_ids = (new SectionNote())->where(['course_id'=>$data['course_id'],'center_id'=>$data['center_id']])->column('id');
        $note_rely_num = (new SectionNoteReply())->where(['note_id'=>['in',$note_ids]])->count(1);
        $data['note_num'] = count($note_ids)+$note_rely_num;

        //获取课程详情（老师，学生，问答，笔记，评论详情等）
        if($detail){
            //获取课程教师团队
            $tea_team= (new CourseRela())
                ->alias('cr')
                ->join('__MOOC_USER__ mu','mu.id=cr.other_id')
                ->where(['cr.course_id'=>$data['course_id'],'cr.type'=>3])
                ->field('mu.id,mu.nick_name,mu.profile')
                ->select();
            $tea = [];
            foreach($tea_team as $item){
                $tea[$item['id']]['name'] = $item['nick_name'];
                $tea[$item['id']]['profile'] = $item['profile'];
            }

            $data['teacher_team'] = $tea;

            //获取课程下的学生
//            $data['stu'] = (new Baoming())->alias('b')->join('__MOOC_USER__ mu','mu.id=b.user_id')->where(['center_id'=>$data['center_id'],'course_id'=>$data['course_id']])->column('nick_name');

            //获取该课程所在场馆的所有用户
            $where = [];
            if($data['center_id'] != 1){
                $where['center_id'] = $data['center_id'];
            }
            $userModel = new MoocUser();
            $userList = $userModel->where($where)->field('id,nick_name')->select();
            if ($userList) {
                $userList = \collection($userList)->toArray();
            } else {
                $userList = [];
            }

            //获取课程下的问答详情
            $questionModel = (new Question());
            $qa_detail = $questionModel
                ->alias('q')
                ->join('__ANSWER__ a','a.question_id=q.id','left')
                ->join('__MOOC_USER__ mu','mu.id=q.user_id','left')
                ->where(['q.course_id'=>$data['course_id'],'q.center_id'=>$data['center_id']])
                ->field('q.id ,q.content,mu.nick_name,a.id as answer_id,a.content as answer_content,a.user_id as reply_user')
                ->select();
            if ($qa_detail) {
                $qa_detail = \collection($qa_detail)->toArray();
            } else {
                $qa_detail = [];
            }

            $qa_det = [];
            foreach ($qa_detail as $v){
                foreach ($userList as $item){
                    if($v['reply_user'] == $item['id']){
                        $v['reply_nick_name'] = $item['nick_name'];
                    }
                }
                $qa_det[$v['id']]['content'] = $v['content'];
                $qa_det[$v['id']]['nick_name'] = $v['nick_name'];
                if($v['reply_id'] == !null){
                    $qa_det[$v['id']]['children'][$v['answer_id']] = [
                        'nick_name'=>$v['reply_nick_name'],
                        'content'=>$v['answer_content']
                    ];
                }else{
                    $qa_det[$v['id']]['children'] = [];
                }

            }
            foreach ($qa_det as $k=>$item){
                $qa_det[$k]['children'] = array_values($item['children']);
            }
            $data['qa_detail'] = array_values($qa_det);

            //获取笔记详情
            $note = (new SectionNote())
                ->alias('s')
                ->join('__SECTION_NOTE_REPLY__ nr','nr.note_id=s.id','left')
                ->join('__MOOC_USER__ mu','mu.id=s.user_id','left')
                ->where(['s.course_id'=>$data['course_id'],'s.center_id'=>$data['center_id']])
                ->field('s.id ,s.content,mu.nick_name,nr.id as reply_id,nr.content as reply_content,nr.user_id as nr_user')
                ->select();
            if ($note) {
                $note = \collection($note)->toArray();
            } else {
                $note = [];
            }

            $note_det = [];
            foreach($note  as $v){
                foreach($userList as $item){
                    if($v['nr_user'] == $item['id']){
                        $v['nr_nick_name'] = $item['nick_name'];
                    }
                }
                $note_det[$v['id']]['content'] = $v['content'];
                $note_det[$v['id']]['nick_name'] = $v['nick_name'];
                if($v['reply_id'] == !null){
                    $note_det[$v['id']]['children'][$v['reply_id']] = [
                        'nick_name' => $v['nr_nick_name'],
                        'content'=>$v['reply_content']
                    ];
                }else{
                    $note_det[$v['id']]['children'] = [];
                }

            }

            $data['note_detail'] = $note_det;
            //获取慕课评论
            $comment = (new Comment())
                ->alias('c')
                ->join('__MOOC_USER__ mu','mu.id=c.user_id')
                ->where(['c.course_id'=>$data['course_id'],'c.center_id'=>$data['center_id']])
                ->field('c.id ,c.content,mu.nick_name')
                ->select();
            $comment_detail = [];
            foreach($comment as $key=>$item){
                $comment_detail[$key]['id'] = $item['id'];
                $comment_detail[$key]['content'] = [
                    'content' =>$item['content'],
                    'nick_name'=>$item['nick_name']
                ];
            }
            $data['comment'] = $comment_detail;
            //获取课程对应的目录
            $chapter_section = (new Chapter())
                ->alias('c')
                ->join('__SECTION__ s', 'c.id = s.chapter_id and s.status = 1', 'left')
                ->where('c.course_id', $data['course_id'])
                ->field('c.*,s.id as section_id,section_title,s.list_order as slist_order')
                ->order(['c.list_order' => 'asc', 's.list_order' => 'asc'])
                ->select();
            if ($chapter_section) {
                $chapter_section = \collection($chapter_section)->toArray();
            } else {
                $chapter_section = [];
            }
            $result = [];
            foreach ($chapter_section as $k => $v) {
                $result[$v['id']]['title'] = $v['chapter_title'];
                $result[$v['id']]['id'] = $v['id'];
                $result[$v['id']]['expand'] = true;
                $result[$v['id']]['children'][$v['section_id']] = [
                    'id' => $v['section_id'],
                    'title' => $v['section_title'],
                    'children' => []
                ];
            }
            foreach ($result as $k => $v) {
                $result[$k]['children'] = array_values($v['children']);
            }
            //对象转数组
            $data['chapter_section'] = array_values($result);
        }

        return $data;
    }


    public function read()
    {
        $where['c.delete_time'] = 0;
        $id = input('param.id', 2, 'intval');

        if($id != 0){
            $where['cc.id'] = $id;
        }else{
            return fail(21001,'id必须');
        }

        $data = $this->_query($where)->find();
        if ($data) {
            $data = $data->toArray();
        } else {
            $data = [];
        }

        return $data;

    }

}