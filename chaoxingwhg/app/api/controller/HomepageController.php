<?php
/**
 * Created by PhpStorm.
 * User: jxbx
 * Date: 2018/5/23
 * Time: 17:22
 */

namespace app\api\controller;


use app\admin\model\ActivityModel;
use app\admin\model\HomePageContentModel;
use app\admin\model\HomePageGlobalModel;
use app\admin\model\HomePageLinkModel;
use app\admin\model\HomePageLogModel;
use app\admin\model\HomePageTplModel;
use app\admin\model\HomePageTplSpecialModel;
use Think\Db;
class HomepageController extends Base
{
    public function pc()
    {
        $contentModel = new HomePageContentModel();
        $type = input('type', 0, 'intval');
        $content_list = $contentModel
            ->alias('c')
            ->join('__HOME_PAGE_RESOURCE__ r', 'c.resource_id = r.id', 'left')
            ->join('__HOME_PAGE_TPL__ t', 'c.tpl_id = t.id', 'left')
            ->field('c.*,r.url as api_url,t.html as tpl_code,t.css as tpl_css')
            ->where(['status' => 1,'c.type'=>$type])
            ->order(['list_order' => 'asc'])
            ->select()
            ->toArray();
        $res = [];

        foreach ($content_list as $k => $first) {
            unset($content_list[$k]['create_time']);
            unset($content_list[$k]['update_time']);
            unset($content_list[$k]['status']);
            unset($content_list[$k]['list_order']);
            $content_list[$k]['tpl_code'] = htmlspecialchars_decode($first['tpl_code']);
            $content_list[$k]['more_url'] = htmlspecialchars_decode($content_list[$k]['more_url']);

            if ($type ==0){
                if ($first['parent_id'] == 0) {
                    unset($content_list[$k]['start']);
                    unset($content_list[$k]['len']);
                    unset($content_list[$k]['tpl_id']);
                    unset($content_list[$k]['resource_id']);
                    unset($content_list[$k]['tpl_code']);
                    unset($content_list[$k]['tpl_css']);
                    unset($content_list[$k]['api_url']);
                    $res[$first['id']] = $content_list[$k];
                    $res[$first['id']]['sub'] = [];
                }
            }


        }
        if ($type==0){
            foreach ($content_list as $second) {
                if ($second['parent_id'] != 0) {
                    if (isset($res[$second['parent_id']])) {
                        $second['start']--;
                        $res[$second['parent_id']]['sub'][] = $second;
                    }
                }
            }
        }else{
            $res = $content_list;
        }


        return $this->output_success(17101, array_values($res), '获取内容成功');

    }

    public function pc_global()
    {
        $global = HomePageGlobalModel::get(0);
        if ($global == null) {
            return $this->output_error(17002, '获取全局配置失败');
        } else {
            $header = HomePageTplSpecialModel::where('id', $global->header_tpl_id)->field('background_image,background_color,alias as header_alias,css as header_css')->find();
            if ($header == null) {
                return $this->output_error(17002, '获取全局配置失败');
            }
            $global['footer_alias'] = HomePageTplSpecialModel::where('id', $global->footer_tpl_id)->value('alias');
            unset($global['create_time']);
            unset($global['update_time']);
            $header->background_image = cmf_get_image_preview_url($header->background_image);
            $global = array_merge($global->toArray(), $header->toArray());
            Db::table('cxtj_baseinfo')->where('id', 0)->setInc('pcaccess_count');
            Db::table('cxtj_baseinfo')->where('id', 0)->setInc('pctrue_count');
            return $this->output_success(17102, $global, '获取全局配置成功');
        }
    }

    public function getTpl()
    {
        $id = input('id', 0, 'intval');
        $tplModel = new HomePageTplModel();
        $tpl_msg = $tplModel->where(['id' => $id])->find();
        return $this->output_success(11112, $tpl_msg, '获取模板信息成功');
    }

    public function getBlockCss()
    {
        $id = input('id', 0, 'intval');
        $blockModel = new HomePageContentModel();
        $block_css = $blockModel->where(['id' => $id])->find();

        return $this->output_success(11112, $block_css, '获取模块信息成功');
    }


    //list模板另存为
    public function Saveas()
    {
        $id = input('id', 0, 'intval');
        $name = input('name');

        $tplModel = new HomePageTplModel();
        $data = $tplModel->where(['id' => $id])->find()->toArray();
        unset($data['id']);
        unset($data['name']);
        unset($data['creat_time']);
        unset($data['update_time']);
        $data['name'] = $name;
        $data['create_time'] = time();
        $data['update_time'] = time();
        $result = $tplModel->save($data);

        $res = [];
        $res['id'] = $tplModel->getLastInsID();
        $res['url'] = url('Admin/HomePageListTpl/edit', ['id' => $res['id']]);

        if ($result === false) {
            return $this->output_error(11101, '另存为失败');
        }
        return $this->output_success(11111, $res, '另存为成功');


    }

    //特殊模板另存为
    public function Special_saveas()
    {
        $id = input('id', 0, 'intval');
        $name = input('name');

        $spcialModel = new HomePageTplSpecialModel();
        $data = $spcialModel->where(['id' => $id])->find()->toArray();
        unset($data['id']);
        unset($data['name']);
        unset($data['creat_time']);
        unset($data['update_time']);
        $data['name'] = $name;
        $data['create_time'] = time();
        $data['update_time'] = time();
        $result = $spcialModel->save($data);

        $res = [];
        $res['id'] = $spcialModel->getLastInsID();
        $res['url'] = url('Admin/HomePageSpecialTpl/edit', ['id' => $res['id']]);

        if ($result === false) {
            return $this->output_error(11101, '另存为失败');
        }
        return $this->output_success(11111, $res, '另存为成功');
    }

    function home_page_link()
    {
        $cid = input('param.cid', 0, 'intval');
        $offset = input('param.start', 0, 'intval');
        $limit = input('param.len', 10, 'intval');
        if ($cid > 0) {
            $where = ['type' => $cid];
        } else {
            $where = [];
        }
        $select = HomePageLinkModel::where($where)->field('id,\'\' as abstract,target,thumb,create_time as time,\'\' as title,url')->order('list_order asc')->limit($offset, $limit)->select();
        foreach ($select as $k => $v) {
            $select[$k]['thumb'] = cmf_get_image_preview_url($v['thumb']);
            $select[$k]['target'] = $v['target'] == '_self' ? 1 : 0;
        }
        return $this->output_success(11112, $select, '获取成功');
    }

    //活动招募
    function volun_zhaomu()
    {
        $offset = input('param.start', 0, 'intval');
        $limit = input('param.len', 10, 'intval');
        $where = [];
        $where['volun_type'] = ['neq', 0];
        $where['type'] = 0;
        $where['delete_time'] = 0;
        $where['status'] = 1;
        $recuritModel = new ActivityModel();
        $recuritList = $recuritModel->where($where)->field('id,abstract,1 as target,thumb, published_time as time')->order('is_top desc,baoming_end_time desc,id desc')->limit($offset, $limit)->select();

        if ($recuritList) {
            foreach ($recuritList as $key => $value) {
                $recuritList[$key]['thumb'] = cmf_get_image_preview_url($value['thumb']);;
            }
            return $this->output_success(11101, $recuritList, '招募获取成功');
        } else {
            return $this->output_error(11102, '获取招募失败');
        }
    }
}


