<?php

namespace app\admin\controller;

use app\admin\model\HomePageContentModel;
use app\admin\model\HomePageGlobalModel;
use app\admin\model\HomePageResourceModel;
use app\admin\model\HomePageTplModel;
use app\admin\model\HomePageTplSpecialModel;
use cmf\controller\AdminBaseController;

class HomePageContentController extends AdminBaseController
{

    public function index()
    {
        $homePageContentModel = new HomePageContentModel();
        $contents = $homePageContentModel->contentTableTree();

        $this->assign('contents', $contents);
        return $this->fetch();
    }

    public function config()
    {
        $config = HomePageGlobalModel::get(0);
        $header_options = (new HomePageTplSpecialModel())->getOption($config->header_tpl_id, ['type' => 1]);
        $footer_options = (new HomePageTplSpecialModel())->getOption($config->footer_tpl_id, ['type' => 2]);

        $this->assign('header_options', $header_options);
        $this->assign('footer_options', $footer_options);
        $this->assign($config->toArray());
        return $this->fetch();
    }

    public function editConfig()
    {
        $post = $this->request->param();
        if ($post['header_tpl_id'] == 0) {
            $this->error('头部模板必须选择');
        }
        if ($post['footer_tpl_id'] == 0) {
            $this->error('footer模板必须选择');
        }
        $post['id'] = 0;
        $res = HomePageGlobalModel::update($post);
        if (false === $res) {
            $this->error('修改失败');
        } else {
            $this->success('修改成功');
        }
    }

    public function add()
    {
        $parent_id = $this->request->param('parent_id', 0, 'intval');
        //获取资源
        $resources = (new HomePageResourceModel())->getOption();
        //获取模板
        $tpls = (new HomePageTplModel())->getOption();
        //选择菜单
        $contentOptions = (new HomePageContentModel())->contentTree($parent_id, 0, ['parent_id' => 0]);

        $this->assign('parent_id', $parent_id);
        $this->assign('contentOptions', $contentOptions);
        $this->assign('resources', $resources);
        $this->assign('tpls', $tpls);
        return $this->fetch();
    }

    public function addPost()
    {
        $post = $this->request->param();
        if ($post['title'] == '') {
            $this->error('标题必须');
        }
        if ($post['parent_id'] != 0) {
            if ($post['start'] == '') {
                $this->error('开始位置必须');
            }
            if ($post['len'] == '') {
                $this->error('数量必须');
            }
        }
        $homePageContentModel = new HomePageContentModel();
        $homePageContentModel->allowField(true)->save($post);
        return $this->success('操作成功', url('HomePageContent/edit', ['id' => $homePageContentModel->getLastInsID()]));
    }

    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');
        $content = HomePageContentModel::get($id);
        if ($content == null) {
            $this->error('模块获取失败');
        }
        //获取资源
        $resources = (new HomePageResourceModel())->getOption($content->resource_id);
        //获取模板
        $tpls = (new HomePageTplModel())->getOption($content->tpl_id);
        //选择菜单
        $contentOptions = (new HomePageContentModel())->contentTree($content->parent_id, 0, ['parent_id' => 0, 'id' => ['neq', $id]]);

        $this->assign('parent_id', $content->parent_id);
        $this->assign('contentOptions', $contentOptions);
        $this->assign('resources', $resources);
        $this->assign('tpls', $tpls);
        $this->assign($content->toArray());
        return $this->fetch();
    }

    public function editPost()
    {
        $post = $this->request->param();
        if ($post['title'] == '') {
            $this->error('标题必须');
        }
        if ($post['parent_id'] != 0) {
            if ($post['start'] == '') {
                $this->error('开始位置必须');
            }
            if($post['start'] < 1){
                $this->error('开始位置必须大于1');
            }
            if ($post['len'] == '') {
                $this->error('数量必须');
            }
            if($post['len'] < 0){
                $this->error('开始位置必须大于0');
            }
        }
        $homePageContentModel = new HomePageContentModel();
        $post['update_time'] = time();
        $res = $homePageContentModel->allowField(true)->where('id', $post['id'])->update($post);
        if (false === $res) {
            return $this->success('编辑失败');
        } else {
            return $this->success('编辑成功');
        }
    }

    public function delete()
    {
        $id = $this->request->param('id', 0, 'intval');
        $content = HomePageContentModel::get($id);
        if ($content == null) {
            $this->error('记录不存在');
        }
        if ($content->parent_id == 0) {
            $childNum = HomePageContentModel::where('parent_id', $id)->count(1);
            if ($childNum > 0) {
                $this->error('请删除子模块后重试');
            }
        }
        HomePageContentModel::destroy(['id' => $id]);
        return $this->success('操作成功');
    }

    public function toggle()
    {
        $ids = $this->request->param('ids/a');
        $status = $this->request->param('status');
        $res = HomePageContentModel::where(['id' => ['in', $ids]])->update(['status' => $status]);
        if (false === $res) {
            return $this->success('操作失败');
        } else {
            return $this->success('操作成功');
        }
    }

    public function listOrder()
    {
        $res = parent::listOrders(new HomePageContentModel()); // TODO: Change the autogenerated stub
        if ($res == true) {
            $this->success('排序成功');
        } else {
            $this->success('排序失败');
        }
    }

}