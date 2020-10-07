<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 6/23/2020
 * Time: 12:04 PM
 */

require_once "M_DataTable.php";
class M_MatchFinalSummary extends M_DataTable
{
    public function __construct()
    {
        parent::__construct();

        $this->m_strTable = "matches_final_summary";
    }

    /**
     * ------------------------------------------------------------------------
     *  saveSummary :
     * ========================================================================
     *
     *
     * @param $match
     * @param $summary
     * Updated by C.R. 6/23/2020
     *
     * ------------------------------------------------------------------------
     */
    public function saveSummary($match, $summary) {
        if($summary != null && is_array($summary)) {
            $sql = "INSERT INTO {$this->m_strTable} SET match_id=" . $match['id'] . $this->sqlAppendSetValues($summary, true);

            $sql.= " ON DUPLICATE KEY UPDATE " . $this->sqlAppendSetValues($summary, false);

            $this->executeSQL($sql);
        }
    }

    /**
     * ------------------------------------------------------------------------
     *  arrangeRoyPickGrp :
     * ========================================================================
     *
     *
     * Updated by C.R. 7/29/2020
     *
     * ------------------------------------------------------------------------
     */
    public function arrangeRoyPickGrp() {
        $sql = "SELECT IFNULL(MAX(roypick_grp), 0) max_grp FROM matches_final_summary ORDER BY match_at, match_team";
        $rec = $this->executeSQLAsArray($sql);
        $lastMaxGrp = $rec[0]['max_grp'];

        if($lastMaxGrp == '0') {
            $curGrp = 1;
            $curIdx = 1;
        }
        else {
            $sql = "SELECT COUNT(match_id) cnt FROM matches_final_summary WHERE roypick_grp='{$lastMaxGrp}'";
            $rec = $this->executeSQLAsArray($sql);
            $nLastMatches = $rec[0]['cnt'];

            if($nLastMatches >= 8) {
                $curGrp = $lastMaxGrp + 1;
                $curIdx = 1;
            }
            else {
                $curGrp = $lastMaxGrp;
                $curIdx = $nLastMatches + 1;
            }
        }

        $sql = "SELECT * FROM matches_final_summary WHERE ISNULL(roypick_grp) ORDER BY match_at, match_team";
        $recMatches = $this->executeSQLAsArray($sql);
        for($k = 0; $k < sizeof($recMatches); $k++) {
            $values = array(
                'roypick_grp' => $curGrp,
                'roypick_no' => $curIdx
            );

            $sql = "UPDATE matches_final_summary SET " .
                $this->sqlAppendSetValues($values, false) .
                " WHERE id='{$recMatches[$k]['id']}'";
            $this->executeSQL($sql);

            $curIdx ++;
            if($curIdx > 8) {
                $curGrp ++;
                $curIdx = 1;
            }
        }
    }

    /**
     * ------------------------------------------------------------------------
     *  getMatches_DT :
     * ========================================================================
     *
     *
     * @param $params
     * @return array
     * Updated by C.R. 7/1/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getMatches_DT($params) {
        $fields = array(
            'index_no',
            'roypick_grp',
            'match_at',
            'competition',
            'match_team',
            'result_1',
            'result_2',
            'c_spic1',
            'c_spic1_p',
            'c_spic2',
            'c_spic2_p',
            'c_spic3',
            'c_spic3_p',
            'c_spic4',
            'c_spic4_p',
        );

        // SQL
        $sql_all = "SELECT * 
                    FROM {$this->m_strTable}";

        $filter = "";
        if(isset($params['country'])) {
            $value = $params['country'];
            if(is_array($value)) {
                if(sizeof($value) > 0) {
                    $filter .= (strlen($filter) > 0 ? " AND " : "")."SUBSTR(competition, 1, LOCATE('>>', competition) - 2) IN('" . implode("','", $value) . "')";
                }
            }
            else {
                if(strlen($value) > 0) {
                    $filter .= (strlen($filter) > 0 ? " AND " : "")."SUBSTR(competition, 1, LOCATE('>>', competition) - 2)='{$value}'";
                }
            }
        }

        $dateType = getValueInArray($params, 'dateType');
        if($dateType == 'daily') {
            $value = getValueInArray($params, 'date');
            if (!isEmptyString($value)) {
                $filter .= (strlen($filter) > 0 ? " AND " : "") . "DATE(match_at)='{$value}'";
            }
        }
        else if($dateType == 'weekly') {
            $value = getValueInArray($params, 'week');
            if(!isEmptyString($value) && is_numeric($value)) {
                $year = date('Y');

                $dates = getStartAndEndDateOfWeek($value, $year);
                $filter .= (strlen($filter) > 0 ? " AND " : "") . "(DATE(match_at)>='{$dates['start_date']}' AND DATE(match_at)<='{$dates['end_date']}')";
            }
        }
        else if($dateType == 'monthly') {
            $value = getValueInArray($params, 'month');
            if(!isEmptyString($value) && is_numeric($value)) {
                $filter .= (strlen($filter) > 0 ? " AND " : "") . "MONTH(match_at)='{$value}'";
            }
        }

        if(strlen($filter) > 0) { $sql_all .= " WHERE {$filter}"; }

        $data = array();
        $data['recordsTotal'] = $this->getCount($sql_all, 'id');

        $tbl_alias = 'entire';

        ///////////////////////////////////////////////
        // Get Filtered Count
        ///////////////////////////////////////////////
        $sql_flt = "SELECT * FROM (".$sql_all.") ".$tbl_alias;
        $sql_flt = $this->appendFilterToSQL($params, $sql_flt, $tbl_alias, $fields, array('index_no'));

        $data['recordsFiltered'] = $this->getCount($sql_flt, 'id');

        ///////////////////////////////////////////////
        // Get Records of current page
        ///////////////////////////////////////////////
        $sql_flt = $this->appendOrderByToSQL($params, $sql_flt, $tbl_alias, $fields, array('index_no'));
        // Add Limitation; Default page size 50
        $sql_flt = $this->appendLimitToSQL($params, $sql_flt);

        $records = $this->executeSQLAsArray($sql_flt);

        $startNo = $this->getPageStartIndex($params);
        for($i = 0; $i < sizeof($records); $i++) {
            $records[$i]['index_no'] = $startNo + $i + 1;
        }

        $data['data'] = $records;

        // Current Draw
        $data['draw'] = $this->getCurrentDrawNo($params);

        return $data;
    }

    /**
     * ------------------------------------------------------------------------
     *  getMatchesForExcel :
     * ========================================================================
     *
     *
     * @param $params
     * @return mixed
     * Updated by C.R. 7/1/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getMatchesForExcel($params) {
        // SQL
        $sql_all = "SELECT * FROM {$this->m_strTable}";

        $filter = "";
        if(isset($params['country'])) {
            $value = $params['country'];
            if(is_array($value)) {
                if(sizeof($value) > 0) {
                    $filter .= (strlen($filter) > 0 ? " AND " : "")."SUBSTR(competition, 1, LOCATE('>>', competition) - 2) IN('" . implode("','", $value) . "')";
                }
            }
            else {
                if(strlen($value) > 0) {
                    $filter .= (strlen($filter) > 0 ? " AND " : "")."SUBSTR(competition, 1, LOCATE('>>', competition) - 2)='{$value}'";
                }
            }
        }

        $dateType = getValueInArray($params, 'dateType');
        if($dateType == 'daily') {
            $value = getValueInArray($params, 'date');
            if (!isEmptyString($value)) {
                $filter .= (strlen($filter) > 0 ? " AND " : "") . "DATE(match_at)='{$value}'";
            }
        }
        else if($dateType == 'weekly') {
            $value = getValueInArray($params, 'week');
            if(!isEmptyString($value) && is_numeric($value)) {
                $year = date('Y');

                $dates = getStartAndEndDateOfWeek($value, $year);
                $filter .= (strlen($filter) > 0 ? " AND " : "") . "(DATE(match_at)>='{$dates['start_date']}' AND DATE(match_at)<='{$dates['end_date']}')";
            }
        }
        else if($dateType == 'monthly') {
            $value = getValueInArray($params, 'month');
            if(!isEmptyString($value) && is_numeric($value)) {
                $filter .= (strlen($filter) > 0 ? " AND " : "") . "MONTH(match_at)='{$value}'";
            }
        }

        if(strlen($filter) > 0) { $sql_all .= " WHERE {$filter}"; }
        $sql_all .= "  ORDER BY roypick_grp, roypick_no";

        $records = $this->executeSQLAsArray($sql_all);

        for($i = 0; $i < sizeof($records); $i++) {
            $records[$i]['index_no'] = $i + 1;
        }

        return $records;
    }
}