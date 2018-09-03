<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------

namespace app\user\controller;

use cmf\controller\AdminBaseController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use think\Db;

/**
 * Class AdminIndexController
 * @package app\user\controller
 *
 * @adminMenuRoot(
 *     'name'   =>'用户管理',
 *     'action' =>'default',
 *     'parent' =>'',
 *     'display'=> true,
 *     'order'  => 10,
 *     'icon'   =>'group',
 *     'remark' =>'用户管理'
 * )
 *
 * @adminMenuRoot(
 *     'name'   =>'用户组',
 *     'action' =>'default1',
 *     'parent' =>'user/AdminIndex/default',
 *     'display'=> true,
 *     'order'  => 10000,
 *     'icon'   =>'',
 *     'remark' =>'用户组'
 * )
 */
class AdminIndexController extends AdminBaseController
{

    /**
     * 后台本站用户列表
     * @adminMenu(
     *     'name'   => '本站用户',
     *     'parent' => 'default1',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '本站用户',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $where   = [];
        $request = input('request.');

        if (!empty($request['uid'])) {
            $where['id'] = intval($request['uid']);
        }

        if (isset( $request['user_status']) && $request['user_status'] != 999) {
            $where['user_status'] = intval($request['user_status']);
        }

        $keywordComplex = [];
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];

            $keywordComplex['user_login|user_nickname|user_email|mobile']    = ['like', "%$keyword%"];
        }
        $usersQuery = Db::name('user');

        $list = $usersQuery->whereOr($keywordComplex)->where($where)->order("create_time DESC")->paginate(10);

        $list->appends( $where);
        // 获取分页显示
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);


        $this->assign('user_status' , isset( $request['user_status']) ?  $request['user_status'] : 999);
        // 渲染模板输出
        return $this->fetch();
    }

    /**
     * 本站用户拉黑
     * @adminMenu(
     *     'name'   => '本站用户拉黑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '本站用户拉黑',
     *     'param'  => ''
     * )
     */
    public function ban()
    {
        $id = input('param.id', 0, 'intval');
        if ($id) {
            $result = Db::name("user")->where(["id" => $id, "user_type" => 2])->setField('user_status', 0);
            if ($result) {
                $this->success("会员拉黑成功！", "adminIndex/index");
            } else {
                $this->error('会员拉黑失败,会员不存在,或者是管理员！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    public function del()
    {
        $id = input('param.id', 0, 'intval');
        if ($id) {
            //
            $user = Db::name("user")->where(["id" => $id, "user_type" => 2 ])->find();
            empty($user) && $this->error('用户不存在');
            1 == $user['user_type'] && $this->error('不能删除管理用账户');

            $result = Db::name("user")->where(["id" => $id])->delete();
            if ($result) {
                $this->success("删除成功！", "adminIndex/index");
            } else {
                $this->error('删除失败');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    /**
     * 本站用户启用
     * @adminMenu(
     *     'name'   => '本站用户启用',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '本站用户启用',
     *     'param'  => ''
     * )
     */
    public function cancelBan()
    {
        $id = input('param.id', 0, 'intval');
        if ($id) {
            Db::name("user")->where(["id" => $id, "user_type" => 2])->setField('user_status', 1);
            $this->success("会员启用成功！", '');
        } else {
            $this->error('数据传入失败！');
        }
    }

    public function exportUserIndex() {

        $usersQuery = Db::name('user');
        $param = [];
        $where = [];
        $param['user_status'] = input('user_status' , 999,'intval');
        $param['uid'] = input('uid' , 999,'intval');
        $param['keyword'] = input('keyword' , 0,'intval');

        if (isset($param['user_status']) && $param['user_status'] != 999) {
            $where['user_status'] = intval($param['user_status']);
        }

        if (!empty($param['uid'])) {
            $where['id'] = intval($param['uid']);
        }

        $keywordComplex = [];
        if (!empty($param['keyword'])) {
            $keyword = $param['keyword'] ;
            $keywordComplex['user_login|user_nickname|user_email|mobile']    = ['like', "%$keyword%"];
        }

        $list = $usersQuery->whereOr($keywordComplex)->where($where)->order("create_time DESC")->select();

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
            ->setCellValue('B1','注册时间' )
            ->setCellValue('C1','家庭地址')
            ->setCellValue('D1','性别')
            ->setCellValue('E1','年龄')
            ->setCellValue('F1','状态');

        for($i=0;$i<count($list);$i++){

            switch ($list[$i]['sex'] ) {
                case 0:
                    $sex = "保密";
                    break;
                case 1:
                    $sex = "男";
                    break;
                case 2:
                    $sex = "女";
                    break;
                default:
                    $sex = "未知";
                    break;
            }
            switch ($list[$i]['user_status']){
                case 0:
                    $status = "禁用";
                    break;
                case 1:
                    $status = "正常";
                    break;
                case 2:
                    $status = "未验证";
                    break;
                default:
                    $status = "未知";
                    break;
            }

            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A'.($i+2),
                    $list[$i]['user_login'] ? $list[$i]['user_login'] :
                        (
                            $list[$i]['mobile'] ?
                            $list[$i]['mobile'] : lang('THIRD_PARTY_USER')
                        )
                )
                ->setCellValue('B'.($i+2), date('Y-m-d H:i:s', $list[$i]['create_time']))
                ->setCellValue('C'.($i+2), $list[$i]['address'] )
                ->setCellValue('D'.($i+2), $sex )
                ->setCellValue('E'.($i+2), date('Y-m-d H:i:s', $list[$i]['birthday']))
                ->setCellValue('F'.($i+2),  $status );
        }

        // Redirect output to a client’s web browser (Xls)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="本站用户.xls"');
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
