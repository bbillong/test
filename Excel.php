<?php

namespace App\Lib;


use Exception;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Excel
{
    //엑셀 insert 구문 생성
    /*
     * laravel
     * $val : 업로드 파일 정보
     * $table : 업로드 파일 정보가 저장될 테이블 명
     * $keyRowIndex : 칼럼명이 들어가있는 곳의 row 번호
     * */
    public static function upload(
        $val,
        $table,
        $keyRowIndex = 1,
        $dataRowIndex = 2
    ) {
        $dateTime = date('Y-m-d H:i:s');
        $target = storage_path('app/'.$val['path']);
        $sqlStr = '';
        $created_staff_code = 'admin';

        // $objPHPExcel = new PHPExcel();

        try {
            // 업로드 된 엑셀 형식에 맞는 Reader객체를 만든다.
            $objReader = IOFactory::createReaderForFile($target);
            // 읽기전용으로 설정
            $objReader->setReadDataOnly(true);
            // 엑셀파일을 읽는다.
            $objExcel = $objReader->load($target);

            // 첫번째 시트를 선택
            $objExcel->setActiveSheetIndex(0);
            //실행된 시트 반환
            $objWorksheet = $objExcel->getActiveSheet();

            $maxRow = $objWorksheet->getHighestRow();// 시트의 마지막 로우 숫자

            $sqlStr = "INSERT INTO	".$table;
            $rowTemp = '';
            foreach ($objWorksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator(); //셀 호출
                $cellIterator->setIterateOnlyExistingCells(false);
                $datetime = date('Y-m-d H:i:s');

                $rowIndex = $row->getRowIndex();

                // 제목행 앞의 행은 무시한다.
                if ($rowIndex < $keyRowIndex) {
                    continue;
                }

                // $keyRowIndex에 설정된 줄의 값을 불러와서 배열로 생성.
                if ($rowIndex == $keyRowIndex) {
                    $columnTitleArr = [];
                    foreach ($cellIterator as $cell) {
                        $column_index = $cell->getColumn();
                        $titleValue = str_replace(
                            "\n",
                            '',
                            trim($cell->getValue())
                        );
                        if (!$titleValue) {
                            continue;
                        }
                        $columnTitleArr[$column_index]
                            = $titleValue;// 각 row data 배열화

                    }
                    continue;
                }

                //실제 데이터 행 이전의 데이터는 무시
                if ($rowIndex < $dataRowIndex) {
                    continue;
                }

                foreach ($cellIterator as $cell) {
                    $column_index = $cell->getColumn();

                    if (!isset($columnTitleArr[$column_index])) {
                        continue;
                    }
                    $column_title = $columnTitleArr[$column_index];
                    $sheet[$rowIndex][$column_title] = $cell->getValue(
                    );// 각 row data 배열화
                }

                //배열을 쿼리로 변환
                $rowTemp .= "
                                (
                                    '".implode("','", $sheet[$rowIndex])."'
                                    , '".$datetime."'
                                    , '".$created_staff_code."'
                                )";

                if ($rowIndex != $maxRow) {
                    $rowTemp .= ',';
                }
            }

            $sqlStr .= "
                    (
                        ".implode(",", $columnTitleArr)."
                        , created_at
                        , created_staff_code
                    ) VALUES ";
            $sqlStr .= $rowTemp;

            // 파일 삭제
            //unlink($target);
            Storage::delete($target);

            if ($sqlStr) {
                $status = true;
                $message = 'success';
            } else {
                throw new Exception('엑셀 업로드 실패');
            }
        } catch (Exception $e) {
            $status = false;
            $message = $e->getMessage();
        }

        return [
            'status' => $status,
            'message' => $message,
            'data' => [
                'sql' => $sqlStr
            ]
        ];
    }

    //배열 리턴
    public static function upload2($val, $table, $keyRowIndex = 1, $dataRowIndex = 2)
    {
        $dateTime = date('Y-m-d H:i:s');
        $target = storage_path('app/'.$val['path']);
        $sqlStr = '';
        $created_staff_code = 'admin';

        // $objPHPExcel = new PHPExcel();

        try {
            // 업로드 된 엑셀 형식에 맞는 Reader객체를 만든다.
            $objReader = IOFactory::createReaderForFile($target);
            // 읽기전용으로 설정
            $objReader->setReadDataOnly(true);
            // 엑셀파일을 읽는다.
            $objExcel = $objReader->load($target);

            // 첫번째 시트를 선택
            $objExcel->setActiveSheetIndex(0);
            //실행된 시트 반환
            $objWorksheet = $objExcel->getActiveSheet();

            $maxRow = $objWorksheet->getHighestRow();// 시트의 마지막 로우 숫자

            $sqlStr = "INSERT INTO	".$table;
            $rowTemp = '';
            $sqlArray = [];
            foreach($objWorksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator(); //셀 호출
                $cellIterator->setIterateOnlyExistingCells(FALSE);
                $datetime = date('Y-m-d H:i:s');

                $rowIndex = $row->getRowIndex();

                // 제목행 앞의 행은 무시한다.
                if ($rowIndex < $keyRowIndex) {
                    continue;
                }

                // $keyRowIndex에 설정된 줄의 값을 불러와서 배열로 생성.
                if ($rowIndex == $keyRowIndex) {
                    $columnTitleArr = [];
                    foreach ($cellIterator as $cell) {
                        $column_index = $cell->getColumn();
                        $titleValue = str_replace("\n", '', trim($cell->getValue()));
                        if(!$titleValue) {
                            continue;
                        }
                        $columnTitleArr[$column_index] = $titleValue;// 각 row data 배열화

                    }
                    continue;
                }

                //실제 데이터 행 이전의 데이터는 무시
                if ($rowIndex < $dataRowIndex) {
                    continue;
                }

                foreach ($cellIterator as $cell) {
                    $column_index = $cell->getColumn();

                    if (!isset($columnTitleArr[$column_index])) {
                        continue;
                    }
                    $column_title = $columnTitleArr[$column_index];
                    $sheet[$rowIndex][$column_title] = $cell->getValue();// 각 row data 배열화
                }

                //배열 리턴
                array_push($sqlArray, $sheet[$rowIndex]);
            }

            // 파일 삭제
            //unlink($target);
            Storage::delete($target);

            if ($sqlStr) {
                $status = true;
                $message = 'success';
            } else {
                throw new Exception('엑셀 업로드 실패');
            }
        } catch (Exception $e) {
            $status = false;
            $message = $e->getMessage();
        }

        return [
            'status'  => $status,
            'message' => $message,
            'data' => [
                'sqlArray' => $sqlArray
            ]
        ];
    }
}