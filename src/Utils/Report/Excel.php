<?php
namespace Laravelladder\Core\Utils\Report;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell as PHPExcel_Cell;
use PhpOffice\PhpSpreadsheet\Shared\Date as  PHPExcel_Shared_Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
/**
 * Class Excel
 *
 * Excel文档生成通用方法
 * @package Laravelladder\Core\Utils\Report
 */
class Excel {
    /**
     * 对Http请求生成唯一八位ID
     *
     * @return string
     */
    public static function makeDownloadFile(array $data = [], $title = "新文件"){
	    // Create new Spreadsheet object
	    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		// Set document properties
	    $spreadsheet->getProperties()->setCreator('行政超人')
		    ->setLastModifiedBy('行政超人')
		    ->setTitle($title)
		    ->setSubject($title)
		    ->setDescription($title)
		    ->setKeywords($title)
		    ->setCategory($title);
	    foreach ($data as $rowId => $rowData){
		    foreach ($rowData as $columnId => $value){
			    // Add some data
			    $spreadsheet
				    ->setActiveSheetIndex(0)
				    ->setCellValueExplicitByColumnAndRow($columnId, $rowId + 1, $value, DataType::TYPE_STRING);
		    }
	    }
		// Rename worksheet
	    $spreadsheet->getActiveSheet()->setTitle('Sheet 1');
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	    $spreadsheet->setActiveSheetIndex(0);
		// Redirect output to a client’s web browser (Xlsx)
	    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	    header('Content-Disposition: attachment;filename="' . $title . '.xlsx"');
	    header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
	    header('Cache-Control: max-age=1');
		// If you're serving to IE over SSL, then the following may be needed
	    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
	    header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
	    header('Pragma: public'); // HTTP/1.0
	    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
	    $writer->save('php://output');
	    exit;
    }

    /**
     * @param $file
     * @param $extension
     * @param $limitRow 单次上传数量限制 不传表示不限制
     * @return array
     */
    public static function importExecl($file, $extension, $limitRow){

        if(!file_exists($file)){
            return [
                "error"=>0,
                'message'=>'file not found!'
            ];
        }

        if($extension == 'xls')
            $objReader = IOFactory::createReader('Xls');
        else
            $objReader = IOFactory::createReader('Xlsx');
        try{

            $PHPReader = $objReader->load($file);
        }catch(\Exception $e){
            return [
                "error"=>0,
                'message'=>'load file fail!'
            ];
        }
        if(!isset($PHPReader)) return ["error"=>0,'message'=>'read error!'];

        $i = 0;
        //获取所有的sheet
        $allSheets = $PHPReader->getAllSheets();

        $data = [];
        //循环处理每一个sheet
        foreach ($allSheets as $sheet){
            //获取有多少行
            $row = $sheet->getHighestRow();
            if($limitRow > 0 && $row > $limitRow){
                return [
                    "error"=>0,
                    'message'=>"单次上传数量不能超过{$limitRow}",
                ];
            }
            //获取有多少列(字母形式的)
            $highestColumn = $sheet->getHighestColumn();
            //转换字母形式的列为数字
            $column = PHPExcel_Cell::columnIndexFromString($highestColumn);

            $arrTmp = [];
            //循环处理每一行
            for($currentRow = 1 ;$currentRow <= $row; $currentRow++){

                $arrRow = [];
                //循环处理列
                for($currentColumn = 0; $currentColumn < $column; $currentColumn++){

                    //获取单元格
                    $cell = $sheet->getCellByColumnAndRow($currentColumn, $currentRow);
                    //获取后一个列号
                    $afCol = PHPExcel_Cell::stringFromColumnIndex($currentColumn+1);
                    //获取前一个列号
                    $bfCol = PHPExcel_Cell::stringFromColumnIndex($currentColumn-1);
                    //获取当前列号
                    $col = PHPExcel_Cell::stringFromColumnIndex($currentColumn);
                    //获取行列坐标号如A1
                    $address = $col.$currentRow;
                    //获取当前坐标单元格内的数据
                    $value = $sheet->getCell($address)->getValue();
                    //判断是否使用公式
                    if(substr($value,0,1) == '='){

                        return [
                            "error" => 0,
                            'message' => "第{$currentRow}行，第{$col}列 不能使用公式{$value}!"
                        ];
                        exit;
                    }

                    //判断单元格数据类型处理单元格数据
                    if($cell->getDataType() == DataType::TYPE_NUMERIC){

                        $cellstyleformat = $cell->getWorksheet()->getStyle( $cell->getCoordinate() )->getNumberFormat();
                        $formatcode = $cellstyleformat->getFormatCode();

                        if (preg_match('/^([$[A-Z]*-[0-9A-F]*])*[hmsdy]/i', $formatcode)) {

                            $value = gmdate("Y-m-d", PHPExcel_Shared_Date::PHPToExcel($value));
                        }else{

                            $value = NumberFormat::toFormattedString($value,$formatcode);
                        }
                    }

                    $arrRow[$currentColumn] = ''.$value;
                }

                $arrTmp[$currentRow] = $arrRow;
            }

            $data[] = $arrTmp;
        }
        unset($allSheets);
        unset($PHPReader);
        unset($PHPExcel);

        return [
            "error" => 1,
            "data" => $data
        ];
    }


	/**
	 * @return string
	 */
	public static function makeDownloadMultiSheetsFile(array $data = [], $title = "新文件"){
		// Create new Spreadsheet object
		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		// Set document properties
		$spreadsheet->getProperties()->setCreator('行政超人')
			->setLastModifiedBy('行政超人')
			->setTitle($title)
			->setSubject($title)
			->setDescription($title)
			->setKeywords($title)
			->setCategory($title);

		$data = array_reverse($data);

		foreach ($data as $key => $d) {
			$worksheet = new Worksheet();
			$worksheet->setTitle($key);
			foreach ($d as $rowId => $rowData) {
				foreach ($rowData as $columnId => $value) {
					// Add some data
					$worksheet
						->setCellValueExplicitByColumnAndRow($columnId, $rowId + 1, $value, DataType::TYPE_STRING);
				}
			}
			$spreadsheet->addSheet($worksheet, false);
		}
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$spreadsheet->setActiveSheetIndex(0);
		// Redirect output to a client’s web browser (Xlsx)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $title . '.xlsx"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');
		// If you're serving to IE over SSL, then the following may be needed
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
		header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header('Pragma: public'); // HTTP/1.0
		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');
		exit;
	}
    /**
     * @return string
     */
    public static function saveDownloadFile(array $data = [], $title = "新文件", $save_file_name){
        // Create new Spreadsheet object
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        // Set document properties
        $spreadsheet->getProperties()->setCreator('行政超人')
            ->setLastModifiedBy('行政超人')
            ->setTitle($title)
            ->setSubject($title)
            ->setDescription($title)
            ->setKeywords($title)
            ->setCategory($title);
        foreach ($data as $rowId => $rowData){
            foreach ($rowData as $columnId => $value){
                // Add some data
                $spreadsheet
                    ->setActiveSheetIndex(0)
                    ->setCellValueExplicitByColumnAndRow($columnId, $rowId + 1, $value, DataType::TYPE_STRING);
            }
        }
        // Rename worksheet
        $spreadsheet->getActiveSheet()->setTitle('Sheet 1');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $spreadsheet->setActiveSheetIndex(0);
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($save_file_name);
        return 'success';
    }
}