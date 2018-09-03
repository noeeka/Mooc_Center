<?php
/**
 * Created by PhpStorm.
 * User: 李德顺
 * Date: 2018/3/13
 * Time: 14:42
 */
namespace app\admin\controller;

use app\admin\model\DiscussionModel;
use cmf\controller\AdminBaseController;
use think\Controller;
use think\Request;

class IdeaController extends AdminBaseController
{
    protected $discussionModel;

    public function __construct(Request $request = null , DiscussionModel $discussionModel)
    {
        parent::__construct($request);
        $this->discussionModel = $discussionModel;
    }
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
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
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }

    public function show()
    {
        $opinionId = $this->request->param('id' , '0' , 'intval');
        $opinionInfo = $this->discussionModel->find($opinionId)->toArray();

        halt($opinionInfo);
    }
}
