<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/7/30
 * Time: 10:23
 */

namespace app\v1\controller;

use app\v1\model\Like;
use app\v1\model\MoocUser;
use app\v1\model\Section;
use app\v1\model\SectionNote as SectionNotes;
use app\v1\model\Chapter;
use app\v1\model\SectionNoteReply;

class SectionNote extends Base
{
    /**
     * 笔记列表(某一节的课程列表)
     * @return array|bool
     */
    public function index()
    {
        $section_id = input('param.section_id', 0, 'intval');
        $center_id = input('param.center_id',0,'intval');
        $all = input('param.all',-1,'intval');
        $page = input('param.page',1,'intval');
        $len = input('param.len',10,'intval');
        $has_delete = input('param.has_delete',0,'intval');

        $noteModel = new SectionNotes();
        $where = [];
            $where['n.section_id'] = $section_id;

        if($all != -1){
            if($center_id != 1){
                $where['n.center_id'] = 1;
            }
        }else{
            $where['n.center_id'] = 1;
        }
        if($has_delete != 0){
            $where['n.delete_time'] = 0;
        }

        $noteList = $noteModel
            ->alias('n')
            ->join('mooc_user mu', 'mu.id=n.user_id')
            ->join('section s','s.id=n.section_id','left')
            ->join('chapter c','c.id=s.chapter_id','left')
            ->join('comment cm','cm.course_id=c.course_id and cm.center_id=n.center_id','left')
            ->where($where)
            ->field('n.id,n.section_id,n.create_time,n.like_num,n.content,s.section_title,c.chapter_title,mu.nick_name,mu.avatar,count(cm.id) as comment_num')
            ->group('n.id')
            ->page($page,$len)
            ->select();

        return $this->ok($noteList, 20101, '获取笔记成功');

    }

    /**
     * 笔记列表(某一节的课程列表)
     * @return array|bool
     */
    public function noteList(){
        $course_id = input('param.course_id', 2, 'intval');
        $center_id = input('param.center_id',0,'intval');
        $all = input('param.all',-1,'intval');
        $page = input('param.page',1,'intval');
        $len = input('param.len',10,'intval');
        $has_delete = input('param.has_delete',0,'intval');

        $noteModel = new SectionNotes();
        $where = [];
        $where['n.course_id'] = $course_id;

        if($all != -1){
            if($center_id != 1){
                $where['n.center_id'] = 1;
            }
        }else{
            $where['n.center_id'] = 1;
        }
        if($has_delete != 0){
            $where['n.delete_time'] = 0;
        }

        $noteList = $noteModel
            ->alias('n')
            ->join('mooc_user mu', 'mu.id=n.user_id')
            ->join('section s','s.id=n.section_id','left')
            ->join('chapter c','c.id=s.chapter_id','left')
            ->join('comment cm','cm.course_id=c.course_id and cm.center_id=n.center_id','left')
            ->where($where)
            ->field('n.id,n.section_id,n.create_time,n.like_num,n.content,s.section_title,c.chapter_title,mu.nick_name,mu.avatar,count(cm.id) as comment_num')
            ->group('n.id')
            ->page($page,$len)
            ->select();
//        foreach ($noteList as $key => $item) {
//            if ($item['type'] == 2) {
//                $noteList[$key]['collected_user_nickname'] = (new MoocUser())->where(['id' => $item['collect_from']])->value('nick_name');
//            }
//        }

        return $this->ok($noteList, 20101, '获取笔记成功');
    }

    /**
     * 笔记详情
     * @param question_id 笔记id
     * @return array|bool
     */
    public function read()
    {
        $note_id = input('param.note_id', 1, 'intval');
        $type = input('param.type',0,'intval'); //后台1 前台0

        $noteModel = new SectionNotes();
        $noteReplyModel = new SectionNoteReply();

        $note = $noteModel
            ->alias('n')
            ->join('mooc_user mu', 'mu.id=n.user_id')
            ->join('section s','s.id=n.section_id','left')
            ->join('chapter c','c.id=s.chapter_id','left')
            ->join('course cs','cs.id=chapter_id','left')
            ->where(['n.id' => $note_id,'n.delete_time'=>0])
            ->field('n.id,n.section_id,n.user_id,n.create_time,n.like_num,n.content,n.collect_num,s.section_title,c.chapter_title,c.id as chapter_id,mu.nick_name,mu.avatar,cs.course_title,cs.id as course_id')
            ->find();
        $noteModel->where('id', $note_id)->setInc('page_view');
        if($type == 0){
            $note['follow'] = $this->is_follow($note['user_id']);
        }

        if ($note === null) {
            return $this->fail(12010, '笔记不存在');
        }


        $replyList = $noteReplyModel
            ->alias('nr')
            ->join('mooc_user mu', 'mu.id=nr.user_id')
            ->where(['nr.note_id' => $note_id,'nr.delete_time'=>0])
            ->field('nr.*,mu.nick_name,mu.avatar')
            ->select();

        return $this->ok(['note'=>$note,'replyList'=>$replyList], 12110, '获取笔记成功');
    }

    /**
     * 两者是否相互关注
     */
    public function is_follow($note_user_id){
        $user_token = input('param.user_token','','trim');

        if($user_token == ''){
            return 0;
        }

        //签名校验
        $msg = verify();
        if ($msg['status'] == 0) {
            return $msg;
        } else {
            $user_id = $msg['data']['user_id'];
        }

        $is_follow = (new \app\v1\model\Follow())->where(['user_id'=>$note_user_id,'follow_id'=>$user_id])->count(0);

        return $is_follow;

    }

    /**
     * 添加笔记
     * @param user_token 用户令牌
     * @param course_id 课程id
     * @param section_id 节id
     * @param content 笔记内容
     * @return array|bool
     */
    public function create()
    {
        $_GET['user_token'] = 'cd69089bea5af15192e10ce17be7d4939eb02bb7';
        $_GET['timestamp'] = time();
        $salt = 'ttVm5';
        $_GET['sign'] = encrypt_key(['v1/sectionnote/create', $_GET['timestamp'], $_GET['user_token'], $salt], '');
        $_GET['content'] = '笔记123456笔记笔记笔记';
        $_GET['course_id'] = 2;
        $_GET['section_id'] = 2;


//        $_GET['user_token'] = 'b616164e6ce937e8debb6345783a9746ebcd1e5c';
//        $_GET['course_id'] = 2;
//        $_GET['section_id'] = 2;
//        $_GET['content'] = '撒酒疯的咯军事家欸哦推荐独家试爱付款了';
//        $_GET['timestamp'] = time();
//        $salt = 'qSccu';
//        $_GET['sign'] = encrypt_key(['v1/sectionnote/create', $_GET['timestamp'], $_GET['user_token'], $salt], '');

        $user_token = input('param.user_token', '', 'trim');
        $course_id = input('param.course_id', 0, 'intval');
        $section_id = input('param.section_id', 0, 'intval');
        $content = input('param.content', '', 'trim');

        //用户校验
        $tokenRes = checkUserToken($user_token);
        if ($tokenRes !== true) {
            return $tokenRes;
        }

        //数据校验
        if (empty($course_id)) {
            return $this->fail(20020, '课程id必须');
        }

        if (empty($section_id)) {
            return $this->fail(20021, '节id必须');
        }

        if (empty($content)) {
            return $this->fail(20022, '笔记内容必须');
        }

        //校验课程与章节的关联
        $sectionModel = new Section();
        $chapter_id = $sectionModel->where(['id' => $section_id])->value('chapter_id');
        $courseId = (new Chapter())->where(['id' => $chapter_id])->value('course_id');

        if ($course_id != $courseId) {
            return $this->fail(20023, '此课程下没有此章节');
        }

        //数据整理
        $userModel = new MoocUser();
        $selNoteModel = new SectionNotes();
        $user = $userModel->where(['user_token' => $user_token])->find();
        $user_id = $user['id'];
        $center_id = $user['center_id'];
        $data = [
            'center_id' => $center_id,
            'course_id' => $course_id,
            'section_id' => $section_id,
            'user_id' => $user_id,
            'content' => $content,
            'collect_from' => $user_id,
            'create_time' => time()
        ];

        if ($selNoteModel->save($data) > 0) {
            return $this->ok('', 20130, '添加笔记成功');
        } else {
            return $this->fail(20031, '添加笔记失败');
        }
    }


    /**
     * 删除笔记
     * @param user_token 用户令牌
     * @param $note_id 笔记id
     * @return array|bool
     */
    public function delete()
    {
        $_GET['user_token'] = 'b0ce7a263c01dfa88c681b3eba8d99a0b0554c0c';
        $_GET['timestamp'] = time();
        $salt = 'UM9Cg';
        $_GET['sign'] = encrypt_key(['v1/sectionnote/delete', $_GET['timestamp'], $_GET['user_token'], $salt], '');
        $_GET['id'] = 7;

        $user_token = input('param.user_token', '', 'trim');
        $note_id = input('param.id', 0, 'intval');

        //令牌校验
        $tokenRes = checkUserToken($user_token);
        if ($tokenRes !== true) {
            return $tokenRes;
        }

        //校验笔记是否属于此用户
        $userModel = new MoocUser();
        $noteModel = new SectionNotes();
        $user = $userModel->where(['user_token' => $user_token])->find();
        $note = $noteModel->where(['id' => $note_id])->find();
        if ($note === null) {
            return $this->fail(20040, '笔记不存在');
        }
        if ($note['user_id'] != $user['id']) {
            return $this->fail(20041, '笔记不属于此用户，没有权限删除');
        }

        if ($noteModel->where('id', $note_id)->update(['delete_time'=>time()]) !== false) {
            return $this->ok('', 20142, '删除成功');
        } else {
            return $this->fail(20043, '删除失败');
        }
    }

    /**
     * 笔记点赞
     * @param user_token 用户令牌
     * @param note_id 笔记id
     * @return array|bool
     */
    public function addLike()
    {
//        $_GET['user_token'] = 'cd69089bea5af15192e10ce17be7d4939eb02bb7';
//        $_GET['id'] = 6;
//        $_GET['timestamp'] = time();
//        $salt = 'ttVm5';
//        $_GET['sign'] = encrypt_key(['v1/sectionnote/addlike', $_GET['timestamp'], $_GET['user_token'], $salt], '');

        $user_token = input('param.user_token', '', 'trim');
        $note_id = input('param.id', 0, 'intval');

        //令牌校验
        $tokenRes = checkUserToken($user_token);
        if ($tokenRes !== true) {
            return $tokenRes;
        }

        if (empty($note_id)) {
            return $this->fail(20050, '笔记id必须');
        }

        $userModel = new MoocUser();
        $noteModel = new SectionNotes();
        $likeModel = new Like();
        $user = $userModel->where(['user_token' => $user_token])->find();
        $note = $noteModel->where(['id' => $note_id])->find();
        if ($note === null) {
            return $this->fail(20052, '笔记不存在');
        }
        $is_exist = $likeModel->where(['user_id' => $user['id'], 'resource_id' => $note_id, 'type' => 2])->find();
        if ($is_exist) {
            return $this->fail(20053, '已点赞');
        }

        $data = [
            'user_id' => $user['id'],
            'resource_id' => $note_id,
            'type' => 2,
            'create_time' => time()
        ];

        if ($likeModel->save($data) > 0) {
            $noteModel->where(['id' => $note_id])->setInc('like_num');
            return $this->ok('', 20152, '笔记点赞成功');
        } else {
            return $this->fail(230053, '笔记点赞失败');
        }


    }

    /**
     * 笔记采集
     * @param user_token 用户令牌
     * @param $note_id 笔记id
     * @return array|bool
     */
    public function collect_note()
    {
//        $_GET['user_token'] = 'cd69089bea5af15192e10ce17be7d4939eb02bb7';
//        $_GET['id'] = 6;
//        $_GET['timestamp'] = time();
//        $salt = 'ttVm5';
//        $_GET['sign'] = encrypt_key(['v1/sectionnote/collect_note', $_GET['timestamp'], $_GET['user_token'], $salt], '');

        $user_token = input('param.user_token', '', 'trim');
        $note_id = input('param.id', 0, 'intval');
        $type = input('param.type', 2, 'intval');

        //令牌校验
        $tokenRes = checkUserToken($user_token);
        if ($tokenRes !== true) {
            return $tokenRes;
        }

        $userModel = new MoocUser();
        $noteModel = new SectionNotes();
        $note = $noteModel->where('id', $note_id)->find();
        $noteModel->where(['id'=>$note_id])->setInc('collect_num');
        $collect_from = $note_id;
        $user_id = $userModel->where(['user_token' => $user_token])->value('id');

        $data = [
            'user_id' => $user_id,
            'center_id' => $note['center_id'],
            'course_id' => $note['course_id'],
            'section_id' => $note['section_id'],
            'content' => $note['content'],
            'create_time' => time(),
            'type' => $type,
            'collect_from' => $collect_from
        ];


        if ($noteModel->save($data) > 0) {
            return $this->ok('', 20160, '采集笔记成功');
        } else {
            return $this->fail(20061, '采集笔记失败');
        }

    }

    /**
     * 笔记回复
     * @param user_token 用户令牌
     * @param $note_id 笔记id
     * @return array|bool
     */
    public function reply()
    {
        $_GET['user_token'] = 'cd69089bea5af15192e10ce17be7d4939eb02bb7';
        $_GET['timestamp'] = time();
        $salt = 'ttVm5';
        $_GET['sign'] = encrypt_key(['v1/sectionnote/reply', $_GET['timestamp'], $_GET['user_token'], $salt], '');
        $_GET['content'] = '时间fda减肥附属卡大家带上飞机啊看';

        $user_token = input('param.user_token', '', 'trim');
        $note_id = input('param.note_id', 9, 'intval');
        $content = input('param.content', '真是太好了', 'trim');

        //令牌校验
        $tokenRes = checkUserToken($user_token);
        if ($tokenRes !== true) {
            return $tokenRes;
        }

        if (empty($note_id)) {
            return $this->fail(12001, '笔记id不能为空');
        }

        $user = (new MoocUser())->where(['user_token' => $user_token])->find();
        $note = (new SectionNotes())->where(['id' => $note_id])->find();
        if ($note == null) {
            return $this->fail(12002, '笔记不存在');
        }

        $data['note_id'] = $note_id;
        $data['user_id'] = $user['id'];
        $data['reply_id'] = $note['user_id'];
        $data['content'] = $content;
        $data['create_time'] = time();

        if ((new SectionNoteReply())->save($data) > 0) {
            return $this->ok('',12112,'回复笔记成功');
        }else{
            return $this->fail(12003,'回复笔记失败');
        }

    }

    /**
     * 删除回复
     * @param user_token 用户令牌
     * @param $note_reply_id  回复的id
     * @return array|bool
     */
    public function del_reply(){
//        $_GET['user_token'] = 'b616164e6ce937e8debb6345783a9746ebcd1e5c';
//        $_GET['timestamp'] = time();
//        $salt = 'qSccu';
//        $_GET['sign'] = encrypt_key(['v1/sectionnote/del_reply', $_GET['timestamp'], $_GET['user_token'], $salt], '');

        $user_token = input('param.user_token','','trim');
        $reply_id = input('param.reply_id',0,'intval');

        //令牌校验
        $tokenRes = checkUserToken($user_token);
        if ($tokenRes !== true) {
            return $tokenRes;
        }

        if(empty($reply_id)){
            return $this->fail(13001, '回复id不能为空');
        }

        $replyModel = new SectionNoteReply();
        $user = (new MoocUser())->where(['user_token' => $user_token])->find();
        $reply = $replyModel->where(['id'=>$reply_id,'user_id'=>$user['id']])->find();
        if($reply == null){
            return $this->fail(13002, '笔记不存在或此用户没有操作权限');
        }

        if($replyModel->where(['id'=>$reply_id])->update(['delete_time'=>time()]) !== false){
            return $this->ok('',13101, '回复删除成功');
        }else{
            return $this->fail(13003, '回复删除失败');
        }

    }


}