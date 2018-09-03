<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/13
 * Time: 13:40
 */

namespace app\api\controller;


use app\user\model\UserModel;
use app\admin\model\ActivityBaomingModel;
use think\Db;

class VolunScoreController extends Base
{
    //志愿者活跃度
    public function index()
    {

        $scoreList = Db::name('user')->alias('u')->where(['user_role' => 2])->join('sfzimg s', 's.user_id=u.id')->field('u.score,u.avatar,u.id,s.realname')->order("score DESC")->select()->toArray();

        if (empty($scoreList)) {
            //图片处理
            return $this->output_error(14011, '志愿者活跃度获取失败');
        } else {
            foreach ($scoreList as $k => $v) {
                $scoreList[$k]['avatar'] = $v['avatar'] == '' ? '' : cmf_get_image_preview_url($v['avatar']);
            }

            return $this->output_success(14111, $scoreList, '活跃度获取成功');
        }


    }

    /**
     * 定时任务
     * 活动结束三天后如果未派发积分自动加上活动活跃积分
     */
    public function actionthreedays()
    {
        $baomingModel = new ActivityBaomingModel();
        $baomingList = $baomingModel->alias('b')->join('activity a', 'a.id=b.activity_id', 'left')->field('b.*,a.score as activity_score,a.end_time')->where(['b.score' => 0, 'a.type' => 0, 'b.status' => 1])->select()->toArray();

        foreach ($baomingList as $value) {
            $timestamp = 3600 * 24 * 3;

            if (time() > ($value['end_time'] + $timestamp) && $value['score'] == 0) {
                $baomingModel->where('id', $value['id'])->update(['score' => $value['activity_score']]);

                $activity_id = $value['activity_id'];
                $activity_score = $value['activity_score'];
                $user_id = $value['user_id'];
                //给用户加总积分
                $sql = "update cxtj_user u,cxtj_activity_baoming b set u.score=(if((u.score-b.score+$activity_score)>0,u.score-b.score+$activity_score,0)) where u.id = b.user_id and u.id=$user_id and b.activity_id=$activity_id";

                Db::name('user')->query($sql);
            }
        }

    }
}