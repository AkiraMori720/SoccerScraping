<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 5/28/2020
 * Time: 3:22 PM
 */

require_once "M_DataTable.php";
class M_SoccerVista extends M_DataTable
{
    public function __construct()
    {
        parent::__construct();

        $this->m_strTable = 'matches_soccervista';
    }

    /**
     * ------------------------------------------------------------------------
     *  saveMatches :
     * ========================================================================
     *
     *
     * @param $date
     * @param $matches
     * Updated by C.R. 5/28/2020
     *
     * ------------------------------------------------------------------------
     */
    public function saveMatches($date, $matches) {
        $sql = "SELECT season, country, division FROM base_leagues_recommend WHERE season IN (SELECT season FROM base_seasons WHERE `status`='active')";
        $recLeagues = $this->executeSQLAsArray($sql);

        foreach($matches as $match) {
            $match_time = getValueInArray($match, 'time');
            $country    = getValueInArray($match, 'country');
            $division   = getValueInArray($match, 'division');
            $team1      = getValueInArray($match, 'team_1');
            $team2      = getValueInArray($match, 'team_2');

            $result     = getValueInArray($match, 'result');
            $odds_1     = getValueInArray($match, 'odds_1');
            $odds_x     = getValueInArray($match, 'odds_x');
            $odds_2     = getValueInArray($match, 'odds_2');

            $inf_1x2     = getValueInArray($match, 'inf_1x2');
            $goals      = getValueInArray($match, 'goals');
            $score      = getValueInArray($match, 'score');

            $season = '';
            foreach ($recLeagues as $recLeague) {
                if($recLeague['country'] == $country && $recLeague['division'] == $division) {
                    $season = $recLeague['season'];
                    break;
                }
            }

            $values = array(
                'season'    => $season,
                'date_found'=> $date,
                'match_time'=> $match_time,

                'country'   => $country,
                'division'  => $division,

                'team1'     => $team1,
                'team2'     => $team2,
                'away_team' => isEmptyString(getValueInArray($match, 'team_active')) ? '' : ($team1 != getValueInArray($match, 'team_active') ? $team1 : $team2),

                'result'    => $result,
                'odds_1'    => $odds_1,
                'odds_x'    => $odds_x,
                'odds_2'    => $odds_2,

                'inf_1x2'   => $inf_1x2,
                'goals'     => $goals,
                'score'     => $score,
            );

            $updates = array(
                'season'    => $season,
                'result'    => $result,
                'odds_1'    => $odds_1,
                'odds_x'    => $odds_x,
                'odds_2'    => $odds_2,

                'inf_1x2'   => $inf_1x2,
                'goals'     => $goals,
                'score'     => $score,
            );

            $sql = "INSERT INTO {$this->m_strTable} SET ";
            $sql .= $this->sqlAppendSetValues($values, false);

            $sql_update = $this->sqlAppendSetValues($updates, false);
            if(!isEmptyString($sql_update)) {
                $sql .= " ON DUPLICATE KEY UPDATE {$sql_update}";
            }

            $this->executeSQL($sql);
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
     * Updated by C.R. 5/28/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getMatches_DT($params) {
        $fields = array(
            'index_no',
            'match_time',
            'country',
            'division',
            'team1',
            'team2',
            'result',
            'odds_1',
            'odds_x',
            'odds_2',
            'inf_1x2',
            'goals',
            'score',
            'id'
        );

        // SQL
        $sql_all = "SELECT * 
                    FROM {$this->m_strTable}";

        $filter = "division IN (SELECT soccervista FROM base_similarity WHERE `type`='division' AND oddsportal IN (SELECT division FROM base_leagues_recommend))";
        if(isset($params['country'])) {
            $value = $params['country'];
            if(is_array($value)) {
                if(sizeof($value) > 0) {
                    $filter .= (strlen($filter) > 0 ? " AND " : "")."`country` IN('" . implode("','", $value) . "')";
                }
            }
            else {
                if(strlen($value) > 0) {
                    $filter .= (strlen($filter) > 0 ? " AND " : "")."`country`='{$value}'";
                }
            }
        }

        $dateType = getValueInArray($params, 'dateType');
        if($dateType == 'daily') {
            $value = getValueInArray($params, 'date');
            if (!isEmptyString($value)) {
                $filter .= (strlen($filter) > 0 ? " AND " : "") . "DATE(date_found)='{$value}'";
            }
        }
        else if($dateType == 'weekly') {
            $value = getValueInArray($params, 'week');
            if(!isEmptyString($value) && is_numeric($value)) {
                $year = date('Y');

                $dates = getStartAndEndDateOfWeek($value, $year);
                $filter .= (strlen($filter) > 0 ? " AND " : "") . "(DATE(date_found)>='{$dates['start_date']}' AND DATE(date_found)<='{$dates['end_date']}')";
            }
        }
        else if($dateType == 'monthly') {
            $value = getValueInArray($params, 'month');
            if(!isEmptyString($value) && is_numeric($value)) {
                $filter .= (strlen($filter) > 0 ? " AND " : "") . "MONTH(date_found)='{$value}'";
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
}