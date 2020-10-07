<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 5/29/2020
 * Time: 10:26 PM
 */

require_once "M_DataTable.php";
class M_MatchFinal extends M_DataTable
{
    public function __construct()
    {
        parent::__construct();

        $this->m_strTable = 'matches_final';
    }

    /**
     * ------------------------------------------------------------------------
     *  saveMatches :
     * ========================================================================
     *
     *
     * @param $matches
     * Updated by C.R. 5/29/2020
     *
     * ------------------------------------------------------------------------
     */
    public function saveMatches($matches) {
        for ($i = 0; $i < sizeof($matches); $i++) {
            $match = $matches[$i];

            $oddsportal_id      = getValueInArray($match, 'id');
            $oddsportal_date    = getValueInArray($match, 'date_found');
            $oddsportal_time    = getValueInArray($match, 'match_time');

            $country    = getValueInArray($match, 'country');
            $division   = getValueInArray($match, 'division');
            $team1      = getValueInArray($match, 'team1');
            $team2      = getValueInArray($match, 'team2');
            $away       = getValueInArray($match, 'away_team');

            if($team1 == $away) {
                $home_team = $team2;
                $away_team = $team1;
            }
            else {
                $home_team = $team1;
                $away_team = $team2;
            }

            $result      = getValueInArray($match, 'result');
            $odds_1      = getValueInArray($match, 'odds_1');
            $odds_x      = getValueInArray($match, 'odds_x');
            $odds_2      = getValueInArray($match, 'odds_2');
            $bookmark    = getValueInArray($match, 'bookmark');

            $soccervista_1x2    = getValueInArray($match, 'inf_1x2');
            $soccervista_goal   = getValueInArray($match, 'goals');
            $soccervista_cs     = getValueInArray($match, 'cs');

            $predictz_result    = getValueInArray($match, 'predictz_result');
            $predictz_score     = getValueInArray($match, 'predictz_score');

            $windrawwin_1x1     = getValueInArray($match, 'windrawwin_1x1');
            $windrawwin_score   = getValueInArray($match, 'windrawwin_score');
            $windrawwin_result  = getValueInArray($match, 'windrawwin_result');

            $soccerway_link     = getValueInArray($match, 'soccerway');

            $values = array(
                'oddsportal_id' => $oddsportal_id,

                'date_found'    => $oddsportal_date,
                'match_time'    => $oddsportal_time,

                'country'       => $country,
                'division'      => $division,

                'home_team'     => $home_team,
                'result'        => $result,
                'away_team'     => $away_team,

                'odds_1'        => $odds_1,
                'odds_x'        => $odds_x,
                'odds_2'        => $odds_2,
                'bookmark'      => $bookmark,

                'soccervista_1x2'   => $soccervista_1x2,
                'soccervista_goal'  => $soccervista_goal,
                'soccervista_cs'    => $soccervista_cs,

                'predictz_result'   => $predictz_result,
                'predictz_score'    => $predictz_score,

                'windrawwin_1x1'    => $windrawwin_1x1,
                'windrawwin_cs'     => $windrawwin_score,
                'windrawwin_result' => $windrawwin_result,

                'soccerway_link'    => $soccerway_link
            );

            $updates = array(
                'home_team'     => $home_team,
                'result'        => $result,
                'away_team'     => $away_team,

                'odds_1'        => $odds_1,
                'odds_x'        => $odds_x,
                'odds_2'        => $odds_2,
                'bookmark'      => $bookmark,

                'soccervista_1x2'   => $soccervista_1x2,
                'soccervista_goal'  => $soccervista_goal,
                'soccervista_cs'    => $soccervista_cs,

                'predictz_result'   => $predictz_result,
                'predictz_score'    => $predictz_score,

                'windrawwin_1x1'    => $windrawwin_1x1,
                'windrawwin_cs'     => $windrawwin_score,
                'windrawwin_result' => $windrawwin_result,

                'soccerway_link'    => $soccerway_link
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
     *  saveReferee :
     * ========================================================================
     *
     *
     * @param $matchID
     * @param $refereeID
     * @param $refereeName
     * Updated by C.R. 6/12/2020
     *
     * ------------------------------------------------------------------------
     */
    public function saveReferee($matchID, $refereeID, $refereeName) {
        if(!isEmptyString($matchID) && !isEmptyString($refereeID) && !isEmptyString($refereeName)) {
            $refereeName = $this->getEscapedStr($refereeName);
            $sql = "UPDATE {$this->m_strTable} SET referee_id='{$refereeID}', referee_name='{$refereeName}' WHERE id='{$matchID}'";
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
     * Updated by C.R. 5/29/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getMatches_DT($params) {
        $fields = array(
            'index_no',
            'date_found',
            'match_time',
            'country',
            'division',
            'home_team',
            'result',
            'away_team',
            'odds_1',
            'odds_x',
            'odds_2',
            'soccervista_1x2',
            'soccervista_goal',
            'soccervista_cs',
            'predictz_result',
            'predictz_score',
            'windrawwin_1x1',
            'windrawwin_cs',
            'soccerway_link'
        );

        // SQL
        $sql_all = "SELECT * 
                    FROM {$this->m_strTable}";

        $filter = "division IN (SELECT division FROM base_leagues_recommend)";
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

    /**
     * ------------------------------------------------------------------------
     *  getMatchesForExcel :
     * ========================================================================
     *
     *
     * @param $params
     * @return mixed
     * Updated by C.R. 6/2/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getMatchesForExcel($params) {
        $sql_all = "SELECT * FROM {$this->m_strTable}";

        $filter = "division IN (SELECT division FROM base_leagues_recommend)";
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

        $records = $this->executeSQLAsArray($sql_all);

        for($i = 0; $i < sizeof($records); $i++) {
            $records[$i]['index'] = sprintf('%02d', $i + 1);
            $records[$i]['competition'] = $records[$i]['country'] . " >> " . $records[$i]['division'];
            $records[$i]['match'] = $records[$i]['home_team'] . " - " . $records[$i]['away_team'];
        }

        return $records;
    }

    /**
     * ------------------------------------------------------------------------
     *  getMatchesToAnalyze :
     * ========================================================================
     *
     *
     * @param $date
     * @return mixed
     * Updated by C.R. 6/15/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getMatchesToAnalyze($date) {
        return $this->executeSQLAsArray("SELECT * FROM matches_final WHERE date_found='{$date}' AND division IN (SELECT division FROM base_leagues_recommend) ORDER BY match_time ASC");
    }
}