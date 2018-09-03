<?php
/**
 * Created by PhpStorm.
 * User: 伟红
 * Date: 2018/3/1
 * Time: 17:44
 */

namespace app\admin\controller;


use app\admin\model\SfzimgModel;
use app\admin\model\UserModel;
use cmf\controller\AdminBaseController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use think\Db;

class ShenheController extends AdminBaseController
{

    public function index()
    {
        $param = $this->request->param();
        $status = $this->request->param('status', -1, 'intval');
        $keyword = $this->request->param('keyword', '', 'trim');
        $start_time = $this->request->param('start_time', '');
        $end_time = $this->request->param('end_time', '');
        $where = [];
        if ($start_time != '') {
            $where['apply_time'] = ['>=', strtotime($start_time)];
        }
        if ($end_time != '') {
            $where['apply_time'] = ['<=', strtotime($end_time)];
        }
        if ($start_time != '' && $end_time != '') {
            $where['apply_time'] = [['>=', strtotime($start_time)], ['<=', strtotime($end_time)]];
        }

        if (strlen($keyword) != 0) {
            $where['realname'] = ['like', "%$keyword%"];
        }

        if ($status != -1) {
            $where['s.status'] = $status;
        }


        if (!empty($where)) {

            $sfzimg = new SfzimgModel();
            $data = $sfzimg->alias('s')->join('__USER__ u', 'u.id = s.user_id')->where($where)->field('s.*,u.mobile')->order('apply_time desc')->select();
            $this->assign('data', $data);
            $this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
            $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
            $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
            $this->assign('count',count($data));

        } else {
            $sfzimg = new SfzimgModel();
            $data = $sfzimg->alias('s')->join('__USER__ u', 'u.id = s.user_id')->field('s.*,u.mobile')->order('apply_time desc')->select();

            $this->assign('data', $data);
            $this->assign('count',count($data));

        }
        $this->assign('status', $status);

        return $this->fetch();
    }


    public function edit()
    {

        $id = $this->request->param('id', 0, 'intval');
        $sfzimgModel = SfzimgModel::get($id);
        $user = new UserModel();
        $username = $user->where('id', $sfzimgModel['user_id'])->value('mobile');
        $imgs = array();


        $imgs = json_decode($sfzimgModel['img'], true);
        $this->assign('userid', $sfzimgModel['user_id']);
        $this->assign('user', $username);
        $this->assign('imgs', $imgs);
        $this->assign('shenhe', $sfzimgModel);
        return $this->fetch();
    }


    public function editPost()
    {
        $data = $this->request->param();


//        修改user表状态
        $user = new UserModel();
        if ($data['status'] == 2) {
            $user_role = $user->where(['id' => $data['userid']])->value('user_role');
            if ($user_role != 2) {
                $res = $user->where(['id' => $data['userid']])->update(['user_role' => 1]);
            }
        } else {
            $volun_status = $user->where(['id' => $data['userid']])->value('volun_status');
            if ($volun_status != 0) {
                $res = $user->where(['id' => $data['userid']])->update(['user_role' => 0, 'volun_status' => 2]);
            } else {
                $res = $user->where(['id' => $data['userid']])->update(['user_role' => 0]);
            }
        }

//        $data['img'] = $data['img'].",".$data['img1'];
        $data['img'] = json_encode(['img' => $data['img'], 'img1' => $data['img1']]);

        $sfzimgModel = new SfzimgModel();

        $result = $sfzimgModel->validate(true)->allowField(true)->isUpdate(true)->save($data);
        if ($result === false) {
            $this->error($sfzimgModel->getError());
        }

        $this->success("保存成功！", url("shenhe/index"));
    }

    public function delete()
    {
        $id = $this->request->param('id', 0, 'intval');
        SfzimgModel::destroy($id);

        $this->success("删除成功！", url("shenhe/index"));
    }


    public function listOrder()
    {
        parent::listOrders(Db::name('portal_category_post'));
        $this->success("排序更新成功！", '');
    }

    public function move()
    {

    }

    public function copy()
    {

    }


    public function exportUserIndex() {

        $param = $this->request->param();
        $status = $this->request->param('status', -1, 'intval');
        $keyword = $this->request->param('keyword', '', 'trim');
        $start_time = $this->request->param('start_time', '');
        $end_time = $this->request->param('end_time', '');
        $where = [];
        if ($start_time != '') {
            $where['apply_time'] = ['>=', strtotime($start_time)];
        }
        if ($end_time != '') {
            $where['apply_time'] = ['<=', strtotime($end_time)];
        }
        if ($start_time != '' && $end_time != '') {
            $where['apply_time'] = [['>=', strtotime($start_time)], ['<=', strtotime($end_time)]];
        }

        if (strlen($keyword) != 0) {
            $where['realname'] = ['like', "%$keyword%"];
        }

        if ($status != -1) {
            $where['s.status'] = $status;
        }

        $sfzimg = new SfzimgModel();

        $data = $sfzimg->alias('s')
            ->join('__USER__ u', 'u.id = s.user_id');

        $where && $data = $data->where($where);

        $list = $data->field('s.*,u.mobile')->order('apply_time desc')->select();

        if (empty($list))
            $this->error('数据不存在');

        $this->export($list);
    }

    private function export($list){
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
            ->setCellValue('A1','用户名' )
            ->setCellValue('B1','姓名' )
            ->setCellValue('C1','身份证号')
            ->setCellValue('D1','状态')
            ->setCellValue('E1','申请时间');

        for($i=0;$i<count($list);$i++){
            switch ($list[$i]['status']) {
                case 0:
                    $status = "未验证";
                    break;
                case 1:
                    $status = "未通过";
                    break;
                case 2:
                    $status = "通过";
                    break;
                default:
                    $status = "未知";
                    break;
            }
            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A'.($i+2),  $list[$i]['mobile'])
                ->setCellValue('B'.($i+2), $list[$i]['realname'] )
                ->setCellValue('C'.($i+2), $list[$i]['shenfenzheng'] )
                ->setCellValue('D'.($i+2), $status )
                ->setCellValue('E'.($i+2),  date('Y-m-d H:i:s', $list[$i]['apply_time']) );
        }




        // Redirect output to a client’s web browser (Xls)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="活动用户.xls"');
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