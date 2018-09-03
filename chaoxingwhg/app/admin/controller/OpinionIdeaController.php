<?php
/**
 * Created by PhpStorm.
 * User: 李德顺
 * Date: 2018/3/13
 * Time: 14:42
 */
namespace app\admin\controller;

use app\admin\model\AreaModel;
use app\admin\model\DiscussionModel;
use app\admin\model\OpinionIdeaModel;
use app\admin\model\UserModel;
use app\admin\model\VenueModel;
use cmf\controller\AdminBaseController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use think\Controller;
use think\Db;
use think\Request;

class OpinionIdeaController extends AdminBaseController
{
    protected $discussionModel;
    protected $opinionIdeaModel;

    public function __construct(Request $request = null , DiscussionModel $discussionModel ,OpinionIdeaModel $opinionIdeaModel)
    {
        parent::__construct($request);
        $this->discussionModel = $discussionModel;
        $this->opinionIdeaModel = $opinionIdeaModel;
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
        $userModel = new UserModel();
        $id = $this->request->param('id', 0, 'intval');
        $opinionIdea = $this->opinionIdeaModel->find($id);
        $discussion = $this->discussionModel->find($opinionIdea['opinion_id']);
        if(empty($opinionIdea))
            $this->error('option 不存在');

        $userInfo = $userModel->find($opinionIdea['user_id']);

        $user = db('sfzimg')->where(['user_id' => $opinionIdea['user_id'] , 'status' => 2])->find();
        $userInfo['user_realname'] = empty($user) ? "未知" : $user['realname'];
        $this->assign('discussion' ,  $discussion->toArray());
        $this->assign('opinionIdea' , $opinionIdea->toArray());
        $this->assign('userInfo' , $userInfo);
        return $this->fetch();

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
        $id = $this->request->param('id' ,'0','intval');
        $opinionIdea = $this->opinionIdeaModel->find($id);
        if(empty($opinionIdea))
            $this->error('更改失败');

        $content =  $this->request->param('content' ,'0','trim');
        if(empty($content))
            $this->error('内容必须存在');

        $updateResult = $this->opinionIdeaModel->where('id' , $id)->update(['content' => $content]);
        if($updateResult)
            $this->success("保存成功！",  url('/admin/opinion_idea/show/' , ['id'=> $opinionIdea['opinion_id']]) );
        else
            $this->error('保存内容失败');
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $opinionIdeaModel = new OpinionIdeaModel();
        $id = $this->request->param('id' , '0' , 'intval');
        $opinionId = $this->request->param('opinionId' , '0' , 'intval');
        if(empty($id) || empty($opinionId))
            $this->error('id不能为空');

        if(!empty($id) ){
            $updateResult = $opinionIdeaModel->where(['id' => $id])->update( ['is_delete' => 0] );
            $this->success("删除成功！", url('/admin/opinion_idea/show/' , ['id'=> $opinionId]));
        }
        $this->error('删除失败');
    }

    public function show()
    {
        //获取民意征集信息
        $opinionId = $this->request->param('id' , '0' , 'intval');
        $opinionInfo = $this->discussionModel->find($opinionId);
        if(empty($opinionInfo))
            $this->error('不存在');

        $area = AreaModel::get(['id' => $opinionInfo['area_id']]);
        $opinionInfo['areaName'] = isset($area['name']) ? $area['name'] : "未知区域";
        $Venue = VenueModel::get(['id' => $opinionInfo['venue_id']]);
        $opinionInfo['venueName'] = isset($Venue['name']) ? $Venue['name'] : "未知区域";

        //取出所有的次民意征集中的意见
        $opinionIdes = $this->opinionIdeaModel->getOpinionIdeaList(['opinion_id' => $opinionId] , 10);

        $this->assign('opinionInfo' , $opinionInfo->toArray());
        $this->assign('opinionIdes' , $opinionIdes->items());
        $this->assign('page' , $opinionIdes->render());
        return $this->fetch();
    }

    public function updateStatus()
    {
        $opinionIdeaModel = new OpinionIdeaModel();
        $id = $this->request->param('id' , '0' , 'intval');
        $opinionId = $this->request->param('opinionId' , '0' , 'intval');
        $key = $this->request->param('key' , '0' , 'trim');
        $value = $this->request->param('value' , '0' , 'intval');

        if(empty($id) || empty($opinionId) || empty($key))
            $this->error('id不能为空');

        if(!empty($id) ){
            $where[$key] = $value;
            if ($key == "public_status" && $value == 2) {
                $where['status'] = 2;
            }
            $updateResult = $opinionIdeaModel->where([ 'id' => $id])->update( $where );
            $this->success("更改成功！", url('/admin/opinion_idea/show/' , ['id'=> $opinionId]));
        }
        $this->error('更改失败');
    }

    public function export()
    {

        $id = $this->request->param('id' ,'0' ,'intval');
        $ideaList = Db::name('opinion_idea')
            ->alias('idea')
            ->join('user u','idea.user_id = u.id','left')
            ->join('sfzimg' , 'idea.user_id = sfzimg.user_id' , 'left')
            ->where(['opinion_id' => $id , 'is_delete' => 1])
            ->field('idea.*,sfzimg.realname,u.user_login')->select();

        if( $ideaList->isEmpty())
            $this->error('数据不存在');

        $ideaList = $ideaList->toArray();

        $spreadsheet = new Spreadsheet();
        // Set document properties
        $spreadsheet->getProperties()->setCreator('Maarten Balliauw')
            ->setLastModifiedBy('Maarten Balliauw')
            ->setTitle('Office 2007 XLSX Test Document')
            ->setSubject('Office 2007 XLSX Test Document')
            ->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
            ->setKeywords('office 2007 openxml php')
            ->setCategory('Test result file');
        // Add some data
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1','内容简介' )
            ->setCellValue('B1','允许公开' )
            ->setCellValue('C1','公开状态')
            ->setCellValue('D1','提交时间')
            ->setCellValue('E1','状态')
            ->setCellValue('F1','提交人账户')
            ->setCellValue('G1','提交人真实姓名');

        for($i= 0; $i<count($ideaList);$i++){

            $allow_public = ['保留' ,'可公开' , '不可公开'];
            $public_status = ['保留' ,'未公开' , '公开'];
            $status = ['保留' ,'未读' , '已读'];

            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A'.($i+2) , $ideaList[$i]['content'] )
                ->setCellValue('B'.($i+2) , $allow_public[$ideaList[$i]['allow_public']] )
                ->setCellValue('C'.($i+2),  $public_status[$ideaList[$i]['public_status']] )
                ->setCellValue('D'.($i+2),  date('Y-m-d H:i:s' , $ideaList[$i]['create_time'] ))
                ->setCellValue('E'.($i+2),  $status[$ideaList[$i]['status']])
                ->setCellValue('F'.($i+2),  $ideaList[$i]['user_login'])
                ->setCellValue('G'.($i+2),  empty($ideaList[$i]['realname']) ? '未知' :$ideaList[$i]['realname']  );

        }


        // Redirect output to a client’s web browser (Xls)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="建议内容.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
        exit;

    }
}
