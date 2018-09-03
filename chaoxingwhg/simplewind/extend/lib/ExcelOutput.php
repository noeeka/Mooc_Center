<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 2018/3/6
 * Time: 13:59
 */

namespace lib;

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ExcelOutput
{
    public $filename = '导出Excel示例';
    public $field = null;
    public $data = null;
    public $spreadsheet = null;

    public function __construct($filename = 'output', $field = [], $data = [])
    {
        $this->filename = $filename;
        $this->field = $field;
        $this->data = $data;
    }

    public function getColNum()
    {
        if (empty($this->field)) {
            return count($this->data[0]);
        } else {
            return $this->field;
        }
    }

    public function createSpreadsheet()
    {
        if ($this->spreadsheet == null) {
            $this->spreadsheet = new Spreadsheet();
            $this->spreadsheet->getProperties()->setCreator('超星集团天津分公司')
                ->setTitle($this->filename)
                ->setCompany('超星集团天津分公司')
                ->setLastModifiedBy('超星集团天津分公司')
                ->setSubject('Excel导出');

        }
        return $this;
    }

    public function outputBrowser()
    {
        $helper = new Sample();
        if ($helper->isCli()) {
            $helper->log('This example should only be run from a Web Browser' . PHP_EOL);
            return;
        }
        $this->createSpreadsheet()->buildField()->buildData();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$this->filename.'.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        $writer = IOFactory::createWriter($this->spreadsheet, 'Xlsx');
        $writer->save('php://output');
        die;
    }



    function buildField()
    {
        $startChar = 'A';
        if (!empty($this->field)) {
            $col_num = count($this->field);
            for ($i = 0; $i <$col_num; $i++) {
                $coor = chr(ord($startChar) + $i) . '1';
                $this->spreadsheet->setActiveSheetIndex(0)->setCellValue($coor, $this->field[$i]);
            }
        }
        return $this;
    }

    function buildData()
    {
        $startChar = 'A';
        if (empty($this->field)) {
            $i = 1;
        } else {
            $i = 2;
        }
        foreach ($this->data as $k => $v) {
            $j = 0;
            foreach ($v as $v1) {
                $coor = chr(ord($startChar) + $j) . ($i);
                $this->spreadsheet->setActiveSheetIndex(0)->setCellValue($coor, $v1);
                $j++;
            }
            $i++;
        }
        return $this;
    }
}