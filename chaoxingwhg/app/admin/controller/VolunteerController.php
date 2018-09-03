<?php

namespace app\admin\controller;

use app\admin\model\AreaModel;
use app\admin\model\SfzimgModel;
use cmf\controller\AdminBaseController;
use app\admin\model\UserModel;
use think\Controller;
use think\Request;

/**
 * 志愿者管理控制器
 * author lideshun
 */
class VolunteerController extends AdminBaseController
{
    protected $volunteer;

    public function __construct(Request $request = null , UserModel $user)
    {
        parent::__construct($request);
        $this->user = $user;
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $param = $this->request->param();

        $area = new AreaModel();
        $volunteers = $this->getVolunteerList($param , 10);
        $volunteers->appends($param);
        $areas = $area->where('status' , 1)->select()->toArray();

        $this->assign('volunteer' ,  $volunteers->items());
        $this->assign('areas' , array_column($areas , null , 'id'));
        $this->assign('page', $volunteers->render());
        $this->assign('sex', isset($param['sex']) ? $param['sex'] : -1);
        $this->assign('verify', isset($param['verify']) ? $param['verify'] : -1);
        $this->assign('vstatus', isset($param['vstatus']) ? $param['vstatus'] : 0);

        return $this->fetch();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read()
    {
        $id = input('id' , 0 , 'intval');
        $volun_status = input('volun_status' , 0 , 'intval');
        if(empty($id))
            $this->error();

        $area = new AreaModel();
        $userModel = new UserModel();
        $volunteerInfo = $userModel
            ->alias('u')
            ->join('sfzimg s','s.user_id=u.id','left')
            ->where('u.id' , $id)
            ->field('u.*,s.shenfenzheng,s.img as sfzimg,s.realname')
            ->find();
        $areas = $area->where('status' , 1)->select()->toArray();

        if(empty($volunteerInfo['volun_skill_imgs'] ))
            $volunteerInfo['volun_skill_imgs'] = [];
        else
            $volunteerInfo['volun_skill_imgs'] = json_decode($volunteerInfo['volun_skill_imgs'] , 1);

        if(empty($volunteerInfo['sfzimg'] ))
            $volunteerInfo['sfzimg'] = [];
        else
            $volunteerInfo['sfzimg'] = json_decode($volunteerInfo['sfzimg'] , 1);

        $this->assign('areas' , array_column($areas , null , 'id'));
        $this->assign('volunteerInfo' , $volunteerInfo);
        $this->assign('volun_status',$volun_status);
        return $this->fetch();
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit()
    {
        $id = input('id' , 0 , 'intval');
        $verify = input('verify_status',0,'intval');

        if($id == 0) {
            $this->error('id不能为空');
        }

        $userModel = new UserModel();
        $sfzModel = new SfzimgModel();

        //审核驳回
        // 将user表中user_role  变为 1(已认证用户)   volun_status 变为 2（志愿者状态未通过）
        if($verify == 1){
            $rejectResult = $userModel->where('id',$id)->update(['volun_status' => 2,'user_role'=>1]);
            if($rejectResult){
                $this->success('审核驳回成功' , url("Volunteer/index") );
            }
        }

        //审核通过 将user表中user_role 变为2 volun_status 变为3  将sfzimg表中status变为2
        if($verify == 2){
            $passResult = $userModel->where('id',$id)
                ->update(['volun_status' => 3,'user_role'=>2 ]);
            if($passResult){
                $sfzResult = $sfzModel->where('user_id',$id)->update(['status'=>2]);
                $this->success('审核通过成功' , url("Volunteer/index") );
            }
        }


    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {



    }

    /**
     * 删除指定资源
     */
    public function delete()
    {
        $param = $this->request->param();

        $userModel = new UserModel();
        if(isset($param['id'])){
            $id = $this->request->param('id',0,'intval');
            $volun_status = $userModel->where('id',$id)->value('volun_status');
            //删除已通过的志愿者 volun_staus=0 $user_role=1
            if($volun_status == 3){
                $userModel->where('id',$id)->update(['volun_status'=>0,'user_role'=>1]);
            }
            //删除未通过的志愿者 volun_status=0
            if($volun_status == 1 || $volun_status == 1){
                $userModel->where('id',$id)->update(['volun_status'=>0]);
            }
            $this->success('删除志愿者成功');
        }

        if(isset($param['ids'])){
            $ids = $this->request->param('ids/a');
            foreach($ids as $value){
                $volun_status = $userModel->where('id',$value)->value('volun_status');
                if($volun_status == 3){
                    $userModel->where('id',$id)->update(['volun_status'=>0,'user_role'=>1]);
                }
                //删除未通过的志愿者 volun_status=0
                if($volun_status == 1 || $volun_status == 1){
                    $userModel->where('id',$id)->update(['volun_status'=>0]);
                }
            }

            $this->success('删除志愿者成功');
        }
    }

    /**
     * 展示志愿者风采  取消展示志愿者风采
     */
    public function show(){
        $param = $this->request->param();

        $userModel = new UserModel();
        if(isset($param['id']) && isset($param['show'])){
            $id = $this->request->param('id',0,'intval');
            $userModel->where('id',$id)->update(['is_show'=>1]);
            $this->success('展示成功');
        }

        if(isset($param['id']) && isset($param['hide'])){
            $id = $this->request->param('id',0,'intval');
            $userModel->where('id',$id)->update(['is_show'=>0]);
            $this->success('取消展示成功');
        }

        if(isset($param['ids']) && isset($param['show'])){
            $ids = $this->request->param('ids/a');
            $userModel->where(['id'=>['in',$ids]])->update(['is_show'=>1]);
            $this->success('展示成功');
        }

        if(isset($param['ids']) && isset($param['hide'])){
            $ids = $this->request->param('ids/a');
            $userModel->where(['id'=>['in',$ids]])->update(['is_show'=>0]);
            $this->success('取消展示成功');
        }
    }

    public function listOrder(){

        $userModel = new  UserModel();
        parent::listOrders($userModel);
        $this->success("排序更新成功！");
    }

    /**
     * 志愿者风采展示前台顺序调整
     */
    public function up(){
        $param = $this->request->param();
        $userModel = new UserModel();

        if(isset($param['id'])){
            $id = $this->request->param('id',0,'intval');

            $user_listOrder = $userModel
                ->where(['id' => $id])
                 ->value('list_order');

            $prev_user = $userModel
                ->where(
                    [
                        'list_order' => [ '<' , $user_listOrder] ,
                        'volun_status' => ['neq' , 0],
                        'user_type' => 2
                    ])
                ->order('list_order desc')
                ->limit(1)
                ->select()
                ->toArray();

            if(empty($prev_user))
                $this->error('已在最上面,不能上移');
            else {
                $userModel
                    ->where( ['id' => $id] )
                    ->update(
                        ['list_order' => $prev_user[0]['list_order']]);
                $userModel
                    ->where( ['id'=>$prev_user[0]['id']] )
                    ->update(['list_order'=>$user_listOrder]);
                $this->success('上移成功');
            }

        }

    }

    public function down()
    {
        $param = $this->request->param();
        $userModel = new UserModel();

        if(isset($param['id'])){
            $id = $this->request->param('id',0,'intval');
            $user_listOrder = $userModel->where(['id'=>$id])->value('list_order');
            $next_user = $userModel->where(['list_order'=>['>',$user_listOrder],'volun_status'=>['neq',0], 'user_type'=>2])->order('list_order asc')->limit(1)->select()->toArray();

            if(empty($next_user)){
                $this->error('已在最下面,不能下移');
            }else{
                $userModel->where(['id'=>$id])->update(['list_order'=>$next_user[0]['list_order']]);
                $userModel->where(['id'=>$next_user[0]['id']])->update(['list_order'=>$user_listOrder]);
                $this->success('下移成功');
            }

        }


    }

    public function updatePost()
    {
        $ids = explode(',' ,  input('ids' , 0));

        if(count($ids) == 0) {
            echo json_encode(['status'=> 0,'data'=>'','message'=>'id不能为空']);exit;
        }

        $updateResult = $this->user->whereIn('id' , $ids )->update(['status' => 2]);

        if($updateResult) {
            echo json_encode(['status'=> 1,'data'=>'','message'=>'更新成功']);exit;
        } else {
            echo json_encode(['status'=> 0,'data'=>'','message'=>'更新失败']);exit;
        }
    }


    private function getVolunteerList($param , $paginate = 10)
    {

        $where['u.volun_status'] = [ 'in' , '1,2,3'];

        if(isset($param['verify']) &&  $param['verify'] != -1){
            $where['s.status'] = $param['verify'];
        }

        if(isset($param['vstatus']) &&  $param['vstatus'] != 0){
            $where['u.volun_status'] = $param['vstatus'];
        }

        if(isset($param['sex']) &&  $param['sex'] >= 0 )
            $where['u.sex'] = $param['sex'];

        if(isset($param['keyword']) && !empty($param['keyword'])) {
            $where['s.realname'] =  ['like', "%".$param['keyword']."%"];
        }

        $userModel = new UserModel();

        $queryResult = $userModel
            ->alias('u')
            ->join('sfzimg s','s.user_id=u.id')
            ->order('list_order asc')
            ->where($where)
            ->field('u.*,s.status as sfz_status,s.realname')
            ->paginate($paginate);


        return $queryResult;
    }
}
