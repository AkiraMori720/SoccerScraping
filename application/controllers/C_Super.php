<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 5/16/2017
 * Time: 10:30 AM
 */

use PhpOffice\PhpSpreadsheet\Exception as XlsxException;
use PhpOffice\PhpSpreadsheet\IOFactory as XlsxIOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment as XlsxAlignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class C_Super extends MY_Controller
{

    var $m_curUser = null;

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Response', 'response');
        $this->check_user_login();
    }

    /**
     * ------------------------------------------------------------------------
     *  check_user_login :
     * ========================================================================
     *
     *
     *
     * ------------------------------------------------------------------------
     */
    public function check_user_login() {
        $uriStr = $this->uri->uri_string();

        $this->logUserAction($this->getCurrentUser() != null ? $this->getCurrentUser()['user_name'] : '', $uriStr);

        $escapeActions = array(
            'change_lang',
            'errors/page_404',
            'errors/page_error',
            'errors/_error_add',

            '_login',
            '_logout',
            '_register',
            '_forgot',

            'user/index',
            'user/login',
            'user/_login',
            'user/x_login',
            'user/logout',
            'user/_logout',
            'user/x_logout',
            'user/register',
            'user/_register',
            'user/x_register',
            'user/forgot',
            'user/_forgot',
            'user/x_forgot',
            'user/x_get_captcha',
            'user/x_check_login',

            'upload/index',
            'upload/upload_file',
            'upload/x_upload',

            'api/x_fetch_leagues',
            'api/x_fetch_clubs',
            'api/x_fetch_teams',
            'api/x_fetch_refer',
            'api/x_fetch_ranks',
            'api/x_analyze_matches'
        );

        // Exceptions
        if( in_array($uriStr, $escapeActions) || isEmptyString($uriStr) ) {
            return;
        }

        // Check if request is ajax
        if(isAjaxRequest()) {
            // Check Login
            if(!is_Login($this->session)) {
                $this->response->m_Code = RES_C_REQUIRE_LOGIN;
                $this->printResponse();
                exit(1);
            }
            else {
                $curUser = $this->getCurrentUser();
                $curUserDetail = $this->user->getFullUserInfo($curUser['uid']);
                $this->m_curUser = $curUserDetail;
            }
        }
        else {
            if(!is_Login($this->session)) {
                redirect("user/index");
            }
            else {
                $curUser = $this->getCurrentUser();
                $curUserDetail = $this->user->getFullUserInfo($curUser['uid']);
                $this->m_curUser = $curUserDetail;
            }
        }
    }

    /**
     * ------------------------------------------------------------------------
     *  download :
     * ========================================================================
     *  Updated by C.R. on 10/3/2019
     *
     *
     * ------------------------------------------------------------------------
     */
    public function download() {
        $file = getValueInArray($_GET, 'file');
        $name = getValueInArray($_GET, 'name');
        $delete = getValueInArray($_GET, 'delete');

        $this->doDownload($file, $name, !in_array(strtolower($delete), array('0', 'false')));
    }

    /**
     * ------------------------------------------------------------------------
     *  doDownload :
     * ========================================================================
     *
     *
     * @param $file
     * @param $fakeFileName
     * @param bool $deleteFile
     *
     * ------------------------------------------------------------------------
     */
    protected function doDownload( $file, $fakeFileName, $deleteFile = true ) {

        $path = pathinfo($file, PATHINFO_DIRNAME);
        if(isEmptyString($path) || $path == '.') {
            $file = ROOT_PATH . "/" . TEMP_PATH . "/" . $file;
        }

        if( ! $fp = fopen ( $file, 'rb' ) ) {
            die("Cannot Open File!");
        }
        else {
            session_write_close();

            //$fakeFileName = encodeToChinese($fakeFileName);

            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            /* Assign the appropriate Content-Type header for the file and send file to subscribers browser */

            if ( $extension == 'jpg' ) {
                header ( 'Content-Type: image/jpg' );
            } elseif ( $extension == 'jpeg' ) {
                header ( 'Content-Type: image/jpeg' );
            } elseif ( $extension == 'png' ) {
                header ( 'Content-Type: image/png' );
            } elseif ( $extension == 'bmp' ) {
                header ( 'Content-Type: image/bmp' );
            } elseif ( $extension == 'zip' ) {
                header ( "Content-type: application/zip" );
                header ( "Content-Encoding: zip" );
            } elseif ( $extension == 'gif' ) {
                header ( 'Content-Type: image/gif' );
            } elseif ( $extension == 'txt' ) {
                header ( 'Content-Type: text/plain' );
            } elseif ( $extension == 'csv' ) {
                header ( 'Content-Type: text/csv' );
            } elseif ( $extension == 'xls' ) {
                header ( 'Content-Type: application/vnd.ms-excel' );
            } elseif ( $extension == 'xlsx' ) {
                header ( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
            } elseif ( $extension == 'doc' ) {
                header ( "Content-type: application/msword" );
            } elseif ( $extension == 'dot' ) {
                header ( "Content-type: application/msword" );
            } elseif ( $extension == 'docx' ) {
                header ( "Content-type: application/vnd.openxmlformats-officedocument.wordprocessingml.document" );
            } elseif ( $extension == 'dotx' ) {
                header ( "Content-type: application/vnd.openxmlformats-officedocument.wordprocessingml.template" );
            } elseif ( $extension == 'pdf' ) {
                header ( "Content-type: application/pdf" );
            } elseif ( $extension == 'mp3' ) {
                header ( "Content-Type: application/octet-stream" );
            } elseif ( $extension == 'mp4' ) {
                header ( "Content-Type: video/mp4" );
            } elseif ( $extension == 'mobi' ) {
                header ( "Content-Type: application/octet-stream" );
            } elseif ( $extension == 'epub' ) {
                header ( "Content-Type: application/epub+zip" );
            }

//            $fakeFileName = urlencode($fakeFileName);

            header ( "Content-Transfer-Encoding: binary" );
            header ( "Content-Disposition: attachment; filename=\"{$fakeFileName}.{$extension}\"" );
            header ( "Content-Length: " . filesize ( $file ) . "" );
            fpassthru ( $fp );
            fclose($fp);

            if($deleteFile) {
                unlink($file);
            }

            exit();
        }
    }

    /**
     * ------------------------------------------------------------------------
     *  exportDataToExcel :
     * ========================================================================
     *
     *
     * @param $exportPath
     * @param $title
     * @param $fields
     * @param $values
     * @param $templateFile
     * @param int $startRow
     * @param int $startCol
     * @return string
     * Updated by C.R. 6/2/2020
     *
     * ------------------------------------------------------------------------
     */
    protected function exportDataToExcel($exportPath, $title, $fields, $values, $templateFile, $startRow = 0, $startCol = 0) {
        $fileName = getMilliseconds() . ".xlsx";
        $excelPath = $exportPath . DIRECTORY_SEPARATOR . $fileName;

        try {
            $spreadsheet = XlsxIOFactory::load($templateFile);
            $worksheet = $spreadsheet->getActiveSheet();

            // Write Title
            $worksheet->setTitle($title);

            // Write Values
            for ($rowNo = 0; $rowNo < sizeof($values); $rowNo ++) {
                $row = $values[$rowNo];

                for($colNo = 0; $colNo < sizeof($fields); $colNo++) {
                    $field = $fields[$colNo];
                    $value = getValueInArray($row, $field);

                    $cellName = getExcelColNameFromIndex($colNo + $startCol) . ($rowNo + $startRow);
                    $worksheet->setCellValue($cellName, $value);

                    // $worksheet->getStyle($cellName)->getAlignment()->setHorizontal(XlsxAlignment::HORIZONTAL_CENTER);
                    if($rowNo > 0) {
                        $cellStyle  = $worksheet->getStyleByColumnAndRow($startCol + $colNo, $rowNo + $startRow - 1);
                        // $targetCell = $worksheet->getCellByColumnAndRow($colNo + $startCol, $rowNo + $startRow);
                        $cellStyle = $worksheet->getCell(getExcelColNameFromIndex($startCol + $colNo) . ($rowNo + $startRow - 1))->getStyle();

                        $cellNameFrom = getExcelColNameFromIndex($startCol + $colNo) . ($rowNo + $startRow);
                        $cellNameTo   = getExcelColNameFromIndex($startCol + $colNo) . ($rowNo + $startRow);

                        $cellRange = $cellNameFrom . ":" . $cellNameTo;
                        $worksheet->duplicateStyle($cellStyle, $cellRange);
                    }
                }
            }

            if(file_exists($excelPath)) {
                unlink($excelPath);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($excelPath);

        }
        catch(XlsxException $e) {
            echo $e->getMessage();
        }

        if(!file_exists($excelPath)) {
            $excelPath = '';
        }

        return $excelPath;
    }

    /**
     * ------------------------------------------------------------------------
     *  exportDataToExcelWithValues :
     * ========================================================================
     *
     *
     * @param $exportPath
     * @param $title
     * @param $values
     * @param $templateFile
     * @param bool $bSaveFile
     * @return array
     * @throws Exception
     * Updated by C.R. 6/23/2020
     *
     * ------------------------------------------------------------------------
     */
    protected function exportDataToExcelWithValues($exportPath, $title, $values, $templateFile, $bSaveFile = false) {
        $resultValues = array();

        $fileName = getMilliseconds() . ".xlsx";
        if(isEmptyString(pathinfo($exportPath, PATHINFO_EXTENSION))) {
            $excelPath = $exportPath . "/{$fileName}";
        }
        else {
            $excelPath = $exportPath;
        }

        try {
            $spreadsheet = XlsxIOFactory::load($templateFile);
//            $worksheet = $spreadsheet->getActiveSheet();

            // Write Values
            foreach ($values as $sheetIdx => $sheetValues) {
                $currentSheet = $spreadsheet->getSheet($sheetIdx);

                // Write Title
//                if($sheetIdx == 0) {
//                    $currentSheet->setTitle($title);
//                }

                foreach ($sheetValues as $rowNo => $colValues) {
                    foreach ($colValues as $colNo => $cellValue) {
                        $cellName = getExcelColNameFromIndex($colNo) . ($rowNo);

//                        if($sheetIdx > 0) {
//                            echo "{$cellName} -> {$cellValue}" . PHP_EOL;
//                        }

                        $currentSheet->setCellValue($cellName, $cellValue);

                        // $currentSheet->getStyle($cellName)->getAlignment()->setHorizontal(XlsxAlignment::HORIZONTAL_CENTER);
                    }
                }
            }

            // Additional For BC49 ~ BJ49
            $arrTemp = array();
            for($k = 0; $k < 8; $k++) {
                $arrTemp[] = $spreadsheet->getSheet(0)->getCell(getExcelColNameFromIndex(54 + $k) . "47")->getCalculatedValue();
            }

            $arrTemp = array_unique($arrTemp);
            $k = 0;
            foreach ($arrTemp as $key => $val) {
                $cellName = getExcelColNameFromIndex(54 + $k) . (49);
                $spreadsheet->getSheet(0)->setCellValue($cellName, $val);

                $k++;
            }

            // Additional For CF30 ~ CN30
            $arrTemp = array();
            for($k = 0; $k < 8; $k++) {
                $arrTemp[] = $spreadsheet->getSheet(0)->getCell(getExcelColNameFromIndex(74 + $k) . "30")->getCalculatedValue();
            }

            $arrTemp = array_unique($arrTemp);
            $k = 0;
            foreach ($arrTemp as $key => $val) {
                $cellName = getExcelColNameFromIndex(83 + $k) . (30);
                $spreadsheet->getSheet(0)->setCellValue($cellName, $val);

                $k++;
            }

            // Additional For CG35 ~ CN35
            $arrTemp = array();
            for($k = 0; $k < 8; $k++) {
                $arrTemp[] = $spreadsheet->getSheet(0)->getCell(getExcelColNameFromIndex(75 + $k) . "35")->getCalculatedValue();
            }

            $arrTemp = array_unique($arrTemp);
            $k = 0;
            foreach ($arrTemp as $key => $val) {
                $cellName = getExcelColNameFromIndex(84 + $k) . (35);
                $spreadsheet->getSheet(0)->setCellValue($cellName, $val);

                $k++;
            }

            // Additional For CR29 ~ DG29
            $arrTemp = array();
            for($k = 0; $k < 16; $k++) {
                $arrTemp[] = $spreadsheet->getSheet(0)->getCell(getExcelColNameFromIndex(95 + $k) . "28")->getCalculatedValue();
            }

            $arrTemp = array_unique($arrTemp);
            $k = 0;
            foreach ($arrTemp as $key => $val) {
                $cellName = getExcelColNameFromIndex(95 + $k) . (29);
                $spreadsheet->getSheet(0)->setCellValue($cellName, $val);

                $k++;
            }

            // Additional For CR32 ~ CY32
            $arrTemp = array();
            for($k = 0; $k < 8; $k++) {
                $arrTemp[] = $spreadsheet->getSheet(0)->getCell(getExcelColNameFromIndex(95 + $k) . "31")->getCalculatedValue();
            }

            $arrTemp = array_unique($arrTemp);
            $k = 0;
            foreach ($arrTemp as $key => $val) {
                $cellName = getExcelColNameFromIndex(95 + $k) . (32);
                $spreadsheet->getSheet(0)->setCellValue($cellName, $val);

                $k++;
            }

            $resultValues = array(
                'match_week'    => $spreadsheet->getSheet(0)->getCell("B101")->getCalculatedValue(),
                'match_at'      => $spreadsheet->getSheet(0)->getCell("C101")->getCalculatedValue(),
                'competition'   => $spreadsheet->getSheet(0)->getCell("D101")->getCalculatedValue(),
                'match_time'    => $spreadsheet->getSheet(0)->getCell("E101")->getCalculatedValue(),
                'match_team'    => $spreadsheet->getSheet(0)->getCell("F101")->getCalculatedValue(),
                'match_result'  => $spreadsheet->getSheet(0)->getCell("G101")->getCalculatedValue(),
                'match_odds_1'  => $spreadsheet->getSheet(0)->getCell("H101")->getCalculatedValue(),
                'match_odds_x'  => $spreadsheet->getSheet(0)->getCell("I101")->getCalculatedValue(),
                'match_odds_2'  => $spreadsheet->getSheet(0)->getCell("J101")->getCalculatedValue(),
                'match_bookmark'=> $spreadsheet->getSheet(0)->getCell("K101")->getCalculatedValue(),
                'match_sv_1x2'  => $spreadsheet->getSheet(0)->getCell("L101")->getCalculatedValue(),
                'match_sv_ou'   => $spreadsheet->getSheet(0)->getCell("M101")->getCalculatedValue(),
                'match_sv_cs'   => $spreadsheet->getSheet(0)->getCell("N101")->getCalculatedValue(),
                'match_wdw_1x2' => $spreadsheet->getSheet(0)->getCell("O101")->getCalculatedValue(),
                'match_wdw_cs'  => $spreadsheet->getSheet(0)->getCell("P101")->getCalculatedValue(),
                'match_rp2_1x2' => $spreadsheet->getSheet(0)->getCell("Q101")->getCalculatedValue(),
                'match_rp2_cs'  => $spreadsheet->getSheet(0)->getCell("R101")->getCalculatedValue(),
                'match_p_idx'   => $spreadsheet->getSheet(0)->getCell("S101")->getCalculatedValue(),
                'match_sw_link' => $spreadsheet->getSheet(0)->getCell("T101")->getCalculatedValue(),
                'picks_avg'     => $spreadsheet->getSheet(0)->getCell("U101")->getCalculatedValue(),
                'picks_fz'      => $spreadsheet->getSheet(0)->getCell("V101")->getCalculatedValue(),
                'picks_1x2'     => $spreadsheet->getSheet(0)->getCell("W101")->getCalculatedValue(),
                'sv_1x2'        => $spreadsheet->getSheet(0)->getCell("X101")->getCalculatedValue(),
                'sv_cs1'        => $spreadsheet->getSheet(0)->getCell("Y101")->getCalculatedValue(),
                'wdw_1x2'       => $spreadsheet->getSheet(0)->getCell("Z101")->getCalculatedValue(),
                'wdw_cs2'       => $spreadsheet->getSheet(0)->getCell("AA101")->getCalculatedValue(),
                'prdz_1x2'      => $spreadsheet->getSheet(0)->getCell("AB101")->getCalculatedValue(),
                'prdz_cs3'      => $spreadsheet->getSheet(0)->getCell("AC101")->getCalculatedValue(),
                'roy_1x2'       => $spreadsheet->getSheet(0)->getCell("AD101")->getCalculatedValue(),
                'roy_cs4'       => $spreadsheet->getSheet(0)->getCell("AE101")->getCalculatedValue(),
                'roy_percent'   => $spreadsheet->getSheet(0)->getCell("AF101")->getCalculatedValue(),
                'roy_sg'        => $spreadsheet->getSheet(0)->getCell("AG101")->getCalculatedValue(),
                'roy_cs5'       => $spreadsheet->getSheet(0)->getCell("AH101")->getCalculatedValue(),
                'roy_1'         => $spreadsheet->getSheet(0)->getCell("AI101")->getCalculatedValue(),
                'roy_x'         => $spreadsheet->getSheet(0)->getCell("AJ101")->getCalculatedValue(),
                'roy_2'         => $spreadsheet->getSheet(0)->getCell("AK101")->getCalculatedValue(),
                'result_1'      => $spreadsheet->getSheet(0)->getCell("AO101")->getCalculatedValue(),
                'result_2'      => $spreadsheet->getSheet(0)->getCell("AP101")->getCalculatedValue(),
                'c_spic1'       => $spreadsheet->getSheet(0)->getCell("AQ101")->getCalculatedValue(),
                'c_spic1_p'     => $spreadsheet->getSheet(0)->getCell("AR101")->getCalculatedValue(),
                'c_spic2'       => $spreadsheet->getSheet(0)->getCell("AS101")->getCalculatedValue(),
                'c_spic2_p'     => $spreadsheet->getSheet(0)->getCell("AT101")->getCalculatedValue(),
                'c_spic3'       => $spreadsheet->getSheet(0)->getCell("AU101")->getCalculatedValue(),
                'c_spic3_p'     => $spreadsheet->getSheet(0)->getCell("AV101")->getCalculatedValue(),
                'c_spic4'       => $spreadsheet->getSheet(0)->getCell("AW101")->getCalculatedValue(),
                'c_spic4_p'     => $spreadsheet->getSheet(0)->getCell("AX101")->getCalculatedValue(),
                'rfz_o15'       => $spreadsheet->getSheet(0)->getCell("AY101")->getCalculatedValue(),
                'rfz_o25'       => $spreadsheet->getSheet(0)->getCell("AZ101")->getCalculatedValue(),
                'rfz_cs4'       => $spreadsheet->getSheet(0)->getCell("BA101")->getCalculatedValue(),
                'rfz_cs5'       => $spreadsheet->getSheet(0)->getCell("BB101")->getCalculatedValue(),
                'rfz_scrd'      => $spreadsheet->getSheet(0)->getCell("BC101")->getCalculatedValue(),
                'rfz_concd'     => $spreadsheet->getSheet(0)->getCell("BD101")->getCalculatedValue(),
                'rfz_bts'       => $spreadsheet->getSheet(0)->getCell("BE101")->getCalculatedValue(),
                'rfz_sg2'       => $spreadsheet->getSheet(0)->getCell("BF101")->getCalculatedValue(),
                'rfz_sg3'       => $spreadsheet->getSheet(0)->getCell("BG101")->getCalculatedValue(),
                'rfz_cs1'       => $spreadsheet->getSheet(0)->getCell("BH101")->getCalculatedValue(),
                'rfz_cs2'       => $spreadsheet->getSheet(0)->getCell("BI101")->getCalculatedValue(),
                'rfz_cs3'       => $spreadsheet->getSheet(0)->getCell("BJ101")->getCalculatedValue(),
                'rfz_25'        => $spreadsheet->getSheet(0)->getCell("BK101")->getCalculatedValue(),
                'rfz_sg'        => $spreadsheet->getSheet(0)->getCell("BL101")->getCalculatedValue(),
                'e_picks_avg'   => $spreadsheet->getSheet(0)->getCell("BM101")->getCalculatedValue(),
                'e_picks_1x2'   => $spreadsheet->getSheet(0)->getCell("BN101")->getCalculatedValue(),
                'first_r'       => $spreadsheet->getSheet(0)->getCell("BO101")->getCalculatedValue(),
                'first_p'       => $spreadsheet->getSheet(0)->getCell("BP101")->getCalculatedValue(),
                'second_r'      => $spreadsheet->getSheet(0)->getCell("BQ101")->getCalculatedValue(),
                'second_p'      => $spreadsheet->getSheet(0)->getCell("BR101")->getCalculatedValue(),
                'third_r'       => $spreadsheet->getSheet(0)->getCell("BS101")->getCalculatedValue(),
                'third_p'       => $spreadsheet->getSheet(0)->getCell("BT101")->getCalculatedValue(),
                'fourth_r'      => $spreadsheet->getSheet(0)->getCell("BU101")->getCalculatedValue(),
                'fourth_p'      => $spreadsheet->getSheet(0)->getCell("BV101")->getCalculatedValue(),
                'p_odds_1'      => $spreadsheet->getSheet(0)->getCell("BW101")->getCalculatedValue(),
                'p_odds_x'      => $spreadsheet->getSheet(0)->getCell("BX101")->getCalculatedValue(),
                'p_odds_2'      => $spreadsheet->getSheet(0)->getCell("BY101")->getCalculatedValue(),
                'p_roy1_1'      => $spreadsheet->getSheet(0)->getCell("BZ101")->getCalculatedValue(),
                'p_roy1_x'      => $spreadsheet->getSheet(0)->getCell("CA101")->getCalculatedValue(),
                'p_roy1_2'      => $spreadsheet->getSheet(0)->getCell("CB101")->getCalculatedValue(),
                'p_roy2_1'      => $spreadsheet->getSheet(0)->getCell("CC101")->getCalculatedValue(),
                'p_roy2_x'      => $spreadsheet->getSheet(0)->getCell("CD101")->getCalculatedValue(),
                'p_roy2_2'      => $spreadsheet->getSheet(0)->getCell("CE101")->getCalculatedValue(),
                'p_roy3_1'      => $spreadsheet->getSheet(0)->getCell("CF101")->getCalculatedValue(),
                'p_roy3_x'      => $spreadsheet->getSheet(0)->getCell("CG101")->getCalculatedValue(),
                'p_roy3_2'      => $spreadsheet->getSheet(0)->getCell("CH101")->getCalculatedValue(),
                'p_roy4_1'      => $spreadsheet->getSheet(0)->getCell("CI101")->getCalculatedValue(),
                'p_roy4_x'      => $spreadsheet->getSheet(0)->getCell("CJ101")->getCalculatedValue(),
                'p_roy4_2'      => $spreadsheet->getSheet(0)->getCell("CK101")->getCalculatedValue()
            );

//            echo $spreadsheet->getSheet(0)->getCell("DH4")->getCalculatedValue() . PHP_EOL;
//            echo $spreadsheet->getSheet(0)->getCell("DH5")->getCalculatedValue() . PHP_EOL;

            if($bSaveFile) {
                if (file_exists($excelPath)) {
                    unlink($excelPath);
                }

                $writer = new Xlsx($spreadsheet);
                $writer->save($excelPath);
            }

        }
        catch(Exception $e) {
            throw new Exception($e);
        }

        if($bSaveFile) {
            if (!file_exists($excelPath)) {
                throw new Exception("failed to save excel!");
            }
        }

        return $resultValues;
    }
}
