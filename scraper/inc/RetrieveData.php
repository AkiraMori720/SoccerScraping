<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 8/22/2017
 * Time: 10:23 AM
 */

require_once "DataTable.php";

class RetrieveData extends DataTable
{
    public function __construct($dbConn)
    {
        parent::__construct();

        $this->m_DBConn = $dbConn;
    }


    /**
     * ------------------------------------------------------------------------
     *  getEnumValues :
     * ========================================================================
     *  Updated by C.R. on 10/12/2019
     *
     * @param $table
     * @param $field
     * @return array
     * @throws Exception
     *
     * ------------------------------------------------------------------------
     */
    public function getEnumValues($table, $field) {
        $sql = "SHOW COLUMNS FROM {$table} LIKE '{$field}';";

        $records = $this->m_DBConn->executeSQLAsArray($sql);

        $values = array();
        if(sizeof($records) > 0) {
            $type = $records[0]['Type'];

            $type = str_replace("enum(", "", $type);
            $type = str_replace(")", "", $type);
            $type = str_replace("'", "", $type);

            if(strlen($type) > 0) {
                $values = explode(",", $type);
            }
        }

        return $values;
    }

    /**
     * ------------------------------------------------------------------------
     *  saveNewSeasons :
     * ========================================================================
     *
     *
     * @param $seasons
     * @throws Exception
     * Updated by C.R. 8/13/2020
     *
     * ------------------------------------------------------------------------
     */
    public function saveNewSeasons($seasons) {
        if(is_array($seasons) && sizeof($seasons) > 0) {
            foreach ($seasons as $season) {
                $escaped = $this->m_DBConn->getEscapedStr($season);
                $sql = <<<EOD
INSERT INTO base_seasons(season) 
SELECT '{$escaped}' 
WHERE NOT EXISTS (SELECT * FROM `base_seasons` WHERE season='{$escaped}' LIMIT 1)
EOD;
                $this->m_DBConn->executeSQL($sql);
            }
        }
    }


    /**
     * ------------------------------------------------------------------------
     *  getBaseLeagues :
     * ========================================================================
     *
     *
     * @param $siteName
     * @param string $country
     * @param string $league
     * @return array|null
     * @throws Exception
     * Updated by C.R. 7/1/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getBaseLeagues($siteName, $country = '', $league = '') {
        $values = array(
            'site' => $siteName,
            'country' => $country,
            'league' => $league
        );

        $sql = "SELECT * FROM base_leagues WHERE " . $this->m_DBConn->sqlAppendSetValues($values, false, true, null, ' AND ');

        $records = $this->m_DBConn->executeSQLAsArray($sql);

        return $records;
    }

    /**
     * ------------------------------------------------------------------------
     *  getLeaguesFromSoccerBase :
     * ========================================================================
     *
     *
     * @param $country
     * @param string $league
     * @return array|null
     * @throws Exception
     * Updated by C.R. 7/1/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getLeaguesFromSoccerBase($country, $league = '') {
        $country = $this->m_DBConn->getEscapedStr($country);
        $league  = $this->m_DBConn->getEscapedStr($league);

        $sql = "SELECT * FROM soccerbase_leagues";

        $filter = "country='{$country}' AND ";
        if(!isEmptyString($league)) {
            $filter .= "league IN (SELECT soccerbase FROM base_similarity WHERE country='{$country}' AND `type`='division' AND oddsportal='{$league}')";
        }
        else {
            $filter .= "league IN (SELECT soccerbase FROM base_similarity WHERE country='{$country}' AND `type`='division' AND oddsportal IN (SELECT division FROM base_leagues_recommend WHERE country='{$country}' AND LENGTH(division) > 0))";
        }

        if(!isEmptyString($filter)) {
            $sql .= " WHERE {$filter}";
        }

        $records = $this->m_DBConn->executeSQLAsArray($sql);
        return $records;
    }

    /**
     * ------------------------------------------------------------------------
     *  getLeaguesForRankings :
     * ========================================================================
     *
     *
     * @param $season
     * @return array|null
     * @throws Exception
     * Updated by C.R. 8/13/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getLeaguesForRankings($season) {
        $season = $this->m_DBConn->getEscapedStr($season);

        // Check whether the leagues are finished or not
        $sql = <<<EOD
UPDATE
base_leagues_recommend,
(
SELECT season, country, division, MIN(total_matches) total_matches FROM teams_rankings
WHERE fetch_date IN (SELECT MAX(fetch_date) FROM teams_rankings WHERE season='{$season}')
GROUP BY season, country, division
) curRankings
SET 
base_leagues_recommend.`status`='finished'
WHERE
base_leagues_recommend.`season`=curRankings.season
AND
base_leagues_recommend.`country`=curRankings.country
AND
base_leagues_recommend.`division`=curRankings.division
AND
base_leagues_recommend.`max_matches`=curRankings.total_matches
EOD;
        $this->m_DBConn->executeSQL($sql);


        $sql = <<<EOD
SELECT
  recommends.`country`,
  GROUP_CONCAT(recommends.division) divisions_oddsportal,
  GROUP_CONCAT(base_similarity.`soccervista`) divisions_soccervista,
  GROUP_CONCAT(base_similarity.`soccerbase`) divisions_soccerbase,
  GROUP_CONCAT(base_similarity.`predictz`) divisions_predictz,
  GROUP_CONCAT(base_similarity.`windrawwin`) divisions_windrawwin,
  GROUP_CONCAT(base_similarity.`soccerway`) divisions_soccerway
FROM
(SELECT * FROM base_leagues_recommend WHERE season='{$season}' AND LENGTH(division)>0 AND `status` <> 'finished') recommends
LEFT JOIN
base_similarity
ON base_similarity.`type`='division' AND base_similarity.`oddsportal`=recommends.`division`
GROUP BY recommends.country
EOD;
        $records = $this->m_DBConn->executeSQLAsArray($sql);

        return $records;
    }

    /**
     * ------------------------------------------------------------------------
     *  getMatchesInSimilarity :
     * ========================================================================
     *
     *
     * @param $date
     * @param $season
     * @param $country
     * @param float $minOdds
     * @return array|null
     * @throws Exception
     * Updated by C.R. 8/13/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getMatchesInSimilarity($date, $season, $country, $minOdds = MIN_ODDS_VALUE) {
        if(!is_array($country)) {
            $country = array($country);
        }
        $country = implode("','", $country);
        $season  = $this->m_DBConn->getEscapedStr($season);

        $sql = "SELECT
                  matches.id,
                  matches.country,
                  matches.division,
                  base_similarity_division.soccervista division_soccervista,
                  base_similarity_division.predictz division_predictz,
                  base_similarity_division.windrawwin division_windrawwin,
                  base_similarity_division.soccerway division_soccerway,
                  base_similarity_division.soccerbase division_soccerbase,
                  matches.team1,
                  base_similarity_team1.soccervista team1_soccervista,
                  base_similarity_team1.predictz team1_predictz,
                  base_similarity_team1.windrawwin team1_windrawwin,
                  base_similarity_team1.soccerway team1_soccerway,
                  base_similarity_team1.soccerbase team1_soccerbase,
                  matches.team2,
                  base_similarity_team2.soccervista team2_soccervista,
                  base_similarity_team2.predictz team2_predictz,
                  base_similarity_team2.windrawwin team2_windrawwin,
                  base_similarity_team2.soccerway team2_soccerway,
                  base_similarity_team2.soccerbase team2_soccerbase,                  
                  IFNULL(matches.away_team, '') away_team,
                  IFNULL(matches.score, '') result,
                  matches.odds_1,
                  matches.odds_x,
                  matches.odds_2,
                  IF(matches.odds_1 >= {$minOdds} AND matches.odds_x >= {$minOdds} AND matches.odds_2 >= {$minOdds}, 1, 0) as quantified,
                  matches.bookmark,
                  matches.date_found,
                  matches.match_time,
                  matches_soccervista.`inf_1x2`,
                  matches_soccervista.`goals`,
                  matches_soccervista.`score` cs
                FROM
                (SELECT * FROM matches_oddsportal WHERE 
                  (`date_found`='{$date}' AND country IN('{$country}') AND 
                  division IN (SELECT division FROM base_leagues_recommend WHERE season='{$season}' AND country IN('{$country}'))) 
                  OR 
                  (id IN (SELECT oddsportal_id FROM matches_final WHERE `date_found`='{$date}' AND country IN('{$country}')))                  
                ) matches
                LEFT JOIN
                matches_similarity
                ON matches.id=matches_similarity.`oddsportal_id`
                LEFT JOIN 
                matches_soccervista
                ON matches_similarity.`soccervista_id`=matches_soccervista.id
                LEFT JOIN 
                base_similarity base_similarity_division
                ON base_similarity_division.`country`=matches.country AND base_similarity_division.`type`='division' AND base_similarity_division.`oddsportal`=matches.`division`
                LEFT JOIN 
                base_similarity base_similarity_team1
                ON base_similarity_team1.`country`=matches.country AND base_similarity_team1.`type`='team' AND base_similarity_team1.`oddsportal`=matches.`team1`
                LEFT JOIN 
                base_similarity base_similarity_team2
                ON base_similarity_team2.`country`=matches.country AND base_similarity_team2.`type`='team' AND base_similarity_team2.`oddsportal`=matches.`team2`
                ";

        $records = $this->m_DBConn->executeSQLAsArray($sql);

        return $records;
    }

    /**
     * ------------------------------------------------------------------------
     *  getQualifiedMatchesToCheck :
     * ========================================================================
     *
     *
     * @param $date
     * @param $season
     * @param null $countries
     * @return array|null
     * @throws Exception
     * Updated by C.R. 8/13/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getQualifiedMatchesToCheck($date, $season, $countries = null) {
        $season  = $this->m_DBConn->getEscapedStr($season);

        $sql = "SELECT id, oddsportal_id, country, division, home_team, away_team, soccerway_link, date_found 
                FROM matches_final WHERE 
                  `date_found`='{$date}' AND
                  LENGTH(soccerway_link) > 0 AND " .
            // "soccerway_link NOT IN(SELECT link FROM soccerway_link_logs WHERE DATEDIFF(NOW(), date_on) >= 2) AND " .
            " division IN (SELECT division FROM base_leagues_recommend WHERE season='{$season}')";

        if($countries != null) {
            if(!is_array($countries)) {
                $countries = array($countries);
            }

            $strCountries = $this->m_DBConn->getEscapedStr(implode("','", $countries));
            $sql .= " AND country IN('{$strCountries}')";
        }

        $records = $this->m_DBConn->executeSQLAsArray($sql);

        return $records;
    }

    /**
     * ------------------------------------------------------------------------
     *  findRefereeByName :
     * ========================================================================
     *
     *
     * @param $name
     * @return null
     * @throws Exception
     * Updated by C.R. 7/1/2020
     *
     * ------------------------------------------------------------------------
     */
    public function findRefereeByName($name) {
        $found = null;
        if(strlen($name) > 0) {
            $sql = "SELECT * FROM base_referee WHERE `referee_name`='" . $this->m_DBConn->getEscapedStr($name) . "'";
            $records = $this->m_DBConn->executeSQLAsArray($sql);

            if(sizeof($records) > 0) {
                $found = $records[0];
            }
            else {
                $records = $this->m_DBConn->executeSQLAsArray("SELECT * FROM base_referee");
                foreach ($records as $record) {
                    $refName = $record['referee_name'];

                    $percent = 0;
                    similar_text(strtolower($name), strtolower($refName), $percent);
                    if($percent >= 90) {
                        $found = $record;
                        break;
                    }
                }
            }
        }

        return $found;
    }

    /**
     * ------------------------------------------------------------------------
     *  getQualifiedMatchesToAnalyze :
     * ========================================================================
     *
     *
     * @param $date
     * @param $season
     * @return array|null
     * @throws Exception
     * Updated by C.R. 8/13/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getQualifiedMatchesToAnalyze($date, $season) {
        $season  = $this->m_DBConn->getEscapedStr($season);

        $records = $this->m_DBConn->executeSQLAsArray("SELECT * FROM matches_final WHERE date_found='{$date}' AND division IN (SELECT division FROM base_leagues_recommend WHERE season='{$season}') ORDER BY match_time ASC");

        return $records;
    }

    /**
     * ------------------------------------------------------------------------
     *  getRankings :
     * ========================================================================
     *
     *
     * @param $date
     * @param $season
     * @param string $country
     * @param string $league
     * @return array
     * @throws Exception
     * Updated by C.R. 8/13/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getRankings($date, $season, $country = '', $league = '') {
        $season  = $this->m_DBConn->getEscapedStr($season);

        $sql = "SELECT * FROM teams_rankings ";
        $filter = "fetch_date='{$date}' AND season='{$season}'";
        if(!isEmptyString($country)) {
            $country = $this->m_DBConn->getEscapedStr($country);
            $filter .= (isEmptyString($filter) ? '' : ' AND ') . "country='{$country}'";
        }

        if(!isEmptyString($league)) {
            $league = $this->m_DBConn->getEscapedStr($league);
            $filter .= (isEmptyString($filter) ? '' : ' AND ') . "division='{$league}'";
        }

        if(!isEmptyString($filter)) {
            $sql .= "WHERE {$filter}";
        }

        $records = $this->m_DBConn->executeSQLAsArray($sql);
        $rankings = array();
        foreach ($records as $record) {
            $matchCountry = getValueInArray($record, 'country');
            if(!isset($rankings[$matchCountry])) {
                $rankings[$matchCountry] = array();
            }

            $matchLeague  = getValueInArray($record, 'division');
            if(!isset($rankings[$matchCountry][$matchLeague])) {
                $rankings[$matchCountry][$matchLeague] = array();
            }

            $team  = getValueInArray($record, 'team_name');
            $rankings[$matchCountry][$matchLeague][$team] = array(
                'cur_rank'  => getValueInArray($record, 'cur_rank'),
                'prev_rank' => getValueInArray($record, 'prev_rank'),
                'matches'   => getValueInArray($record, 'total_matches'),
                'wins'      => getValueInArray($record, 'total_wins'),
                'draws'     => getValueInArray($record, 'total_draws'),
                'loses'     => getValueInArray($record, 'total_loses'),
                'total_gf'  => getValueInArray($record, 'total_gf'),
                'total_ga'  => getValueInArray($record, 'total_ga'),
                'total_gd'  => getValueInArray($record, 'total_gdiff'),
                'total_pt'  => getValueInArray($record, 'total_point'),
                'last_5'    => getValueInArray($record, 'last_5matches')
            );
        }

        return $rankings;
    }


    /**
     * ------------------------------------------------------------------------
     *  getTeamInfo :
     * ========================================================================
     *
     *
     * @param string $country
     * @param string $team
     * @return array
     * @throws Exception
     * Updated by C.R. 7/1/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getTeamInfo($country = '', $team = '') {
        $filter = "";
        if(!isEmptyString($country)) {
            $country = $this->m_DBConn->getEscapedStr($country);
            $filter .= (isEmptyString($filter) ? '' : ' AND ') . "country='{$country}'";
        }

        if(!isEmptyString($team)) {
            $team = $this->m_DBConn->getEscapedStr($team);
            $filter .= (isEmptyString($filter) ? '' : ' AND ') . "team_name='{$team}'";
        }

        $records = $this->m_DBConn->executeSQLAsArray("SELECT * FROM teams_info " . (isEmptyString($filter) ? '' : "WHERE {$filter}"));

        $teams = array();
        foreach ($records as $record) {
            $teamCountry = getValueInArray($record, 'country');
            if(!isset($teams[$teamCountry])) {
                $teams[$teamCountry] = array();
            }

            $teamName = getValueInArray($record, 'team_name');
            $teams[$teamCountry][$teamName] = $record;
        }

        return $teams;
    }

    /**
     * ------------------------------------------------------------------------
     *  getLastMatchesOfTeam :
     * ========================================================================
     *
     *
     * @param $date
     * @param $type
     * @param $country
     * @param $team
     * @return array
     * @throws Exception
     * Updated by C.R. 7/1/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getLastMatchesOfTeam($date, $type, $country, $team) {
        $filter = "`match_date`<'{$date}' ";

        $country = $this->m_DBConn->getEscapedStr($country);
        $filter .= "AND country='{$country}' ";

        $team = $this->m_DBConn->getEscapedStr($team);
//        $filter .= "AND {$type}_team IN (SELECT soccerway FROM base_similarity WHERE `type`='team' AND country='{$country}' AND oddsportal='{$team}') ";
        $filter .= "AND {$type}_team = '{$team}' ";

        $type = $this->m_DBConn->getEscapedStr($type);
        $filter .= "AND `match_type`='{$type}'";

        $sql = "SELECT * FROM teams_match_history " . (isEmptyString($filter) ? '' : "WHERE {$filter}");
        $sql.= " ORDER BY country, match_type, match_date DESC";

        $records = $this->m_DBConn->executeSQLAsArray($sql);

        $teams = array();
        foreach ($records as $record) {
            $teams[] = array(
                'team'  => getValueInArray($record, ($type == 'home' ? 'away' : 'home') . '_team'),
                'date'  => getValueInArray($record, 'match_date'),
                'result'=> removeLettersInScore(getValueInArray($record, 'match_result', '0-0')),
            );
        }

        return $teams;
    }

    /**
     * ------------------------------------------------------------------------
     *  getRefereeDetailsBy :
     * ========================================================================
     *
     *
     * @param $date
     * @param $season
     * @param string $refereeID
     * @return array
     * @throws Exception
     * Updated by C.R. 7/1/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getRefereeDetailsBy($date, $season, $refereeID = '')
    {
        $filter = "`match_date`<='{$date}' AND season='{$season}'";
        if (!isEmptyString($refereeID)) {
            $filter .= (isEmptyString($filter) ? '' : ' AND ') . "referee_id='{$refereeID}'";
        }

        $sql = "SELECT * FROM soccerbase_referee" . (isEmptyString($filter) ? '' : " WHERE {$filter}");
        $sql.= " ORDER BY referee_id, country, match_date desc";
        $records = $this->m_DBConn->executeSQLAsArray($sql);

        $referees = array();
        foreach ($records as $record) {
            $country = getValueInArray($record, 'country');
            $referee_id = getValueInArray($record, 'referee_id');

            if(!isset($referees[$country])) {
                $referees[$country] = array();
            }

            if(!isset($referees[$country][$referee_id])) {
                $referees[$country][$referee_id] = array();
            }

            $referees[$country][$referee_id][] = array(
                'season'        => getValueInArray($record, 'season'),
                'division'      => getValueInArray($record, 'division'),
                'match_date'    => getValueInArray($record, 'match_date'),
                'home_team'     => getValueInArray($record, 'home_team'),
                'away_team'     => getValueInArray($record, 'away_team'),
                'match_result'  => removeLettersInScore(getValueInArray($record, 'match_result', '0-0')),
                'yellow_card'   => getValueInArray($record, 'yellow_card'),
                'red_card'      => getValueInArray($record, 'red_card'),
            );
        }

        return $referees;
    }

    /**
     * ------------------------------------------------------------------------
     *  getDatesToCheck :
     * ========================================================================
     *
     *
     * @return array
     * @throws Exception
     * Updated by C.R. 7/7/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getDatesToCheck() {
        $sql = <<<EOD
SELECT DISTINCT date_found AS match_date FROM matches_oddsportal 
WHERE (ISNULL(score) OR LENGTH(score) = 0) AND DATEDIFF(NOW(), date_found) < 30
AND season IN (SELECT season FROM base_seasons WHERE `status`='active')
EOD;
        $records = $this->m_DBConn->executeSQLAsArray($sql);

        $dates = array();
        foreach ($records as $record) {
            $dates[] = $record['match_date'];
        }

        return $dates;
    }

    /**
     * ------------------------------------------------------------------------
     *  getDatesToAnalyze :
     * ========================================================================
     *
     *
     * @return array
     * @throws Exception
     * Updated by C.R. 7/12/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getDatesToAnalyze() {
        $sql = <<<EOD
SELECT DISTINCT DATE(match_at) AS match_date FROM matches_final_summary WHERE (ISNULL(result_1) OR LENGTH(result_1) = 0) AND DATEDIFF(NOW(), match_at) < 30
EOD;
        $records = $this->m_DBConn->executeSQLAsArray($sql);

        $dates = array();
        foreach ($records as $record) {
            $dates[] = $record['match_date'];
        }

        return $dates;
    }
}