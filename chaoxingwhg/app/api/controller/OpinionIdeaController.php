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
use app\admin\model\OpinionIdeaModel;
use app\admin\model\OptionModel;
use app\admin\model\VenueModel;
use think\Request;

class OpinionIdeaController extends Base
{
    protected $optionModel;
    public function __construct(DiscussionModel $optionModel , OpinionIdeaModel $optionIdeaModel)
    {
        parent::__construct();
        $this->request = Request::instance();
        $this->optionModel = $optionModel;
        $this->optionIdeaModel = $optionIdeaModel;
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
    public function getOpinionIdeaList()
    {
        if( $this->request->isPost()) {
            $param = [];
            $param['page'] = input('page','1','intval');
            $param['size'] = input('size','15','intval');
            $param['opinionId'] = input('opinion_id','0','intval');

            if(empty( $param['opinionId']))
                return $this->output_error('','opinionId参数错误');

            $nums = $this->optionIdeaModel
                ->where('opinion_id' , $param['opinionId'] )
                ->where('public_status' , 2 )
                ->count('*');

            if(empty($nums))
                return [ 'status' => 1, 'nums' => 0 , 'data' => ['data' => []] ];

            $list = $this->optionIdeaModel
                ->alias('opidea')
                ->join('user' , 'opidea.user_id = user.id' , 'left')
                ->where('opinion_id' , $param['opinionId'] )
                ->where('public_status' , 2 )
                ->where('is_delete' , 1 )
                ->order('opidea.create_time desc')
                ->field('opidea.content,opidea.create_time,user.avatar,user.user_nickname')
                ->limit(($param['page'] - 1) * $param['size'] , $param['size'] )
                ->select();

            if($list->isEmpty())
                $data = [];
            else {
                $list->each(function($item, $key){
                    $item['avatar'] = empty($item['avatar']) ? "/themes/simpleboot3/public/assets/whgcms/images/my/avatar1.png" :cmf_get_image_preview_url($item['avatar']);
                });
                $data = $list->toArray();
            }
            return $this->output_success(200, compact('nums' ,'data'), "访问成功");
        }

        return $this->output_error('','无法访问');
    }

    /**
     * 提交意见
     *
     * @param array $params[] user_id       (int) 用户id
     * @param array $params[] opinion_id    (int) 征集意见的id
     * @param array $params[] allow_public  (int) 是否允许公开
     *
     * @return json
     */
    public function submitIdea()
    {
        if( $this->request->isPost()) {
            $param = [];
            $uid = $this->getuid();

            $user = db('sfzimg')->where(['user_id' => $uid, 'status' => 2])->find();

            if(empty($user)) {
                return $this->output_error("2006" , "请先完成实名认证");
            }

            $param['content'] = urldecode(filter_var($this->request->param('content' , '0' ,'trim'),FILTER_SANITIZE_STRING));
            $param['allow_public'] = $this->request->param('allow_public' , '0' ,'intval');
            $param['opinion_id'] = $this->request->param('opinion_id' , '0' ,'intval');
            $param['user_id'] = $uid;
            $param['is_delete'] = 1;
            $param['public_status'] = 1;
            $param['status'] = 1;
            $param['create_time'] = time();

            $opinion = new DiscussionModel();

            $opinionInfo = $opinion->find($param['opinion_id']);

            if(empty($opinionInfo)) {
                return $this->output_error("查询失败");
            }
            if(time() >= $opinionInfo->start_time && time() <= $opinionInfo->end_time) {
                $saveData = new OpinionIdeaModel($param);
                if( $saveData->allowField(true)->save())
                    return $this->output_success(200, [], "添加成功");
                else
                    return $this->output_error("添加错误");
            } else {
                return $this->output_success('2005' ,[],"不在征集时间之内");
            }
        }
    }



}