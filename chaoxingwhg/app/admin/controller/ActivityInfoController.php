<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use app\admin\model\ActivityModel;
use app\admin\model\ActivityTypeModel;
use app\admin\model\VenueModel;
use app\admin\model\AreaModel;
class ActivityInfoController extends AdminBaseController
{

    /**
     * 活动列表
     */
    public function index()
    {

        $activityModel=new ActivityModel();
        $activity=$activityModel->alias('a')->join('area ar','a.area =ar.id','left')->join('venue v','a.venue =v.id','left')->field('a.*,ar.name as aname,v.name as vname');
       print_r($activity);exit;
        $this->assign('activity', $activity);

        return $this->fetch();
    }
}