<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/13
 * Time: 14:37
 */

namespace app\api\controller;


use app\admin\model\AreaModel;
use app\admin\model\DiscussionModel;
use app\admin\model\OptionModel;
use app\admin\model\VenueModel;
use think\Log;
use think\Request;

class OpinionController extends Base
{
    protected $optionModel;
    public function __construct(DiscussionModel $optionModel)
    {
        parent::__construct();
        $this->request = Request::instance();
        $this->optionModel = $optionModel;
    }

    /**
     * 获取民意列表
     *
     * @param string $page      //页数
     * @param string $size      //一页多少条数据 max 15
     * @param string $type      //类型 0全部 1征集中 2即将征集 3往期征集
     * @param string $venue_id  //场馆id 不传值或者值为0 代表没有此搜索条件
     * @param string $area_id   //区域id 不传值或者值为0 代表没有此搜索条件
     *
     * @return json
     */
    public function getOpinionList()
    {
        if( $this->request->isPost()) {
            $param = [];
            $param['page'] = input('page','1','intval');
            $param['size'] = input('size','15','intval');
            $param['type'] = input('type','0','intval');
            $param['area_id'] = input('area_id','0','intval');
            $param['venue_id'] = input('venue_id','0','intval');
            $param['is_top'] = input('is_top','0','intval');

            $opinionValidate = validate('opinion');

            if(!$opinionValidate->check($param)) {
                return $this->output_error(0 , $opinionValidate->getError());
            }

            $list = $this->getOpinionListByParam($param);

            return $this->output_success(200, $list, "访问成功");
        }

        return $this->output_error('','无法访问');
    }

    /**
     * 获取民意意见详细信息
     *
     * @param string $opinionId
     *
     * @return json
     */
    public function getOpinionInfo()
    {
        if( $this->request->isPost()) {

            $id = input('opinionId','0','intval');

            if(empty($id) )
                return $this->output_error('','id无法访问');

            $opinional = $this->optionModel->find($id);

            $this->optionModel->where('id' , $id)->setInc('views_count' , '1');

            if(empty($opinional)) {
                return $this->output_success(200, [], "访问成功");
            } else {
                $info =  $opinional->toArray();
                $venue = VenueModel::find($info['venue_id']);
                $info['venue_name'] = isset($venue) ? $venue->toArray()['name'] : "";
                $info['time_type'] = 0;
                // 1征集中 2即将征集 3往期征集
                if(time() >= $info['start_time'] && time() <= $info['end_time'])
                    $info['time_type'] = 1;
                if(time() >= $info['start_time'] && time() >= $info['end_time'])
                    $info['time_type'] = 3;
                if(time() <= $info['start_time'] && time() <= $info['end_time'])
                    $info['time_type'] = 2;

                return $this->output_success(200, compact('info'), "访问成功");
            }

        }
    }

    public function getVenueWithArea(){
        //场馆id
        $venue = input('venue',0,'intval');
        //区域id
        $area =input('area',0,'intval');

        //获取场馆
        $venueModel = new VenueModel();
        $v_where =[];
        if(!empty($venue)){
            $v_where['id']=$venue;
        }
        $v_where['status']=1;
        $venues = $venues = VenueModel::getHot(1, 6);;

        //获取区域
        $areaModel = new AreaModel();
        $a_where =[];
        if(!empty($area)){
            $a_where['id']=$area;
        }
        $a_where['status']=1;
        $a_where['parent_id']=0;
        //获取顶级区域
        $area1 = $areaModel->where($a_where)->select();
        $area_arr=[];
        //获取二级区域
        foreach($area1 as $v){
            $son_area = $areaModel->where(['status'=>1,'parent_id'=>$v['id']])->select();
            $v['son'] = $son_area;
            $area_arr[] = $v;
        }
        $result['venue'] = $venues;
        $result['area'] = $area_arr;

        if($result){
            return $this->output_success('13101',$result,'活动获取成功');
        }else{
            return $this->output_error('13100','','活动获取失败');
        }
    }

    protected function getOpinionListByParam(array $param)
    {
        $where = [];
        $where['is_delete'] = 1;

        if(!empty($param['area_id'])) {
            $ids = $areaModel = (new AreaModel())->getTreeIds($param['area_id']);
            array_unshift($ids, $param['area_id']);
            $where['area_id'] = ['in', $ids];
        }

        if(!empty($param['venue_id'])) {
            $where['venue_id'] = $param['venue_id'];
        }

        if($param['is_top'] == 1) {
            $order = ["op.is_top" => "desc" , "op.create_time" => "desc"];
        } else {
            $order = ["op.create_time" => "desc"];
        }

        //0全部 //1征集中 //2即将征集// 3往期征集
        if(!empty($param['type'])) {
            if($param['type'] == 2) { //即将征集
                $where['cxtj_discussion.start_time'] = [ '>', time() ];
                $where['cxtj_discussion.end_time'] = ['>' , time()];
            } else if($param['type'] == 3) { //往期征集
                $where['cxtj_discussion.start_time'] = ['<' , time()];
                $where['cxtj_discussion.end_time'] =  ['<' , time()];
            } else if($param['type'] == 1)  { //征集中
                $where['cxtj_discussion.start_time'] = [ '<=' , time() ];
                $where['cxtj_discussion.end_time'] = [ '>' , time() ];
            }
        }
        $where['v.status'] = 1;
        $nums = $this->optionModel->alias('op')->join('venue v' , 'op.venue_id = v.id' , 'left')->where($where)->count('*');

        if(empty($nums))
            return [ 'nums' => 0 , 'list' => [] ];

        try{
            $datas = $this->optionModel
                ->alias('op')
                ->join('venue v' , 'op.venue_id = v.id' , 'left')
                ->where($where)
                ->order($order)
                ->field('op.is_top,op.views_count,op.create_time,op.id,op.start_time,op.end_time , op.title ,op.area_id ,op.venue_id , op.create_time ,v.name')
                ->limit(($param['page'] - 1) * $param['size'] , $param['size'] )
                ->select();

                if ($datas->isEmpty())
                    return [ 'nums' => 0 , 'list' => [] ];
                else
                    return array_merge( ['list' => $datas->toArray()]  ,['nums' => $nums]);
        } catch (\Exception $e) {

            Log::error(json_encode( $this->request->param() ,JSON_UNESCAPED_UNICODE ));
            return [];
        }

    }





}