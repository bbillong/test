<?php


class fileUpload
{
    //파일 업로드
    public static function uploadFile(
        $fileArray,
        $preFileName = null,
        $targetDirectory,
        $allowExt = null,
        $maxSize = 1,
        $table
    ) {
        $ext = [
            'pdf',
            'dwg',
            'jpg',
            'jpeg',
            'png',
            'gif',
            'tiff',
            'doc',
            'docx',
            'xls',
            'xlsx',
            'ppt',
            'pptx',
            'hwp'
        ];
        if (!$allowExt) {
            $allowExt = $ext;
        }
        $status = false;
        $message = "용량초과";
        $fileName = "";
        $fileNameOri = "";
        $fileSize = "";
        $fileExt = "";
        $path = "";
        $sql = "";

        if ($fileArray['size'] > 0) {
            $maxSize = $maxSize * 1024 * 1024;
            if ($fileArray['size'] > $maxSize) {
                throw new Exception("용량초과 (업로드 가능용량은 ".$maxSize."byte 입니다)");
            } else {
                try {
                    $fileExt = strtolower(
                        trim(end(explode('.', $fileArray['name'])))
                    ); // 파일 확장자

                    // 확장자를 검사
                    if (in_array($fileExt, $allowExt)) {
                        $fileName = uniqid(
                                $preFileName.''.preg_match(" ", "", microtime())
                            ).'.'.$fileExt;
                        //$fileName = uniqid($preFileName.''.preg_match(" ","",microtime())); // 확장자 제거

                        $path = $targetDirectory.'/'.$fileName; // 경로

                        if (!move_uploaded_file(
                            $fileArray['tmp_name'],
                            $path
                        )
                        ) {
                            throw new Exception("업로드 에러");
                        } else {
                            $status = true; // 상태
                            $message = 'success'; // 메시지
                            $fileNameOri = $fileArray['name']; // 업로드 파일명
                            $fileSize = $fileArray['size']; // 파일 크기

                            //엑셀 업로드시
                            if ($fileExt == 'xls' || $fileExt == 'xlsx') {
                                $returnExcel = fileUpload::uploadExcel(
                                    $path,
                                    $table
                                );
                                $status = $returnExcel['status'];
                                $message = $returnExcel['message'];
                                $sql = $returnExcel['data']['sql'];
                                if (!$returnExcel) {
                                    throw new Exception($message);
                                }
                            }
                        }
                    } else {
                        throw new Exception($fileExt." : 허용되지 않는 확장자 입니다");
                    }
                } catch (Exception $e) {
                    $status = false;
                    $message = $e->getMessage();
                }
            }
        }

        return [
            "status"  => $status,
            "message" => $message,
            "data"    => [
                "fileName"    => $fileName,
                "fileNameOri" => $fileNameOri,
                "fileSize"    => $fileSize,
                "fileExt"     => $fileExt,
                "path"        => $path,
                "sql"         => $sql
            ]
        ];
    }

    //엑셀 insert 구문 생성
    public static function uploadExcel($path, $table)
    {
        $dateTime = date("Y-m-d H:i:s");
        $target = $path;
        $sqlStr = "";

        require_once $_SERVER["DOCUMENT_ROOT"]."/extend/PHPExcel.php";
        // $objPHPExcel = new PHPExcel();
        require_once $_SERVER["DOCUMENT_ROOT"]."/extend/PHPExcel/IOFactory.php";

        try {
            // 업로드 된 엑셀 형식에 맞는 Reader객체를 만든다.
            $objReader = PHPExcel_IOFactory::createReaderForFile($target);
            // 읽기전용으로 설정
            $objReader->setReadDataOnly(true);
            // 엑셀파일을 읽는다.
            $objExcel = $objReader->load($target);

            // 첫번째 시트를 선택
            $objExcel->setActiveSheetIndex(0);
            //실행된 시트 반환
            $objWorksheet = $objExcel->getActiveSheet();

            $maxRow = $objWorksheet->getHighestRow();// 시트의 마지막 로우 숫자
            $key_row_index = 5; // 최초 실행 데이터 로우 숫자

            $sql_str = "INSERT INTO	".$table;

            foreach ($objWorksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator(); //셀 호출
                $cellIterator->setIterateOnlyExistingCells(false);
                $datetime = date("Y-m-d H:i:s");

                $row_index = $row->getRowIndex();

                // 제목행 앞의 행은 무시한다.
                if ($row_index < $key_row_index) {
                    continue;
                }

                // $key_row_index에 설정된 줄의 값을 불러와서 배열로 생성.
                if ($row_index == $key_row_index) {
                    $column_title_arr = [];
                    foreach ($cellIterator as $cell) {
                        $column_index = $cell->getColumn();
                        $column_title_arr[$column_index] = str_replace(
                            "\n",
                            '',
                            trim(
                                $cell->getCalculatedValue()
                            )
                        );// 각 row data 배열화
                    }
                    continue;
                }

                foreach ($cellIterator as $cell) {
                    $column_index = $cell->getColumn();
                    $column_title = $column_title_arr[$column_index];
                    if ($column_title == '') {
                        continue;
                    }

                    $sheet[$row_index][$column_title]
                        = $cell->getCalculatedValue();// 각 row data 배열화
                }

                //배열을 쿼리로 변환
                if (count($sheet[$row_index]) == count($column_title_arr)) {
                    $row_temp .= "
                                (
                                    '".implode("','", $sheet[$row_index])."'
                                    , '".$datetime."'
                                )";

                    if ($row_index != $maxRow) {
                        $row_temp .= ",";
                    }
                }
            }

            $sql_str .= "
                    (
                        ".implode(",", $column_title_arr)."
                        , datetime
                    ) VALUES ";
            $sql_str .= $row_temp;


            // 파일 삭제
            unlink($target);

            if ($sqlStr) {
                $status = true;
                $message = 'success';
            } else {
                throw new Exception("엑셀 업로드 실패");
            }
        } catch (Exception $e) {
            $status = false;
            $message = $e->getMessage();
        }

        return [
            "status"  => $status,
            "message" => $message,
            "data"    => [
                "sql" => $sql_str
            ]
        ];
    }
}

