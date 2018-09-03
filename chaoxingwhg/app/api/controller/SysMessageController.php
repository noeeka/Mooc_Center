<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/1
 * Time: 14:40
 */

namespace app\api\controller;


use app\admin\model\BannerModel;
use app\admin\model\CommentsModel;
use app\admin\model\SysMessageModel;
use app\admin\model\UserModel;

class SysmessageController extends Base
{
    public function index($param = [])
    {
        $uid = $this->getuid();
        $page = input('param.page', 1);
        $len = input('param.len', 10);
        $sysMessageModel = new SysMessageModel();
        $list = $sysMessageModel->where(['to_id' => $uid])->page($page, $len)->order(['create_time' => 'desc'])->select();
        $num = $sysMessageModel->where(['to_id' => $uid])->count(1);
        foreach ($list as &$value) {
            $value['detail_url'] = '';
            if ($value['type'] == 1) {
                $value['detail_url'] = '/portal/sysmessage/comment/?id=' . $value['id'];
            }
        }
        return $this->output_success(11101, ['list' => $list, 'count' => $num], '获取列表成功');
    }

    public function read()
    {
        $uid = $this->getuid();
        $id = input('param.id', 0, 'intval');
        $sys = SysMessageModel::where(['id' => $id, 'type' => 1, 'to_id' => $uid])->find();
        if ($sys == null) {
            return $this->output_error(11002, '获取系统消息失败');
        } else {
            $sys->user_nickname = UserModel::where('id', $sys->to_id)->value('user_nickname');
            $sys->avatar = UserModel::where('id', $sys->to_id)->value('avatar');
            $sys->avatar = $sys->avatar == '' ? '' : cmf_get_image_preview_url($sys->avatar);
            $article_id = CommentsModel::where('id', $sys->rid)->value('articleid');
            $sys->mb_url = '/wx/category/read/?id='.$article_id;
            return $this->output_success(11102, $sys, '获取系统消息成功');
        }
    }
}