<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/3/14
 * Time: 10:17
 */

namespace app\api\controller;

use app\admin\model\PerformModel;
use app\admin\model\AreaModel;
use app\admin\model\PerformDiandanModel;
use app\admin\model\VenueModel;

class CultureController extends Base
{
    /*
     * 获取文化点单列表
     */
    public function index()
    {
        $page = input('param.page', 1, 'intval');
        $len = input('param.len', 10, 'intval');
        $perform_type = input('param.typeid', 0, 'intval');
        $address = input('param.area', 0, 'intval');
        $venue = input('param.venue', 0, 'intval');
        $sort = input('param.sort', 0, 'intval');
        $type = input('param.type', 0, 'intval');
        $where = ['delete_time' => 0, 'p.type' => $type];
        $order = [];
        if ($perform_type != 0) {
            $where['typeid'] = $perform_type;
        }
        if ($address != 0) {
            $ids = $areaModel = (new AreaModel())->getTreeIds($address);
            array_unshift($ids, $address);
            $where['v.address'] = ['in', $ids];
        }
        if ($venue != 0) {
            $where['p.venue'] = $venue;
        }

        if ($sort != 0) {
            $order['p.vote_num'] = 'desc';
        } else {
            $order['p.create_time'] = 'desc';
        }
        $where['v.status'] = 1;

        $performModel = new PerformModel();
        $cultures = $performModel
            ->alias('p')
            ->join('__VENUE__ v', 'p.venue = v.id')
            ->where($where)
            ->join('perform_type t', 't.id = p.typeid')
            ->order($order)
            ->field('p.*,v.name,t.name as tname')
            ->page($page, $len)
            ->group('p.id')
            ->select()
            ->toArray();

        $count = $performModel
            ->alias('p')
            ->join('__VENUE__ v', 'p.venue = v.id')
            ->where($where)
            ->group('p.id')
            ->count();
        $res['list'] = $cultures;
        $res['count'] = $count;

        if (empty($res)) {
            return $this->output_error('13100', '获取失败');
        } else {
            //图片处理
            foreach ($res['list'] as &$v) {
                $v['thumb'] = cmf_get_image_preview_url($v['thumb']);
            }
            return $this->output_success('13001', $res, '获取成功');
        }

    }

    /*
     * 点击列表页获取文化点单详情页
     */
    public function read()
    {
        $id = input('id', 0, 'intval');
        $uid = $this->getuid(false);
        $perform = PerformModel::get($id);
        if ($perform) {
            //图片处理
            $perform['thumb'] = cmf_get_image_preview_url($perform['thumb']);
            $venue = VenueModel::get($perform['venue']);
            $perform['venue'] = $venue == null ? '' : $venue->name;
            $perform['leafs'] = (new AreaModel())->getLeafsOption($perform['areas']);
            $perform['state'] = PerformDiandanModel::where(['user_id' => $uid, 'perform_id' => $id])->count(1);
            return $this->output_success('13101', $perform, '文化点单详情获取成功');
        } else {
            return $this->output_error('13001', '文化点单详情获取失败');
        }
    }


//    资源库详情页
    public function detail()
    {
        $id = input('id', 0, 'intval');
        $perform = new PerformModel();
        $url = $perform->where(['id' => $id])->field('url')->select()->toArray();
        if ($perform) {
            return $this->output_success('13101', $url, '文化活动资源库获取成功');
        } else {
            return $this->output_error('13001', '', '文化活动资源库获取失败');
        }

    }

    /*
     * 添加点单
     */
    public function add()
    {
        $userid = $this->getuid();
        $performid = input('param.performid', 0, 'intval');
        $area = input('param.area', 0, 'intval');
        $create_time = time();
        $perform = '';
        $data['perform_id'] = $performid;
        $data['area_id'] = $area;
        $data['user_id'] = $userid;
        $data['create_time'] = $create_time;
        $performDiandan = new PerformDiandanModel();
        $result = $performDiandan->where(['user_id' => $userid, 'perform_id' => $performid])->find();

        if ($result) {
            return $this->output_error('13001', '已点过此表演，点单失败');
        } else {
            //点单信息存入数据库
            $diandan = $performDiandan->validate(true)->allowField(true)->save($data);
            if ($diandan) {
                (new PerformModel())->where('id', $performid)->setInc('vote_num');
                return $this->output_success('17101', '', '点单成功');
            } else {
                return $this->output_error('17001', $performDiandan->getError());
            }

        }


    }

    /*
     * 获取选择框选项
     */
    public function getOption()
    {
        $areaModel = new AreaModel;
        $html = $areaModel->getLeafsOption(0);
        return $html;
    }


}