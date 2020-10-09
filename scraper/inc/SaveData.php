<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 6/28/2018
 * Time: 11:42 AM
 */

require_once "Database.php";
require_once "RetrieveData.php";

use PhpOffice\PhpSpreadsheet\Exception as XlsxException;
use PhpOffice\PhpSpreadsheet\IOFactory as XlsxIOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment as XlsxAlignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SaveData extends DataTable
{
    public function __construct($dbConn)
    {
        parent::__construct();

        $this->m_DBConn = $dbConn;
    }

    /**
     * ------------------------------------------------------------------------
     *  saveLeagues :
     * ========================================================================
     *
     *
     * @param $siteName
     * @param $data
     * @throws Exception
     * Updated by C.R. 7/1/2020
     *
     * ------------------------------------------------------------------------
     */
    public function saveLeagues($siteName, $data) {
        foreach ($data as $country => $leagues) {
            foreach ($leagues as $name => $link) {
                $leagueName = $name;
                if($siteName == 'soccervista') {
                    $leagueName = preg_replace('/(\d){1} /', '$1.', $name);
                }
                else if($siteName == 'windrawwin') {
                    if($leagueName == 'La Liga') {
                        $leagueName = 'Primera Liga';
                    }
                }

                $values = array(
                    'site' => $siteName,
                    'country' => $country,
                    'league' => $leagueName,
                    'link' => $link
                );

                $sql =
                    "INSERT INTO base_leagues SET " . $this->m_DBConn->sqlAppendSetValues($values, false) .
                    " ON DUPLICATE KEY UPDATE `link`='" . $this->m_DBConn->getEscapedStr($link) . "'";
                $this->m_DBConn->executeSQL($sql);
            }
        }
    }

    /**
     * ------------------------------------------------------------------------
     *  saveClubs :
     * ========================================================================
     *
     *
     * @param $season
     * @param $siteName
     * @param $country
     * @param $league
     * @param $data
     * @throws Exception
     * Updated by C.R. 8/13/2020
     *
     * ------------------------------------------------------------------------
     */
    public function saveClubs($season, $siteName, $country, $league, $data) {
        if(sizeof($data) > 0) {
            $season  = $this->m_DBConn->getEscapedStr($season);
            $siteName= $this->m_DBConn->getEscapedStr($siteName);
            $country = $this->m_DBConn->getEscapedStr($country);
            $league  = $this->m_DBConn->getEscapedStr($league);

            for($i = 0; $i < sizeof($data); $i++) {
                $club = $data[$i];

                $club = $this->m_DBConn->getEscapedStr($club);

                // Insert or update clubs
                $sql = <<<EOD
INSERT INTO base_clubs(`season`, `site`, `country`, `league`, `club`)
SELECT '{$season}', '{$siteName}', '{$country}', '{$league}', '{$club}'
WHERE NOT EXISTS (SELECT * FROM base_clubs WHERE `season`='{$season}' AND `site`='{$siteName}' AND `country`='{$country}' AND `club`='{$club}')
EOD;

                $this->m_DBConn->executeSQL($sql);
            }


        }
    }

    /**
     * ------------------------------------------------------------------------
     *  saveRefereeList :
     * ========================================================================
     *
     *
     * @param $selectedSeason
     * @param $data
     * @throws Exception
     * Updated by C.R. 7/1/2020
     *
     * ------------------------------------------------------------------------
     */
    public function saveRefereeList($selectedSeason, $data) {
        foreach ($data as $refereeItem) {
            $insertValues = array(
                "season"        => $selectedSeason,
                "country"       => getValueInArray($refereeItem, "country"),
                "referee_name"  => getValueInArray($refereeItem, "referee"),
                "referee_link"  => getValueInArray($refereeItem, "link"),
                "total_matches" => getValueInArray($refereeItem, "games"),
                "yellow_cards"  => getValueInArray($refereeItem, "yellow"),
                "red_cards"     => getValueInArray($refereeItem, "red")
            );

            $updateValues = array(
                "country"       => getValueInArray($refereeItem, "country"),
                "referee_name"  => getValueInArray($refereeItem, "referee"),
                "total_matches" => getValueInArray($refereeItem, "games"),
                "yellow_cards"  => getValueInArray($refereeItem, "yellow"),
                "red_cards"     => getValueInArray($refereeItem, "red")
            );

            $sql =
                "INSERT INTO base_referee SET " . $this->m_DBConn->sqlAppendSetValues($insertValues, false) .
                " ON DUPLICATE KEY UPDATE " . $this->m_DBConn->sqlAppendSetValues($updateValues, false);
            $this->m_DBConn->executeSQL($sql);
        }
    }

    /**
     * ------------------------------------------------------------------------
     *  saveRefereeDetail :
     * ========================================================================
     *
     *
     * @param $link
     * @param $selectedSeason
     * @param $selectedCountry
     * @param $matches
     * @throws Exception
     * Updated by C.R. 7/1/2020
     *
     * ------------------------------------------------------------------------
     */
    public function saveRefereeDetail($link, $selectedSeason, $selectedCountry, $matches) {
        $retrieveObj = new RetrieveData($this->m_DBConn);
        $possibleLeagues = $retrieveObj->getLeaguesFromSoccerBase($selectedCountry);

        $leaguesInCountry = array();
        foreach ($possibleLeagues as $possibleLeague) {
            $leaguesInCountry[] = $possibleLeague['league'];
        }

        $refLink = $this->m_DBConn->getEscapedStr($link);
        $sql = "SELECT id FROM base_referee WHERE referee_link='{$refLink}' AND season='{$selectedSeason}'";
        $records = $this->m_DBConn->executeSQLAsArray($sql);
        $refereeID = sizeof($records) > 0 ? $records[0]['id'] : -1;

        foreach ($matches as $match) {
            $leagueName = getValueInArray($match, 'league');

            if(in_array($leagueName, $leaguesInCountry) === false) {
                continue;
            }

            $tmp = substr(getValueInArray($match, 'date'), 3);
            $date = DateTime::createFromFormat('dM Y', $tmp);
            $dateFormat = $date->format('Y-m-d');

            $insertValues = array(
                "referee_id"=> $refereeID,
                "country"   => $selectedCountry,
                "season"    => $selectedSeason,
                "division"  => $leagueName,
                "league_id" => getValueInArray($match, 'comp_id'),

                "match_date"    => $dateFormat,
                "home_team"     => getValueInArray($match, 'team_a'),
                "away_team"     => getValueInArray($match, 'team_b'),
                "match_result"  => getValueInArray($match, 'result'),
                "yellow_card"   => getValueInArray($match, 'yellow'),
                "red_card"      => getValueInArray($match, 'red')
            );

            $updateValues = array(
                "country"   => $selectedCountry,
                "division"  => $leagueName,

                "match_date"    => $dateFormat,
                "match_result"  => getValueInArray($match, 'result'),
                "yellow_card"   => getValueInArray($match, 'yellow'),
                "red_card"      => getValueInArray($match, 'red')
            );

            $sql =
                "INSERT INTO soccerbase_referee SET " . $this->m_DBConn->sqlAppendSetValues($insertValues, false) .
                " ON DUPLICATE KEY UPDATE " . $this->m_DBConn->sqlAppendSetValues($updateValues, false);
            $this->m_DBConn->executeSQL($sql);
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
     * @throws Exception
     * Updated by C.R. 7/1/2020
     *
     * ------------------------------------------------------------------------
     */
    public function saveReferee($matchID, $refereeID, $refereeName) {
        if(!isEmptyString($matchID) && !isEmptyString($refereeID) && !isEmptyString($refereeName)) {
            $refereeName = $this->m_DBConn->getEscapedStr($refereeName);
            $sql = "UPDATE matches_final SET referee_id='{$refereeID}', referee_name='{$refereeName}' WHERE id='{$matchID}'";
            $this->m_DBConn->executeSQL($sql);
        }
    }

    /**
     * ------------------------------------------------------------------------
     *  saveRankings :
     * ========================================================================
     *
     *
     * @param $season
     * @param $ranks
     * Updated by C.R. 8/13/2020
     *
     * ------------------------------------------------------------------------
     */
    public function saveRankings($season, $ranks) {
        if($ranks != null && is_array($ranks)) {
            $season = $this->m_DBConn->getEscapedStr($season);
            foreach ($ranks as $country => $leagues) {
                $country = $this->m_DBConn->getEscapedStr($country);
                foreach ($leagues as $league => $teams) {
                    $league = $this->m_DBConn->getEscapedStr($league);

                    foreach ($teams as $team) {
                        $teamName = $this->m_DBConn->getEscapedStr(getValueInArray($team, 'team'));
                        $insertValues = array(
                            "cur_rank"      => getValueInArray($team, 'cur_rank'),
                            "prev_rank"     => getValueInArray($team, 'prev_rank'),
                            "total_matches" => getValueInArray($team, 'matches'),
                            "total_wins"    => getValueInArray($team, 'win'),
                            "total_draws"   => getValueInArray($team, 'draw'),
                            "total_loses"   => getValueInArray($team, 'lose'),
                            "total_gf"      => getValueInArray($team, 'goals'),
                            "total_ga"      => getValueInArray($team, 'goals_against'),
                            "total_gdiff"   => getValueInArray($team, 'diff_goals'),
                            "total_point"   => getValueInArray($team, 'points'),
                            "last_5matches" => getValueInArray($team, 'last_5_matches')
                        );

                        $updateValues = array(
                            "cur_rank"      => getValueInArray($team, 'cur_rank'),
                            "prev_rank"     => getValueInArray($team, 'prev_rank'),
                            "total_matches" => getValueInArray($team, 'matches'),
                            "total_wins"    => getValueInArray($team, 'win'),
                            "total_draws"   => getValueInArray($team, 'draw'),
                            "total_loses"   => getValueInArray($team, 'lose'),
                            "total_gf"      => getValueInArray($team, 'goals'),
                            "total_ga"      => getValueInArray($team, 'goals_against'),
                            "total_gdiff"   => getValueInArray($team, 'diff_goals'),
                            "total_point"   => getValueInArray($team, 'points'),
                            "last_5matches" => getValueInArray($team, 'last_5_matches')
                        );

                        $divisions = $this->m_DBConn->executeSQLAsArray("SELECT oddsportal FROM base_similarity WHERE country='{$country}' AND `type`='division' AND soccerway='{$league}' LIMIT 1");
                        if(!count($divisions)){
                            printMessage("    Not Registered Division in base_similarity -> country : ${country} , soccerway : ${league} ", "");
                            continue;
                        }

                        $team_names = $this->m_DBConn->executeSQLAsArray("SELECT oddsportal FROM base_similarity WHERE country='{$country}' AND `type`='team' AND soccerway='{$teamName}' LIMIT 1");
                        if(!count($team_names)){
                            printMessage("    Not Registered Team in base_similarity -> country : ${country} , soccerway : ${teamName} ", "");
                            continue;
                        }

                        $sql = "INSERT INTO teams_rankings SET season='{$season}', fetch_date=CURDATE(),";

                        $sql.= "`country`=(SELECT oddsportal FROM base_country WHERE season='{$season}' AND country='{$country}'),";
                        $sql.= "`division`='{$divisions[0]['oddsportal']}',";
                        $sql.= "`team_name`='{$team_names[0]['oddsportal']}'";

                        $sql.= $this->m_DBConn->sqlAppendSetValues($insertValues, true);
                        $sql.= " ON DUPLICATE KEY UPDATE " . $this->m_DBConn->sqlAppendSetValues($updateValues, false);

                        try {
                            $this->m_DBConn->executeSQL($sql);
                        }
                        catch(Exception $e) {
                            printMessage("    SaveRankings InsertOrDuplicate SQL Failed: " . $sql, "");
                        }
                    }
                }
            }
        }
    }

    /**
     * ------------------------------------------------------------------------
     *  saveMatches_oddsportal :
     * ========================================================================
     *
     *
     * @param $date
     * @param $matches
     * @param float $minOdds
     * @return mixed
     * @throws Exception
     * Updated by C.R. 7/1/2020
     *
     * ------------------------------------------------------------------------
     */
    public function saveMatches_oddsportal($date, $matches, $minOdds = MIN_ODDS_VALUE) {
        $sql = "SELECT season, country, division FROM base_leagues_recommend WHERE season IN (SELECT season FROM base_seasons WHERE `status`='active')";
        $recLeagues = $this->m_DBConn->executeSQLAsArray($sql);

        for ($i = 0; $i < sizeof($matches); $i++) {
            $match = $matches[$i];

            $match_time = getValueInArray($match, 'time');
            if(!preg_match('/[:]/', $match_time)) {
                $match_time = '';
            }

            $country    = getValueInArray($match, 'country');
            $division   = getValueInArray($match, 'division');
            $team1      = getValueInArray($match, 'team_1');
            $team2      = getValueInArray($match, 'team_2');

            $odds_1     = getValueInArray($match, 'odds_1');
            if(!is_numeric($odds_1)) {
                $odds_1 = '';
            }
            if(abs($odds_1) >= 100) {
                $odds_1 = sprintf("%.02f", $odds_1 / 100);
            }
            $odds_x     = getValueInArray($match, 'odds_x');
            if(!is_numeric($odds_x)) {
                $odds_x = '';
            }
            if(abs($odds_x) >= 100) {
                $odds_x = sprintf("%.02f", $odds_x / 100);
            }
            $odds_2     = getValueInArray($match, 'odds_2');
            if(!is_numeric($odds_2)) {
                $odds_2 = '';
            }
            if(abs($odds_2) >= 100) {
                $odds_2 = sprintf("%.02f", $odds_2 / 100);
            }

            $bookmark   = getValueInArray($match, 'bookmark');
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

                'score'     => $score,

                'odds_1'    => $odds_1,
                'odds_x'    => $odds_x,
                'odds_2'    => $odds_2,

                'bookmark'  => $bookmark,
            );

            $updates = array(
                'season'    => $season,
                'match_time'=> $match_time,
                'score'     => $score,

                'odds_1'    => $odds_1,
                'odds_x'    => $odds_x,
                'odds_2'    => $odds_2,

                'bookmark'  => $bookmark,
            );

            $conditions = array(
                'date_found'=> $date,

                'country'   => $country,
                'division'  => $division,

                'team1'     => $team1,
                'team2'     => $team2,
            );

            $sql_find = "SELECT * FROM matches_oddsportal WHERE " . $this->m_DBConn->sqlAppendSetValues($conditions, false, true, null, ' AND ');
            $records = $this->m_DBConn->executeSQLAsArray($sql_find);
            if(sizeof($records) > 0) {
                $old_1 = $records[0]['odds_1'];
                $old_x = $records[0]['odds_x'];
                $old_2 = $records[0]['odds_2'];

                if( $old_1 >= $minOdds && $old_x >= $minOdds && $old_2 >= $minOdds ) {
                    $updates = array(
                        'match_time'=> $match_time,
                        'score'     => $score,
                        'bookmark'  => $bookmark,
                    );
                }
            }

            $sql = "INSERT INTO matches_oddsportal SET ";
            $sql .= $this->m_DBConn->sqlAppendSetValues($values, false);

            $sql_update = $this->m_DBConn->sqlAppendSetValues($updates, false);
            if(!isEmptyString($sql_update)) {
                $sql .= " ON DUPLICATE KEY UPDATE {$sql_update}";
            }
            try{
                $this->m_DBConn->executeSQL($sql);
            } catch (Exception $e){
                printMessage("    InsertOrDuplicate SQL Failed: " . $sql, "");
            }

            // get ID
            $records = $this->m_DBConn->executeSQLAsArray($sql_find);
            $id = sizeof($records) > 0 ? $records[0]['id'] : '';

            // Update Match_Final
            $updates['result'] = $score;
            unset($updates['score']);
            if(isset($updates['season'])){
				unset($updates['season']);
			}

            $sql = "UPDATE matches_final SET " . $this->m_DBConn->sqlAppendSetValues($updates, false) . " WHERE oddsportal_id='{$id}'";
            try{
                $this->m_DBConn->executeSQL($sql);
            } catch (Exception $e){
                printMessage("    Update SQL Failed: " . $sql, "");
            }
            $matches[$i]['id'] = $id;
        }
        return $matches;
    }

    /**
     * ------------------------------------------------------------------------
     *  saveMatches_soccervista :
     * ========================================================================
     *
     *
     * @param $date
     * @param $matches
     * @throws Exception
     * Updated by C.R. 7/1/2020
     *
     * ------------------------------------------------------------------------
     */
    public function saveMatches_soccervista($date, $matches) {
        $sql = "SELECT season, country, division FROM base_leagues_recommend WHERE season IN (SELECT season FROM base_seasons WHERE `status`='active')";
        $recLeagues = $this->m_DBConn->executeSQLAsArray($sql);

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

            $sql = "INSERT INTO matches_soccervista SET ";
            $sql .= $this->m_DBConn->sqlAppendSetValues($values, false);

            $sql_update = $this->m_DBConn->sqlAppendSetValues($updates, false);
            if(!isEmptyString($sql_update)) {
                $sql .= " ON DUPLICATE KEY UPDATE {$sql_update}";
            }

            $this->m_DBConn->executeSQL($sql);
        }
    }

    /**
     * ------------------------------------------------------------------------
     *  checkMatches :
     * ========================================================================
     *
     *
     * @param $date
     * @param $selectedCountry
     * @throws Exception
     * Updated by C.R. 7/1/2020
     *
     * ------------------------------------------------------------------------
     */
    public function checkMatches($date, $selectedCountry) {
        if(!is_array($selectedCountry)) {
            $selectedCountry = array($selectedCountry);
        }
        $selectedCountry = implode("','", $selectedCountry);

        $sql = "SELECT * FROM matches_oddsportal WHERE date_found='{$date}' AND country IN('{$selectedCountry}')";
        $matches_1 = $this->m_DBConn->executeSQLAsArray($sql);

        $sql = "SELECT * FROM matches_soccervista WHERE date_found='{$date}' AND country IN('{$selectedCountry}')";
        $matches_2 = $this->m_DBConn->executeSQLAsArray($sql);

        $sql = "SELECT * FROM base_similarity WHERE country IN('{$selectedCountry}')";
        $records = $this->m_DBConn->executeSQLAsArray($sql);

        $similarDivisions = array();
        $similarTeams = array();
        foreach ($records as $record) {
            $country= getValueInArray($record, 'country');
            $type   = getValueInArray($record, 'type');
            $name_1 = getValueInArray($record, 'oddsportal');
            $name_2 = getValueInArray($record, 'soccervista');

            if(!isset($similarDivisions[$country])) {
                $similarDivisions[$country] = array();
            }

            if(!isset($similarTeams[$country])) {
                $similarTeams[$country] = array();
            }

            if($type == 'division' && !isset($similarDivisions[$country][$name_1])) {
                $similarDivisions[$country][$name_1] = $name_2;
            }
            if($type == 'team' && !isset($similarTeams[$country][$name_1])) {
                $similarTeams[$country][$name_1] = $name_2;
            }
        }

        $newSameMatches = array();
        $newDivisions = array();
        $newTeams = array();
        for($i = 0; $i < sizeof($matches_1); $i++) {
            $match_odds = $matches_1[$i];

            $country_odds = getValueInArray($match_odds, 'country');
            $division_odds= $match_odds['division'];
            $team_a_odds  = $match_odds['team1'];
            $team_b_odds  = $match_odds['team2'];

            for ($k = 0; $k < sizeof($matches_2); $k++) {
                $country_vista = getValueInArray($matches_2[$k], 'country');
                $division_vista= $matches_2[$k]['division'];
                $team_a_vista  = $matches_2[$k]['team1'];
                $team_b_vista  = $matches_2[$k]['team2'];

                if($country_odds != $country_vista) {
                    continue;
                }

                if(!isset($similarDivisions[$country_odds])) {
                    $similarDivisions[$country_odds] = array();
                }

                if(!isset($similarTeams[$country_odds])) {
                    $similarTeams[$country_odds] = array();
                }

                if(!isset($newDivisions[$country_odds])) {
                    $newDivisions[$country_odds] = array();
                }

                if(!isset($newTeams[$country_odds])) {
                    $newTeams[$country_odds] = array();
                }

                if(!isset($similarDivisions[$country_odds][$division_odds])) {
                    if($country_odds == 'Germany') {
                        $division_vista = trim($division_vista, '/[1.]/');
                    }

                    $bSameDivision = isSimilarDivision($country_odds, $division_odds, $division_vista);
                    if($bSameDivision) {
                        $similarDivisions[$country_odds][$division_odds] = $matches_2[$k]['division'];
                        $newDivisions[$country_odds][$division_odds] = $similarDivisions[$country_odds][$division_odds];
                    }
                }
                else {
                    $bSameDivision = ($similarDivisions[$country_odds][$division_odds] == $division_vista);
                }

                if($bSameDivision) {
                    $suggest_team_a_vista = isset($similarTeams[$country_odds]) ? getValueInArray($similarTeams[$country_odds], $team_a_odds) : '';
                    $suggest_team_b_vista = isset($similarTeams[$country_odds]) ? getValueInArray($similarTeams[$country_odds], $team_b_odds) : '';

                    $compare_team_1 = isEmptyString($suggest_team_a_vista) ? $team_a_odds : $suggest_team_a_vista;
                    $compare_team_2 = isEmptyString($suggest_team_b_vista) ? $team_b_odds : $suggest_team_b_vista;

                    $teamsChecked = checkMatchesSimilarity($compare_team_1, $compare_team_2, $team_a_vista, $team_b_vista);
                    if($teamsChecked != null) {
                        $match_odds['vista_id']     = $matches_2[$k]['id'];
                        $match_odds['similarity']   = $teamsChecked['similarity'];
                        $newSameMatches[] = $match_odds;

                        if($compare_team_1 == $team_a_odds || $compare_team_2 == $team_a_odds) {
                            $similarTeams[$country_odds][$team_a_odds] = $teamsChecked[$team_a_odds];
                            $newTeams[$country_odds][$team_a_odds] = $similarTeams[$country_odds][$team_a_odds];
                        }

                        if($compare_team_1 == $team_b_odds || $compare_team_2 == $team_b_odds) {
                            $similarTeams[$country_odds][$team_b_odds] = $teamsChecked[$team_b_odds];
                            $newTeams[$country_odds][$team_b_odds] = $similarTeams[$country_odds][$team_b_odds];
                        }

                        break;
                    }
                }
            }
        }

        if(sizeof($newDivisions) > 0) {
            foreach ($newDivisions as $country => $divisions) {
                foreach ($divisions as $oddsportal => $soccervista) {
                    $newDiv_odds  = $this->m_DBConn->getEscapedStr($oddsportal);
                    $newDiv_vista = $this->m_DBConn->getEscapedStr($soccervista);
                    $sql = "INSERT INTO base_similarity SET country='{$country}', `type`='division', oddsportal='{$newDiv_odds}', soccervista='{$newDiv_vista}'";
                    if(!isEmptyString($newDiv_vista)) {
                        $sql .= " ON DUPLICATE KEY UPDATE soccervista='{$newDiv_vista}'";
                    }
                    $this->m_DBConn->executeSQL($sql);
                }
            }
        }

        if(sizeof($newTeams) > 0) {
            foreach ($newTeams as $country => $teams) {
                foreach ($teams as $newTeam_odds => $newTeam_vista) {
                    $newTeam_odds  = $this->m_DBConn->getEscapedStr($newTeam_odds);
                    $newTeam_vista = $this->m_DBConn->getEscapedStr($newTeam_vista);
                    $sql = "INSERT INTO base_similarity SET country='{$country}', `type`='team', oddsportal='{$newTeam_odds}', soccervista='{$newTeam_vista}' ";
                    if(!isEmptyString($newTeam_vista)) {
                        $sql .= " ON DUPLICATE KEY UPDATE soccervista='{$newTeam_vista}'";
                    }
                    $this->m_DBConn->executeSQL($sql);
                }
            }
        }

        if(sizeof($newSameMatches)) {
            foreach ($newSameMatches as $match) {
                $values = array(
                    'oddsportal_id' => getValueInArray($match, 'id'),
                    'soccervista_id'=> getValueInArray($match, 'vista_id'),
                    'similarity'    => getValueInArray($match, 'similarity'),
                );

                $sql = "INSERT INTO matches_similarity SET ";
                $sql.= $this->m_DBConn->sqlAppendSetValues($values, false);
                $sql.= " ON DUPLICATE KEY UPDATE similarity=VALUES(`similarity`)";

                $this->m_DBConn->executeSQL($sql);
            }
        }
    }

    /**
     * ------------------------------------------------------------------------
     *  saveQualifiedMatches :
     * ========================================================================
     *
     *
     * @param $matches
     * @throws Exception
     * Updated by C.R. 7/1/2020
     *
     * ------------------------------------------------------------------------
     */
    public function saveQualifiedMatches($matches) {
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
            $quantified  = getValueInArray($match, 'quantified');
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
                'quantified'    => $quantified,
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
                'quantified'    => $quantified,
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

            $sql = "INSERT INTO matches_final SET ";
            $sql .= $this->m_DBConn->sqlAppendSetValues($values, false);

            $sql_update = $this->m_DBConn->sqlAppendSetValues($updates, false);
            if(!isEmptyString($sql_update)) {
                $sql .= " ON DUPLICATE KEY UPDATE {$sql_update}";
            }

            $this->m_DBConn->executeSQL($sql);
        }
    }

    /**
     * ------------------------------------------------------------------------
     *  updateDivisionSimilarity :
     * ========================================================================
     *
     *
     * @param $data
     * @throws Exception
     * Updated by C.R. 7/1/2020
     *
     * ------------------------------------------------------------------------
     */
    public function updateDivisionSimilarity($data) {
        if($data != null && is_array($data) && sizeof($data) > 0) {
            foreach ($data as $country => $similarities) {
                foreach ($similarities as $divOnOddsportal => $similarDivs) {
                    $values = $this->m_DBConn->sqlAppendSetValues($similarDivs, false);

                    $oddsportal = $this->m_DBConn->getEscapedStr($divOnOddsportal);
                    $sql = "INSERT " . (isEmptyString($values) ? "IGNORE" : "") . " INTO base_similarity SET country='{$country}', `type`='division', oddsportal='{$oddsportal}' ". (isEmptyString($values) ? "" : ",") . $values;
                    if(!isEmptyString($values)) {
                        $sql .= " ON DUPLICATE KEY UPDATE " . $values;
                    }

                    $this->m_DBConn->executeSQL($sql);
                }
            }
        }
    }

    /**
     * ------------------------------------------------------------------------
     *  updateTeamSimilarity :
     * ========================================================================
     *
     *
     * @param $data
     * @throws Exception
     * Updated by C.R. 7/1/2020
     *
     * ------------------------------------------------------------------------
     */
    public function updateTeamSimilarity($data) {
        if($data != null && is_array($data) && sizeof($data) > 0) {
            foreach ($data as $country => $similarities) {
                foreach ($similarities as $teamOnOddsportal => $similarTeams) {
                    $values = $this->m_DBConn->sqlAppendSetValues($similarTeams, false);

                    $oddsportal = $this->m_DBConn->getEscapedStr($teamOnOddsportal);
                    $sql = "INSERT " . (isEmptyString($values) ? "IGNORE" : "") . " INTO base_similarity SET country='{$country}', `type`='team', oddsportal='{$oddsportal}' " . (isEmptyString($values) ? "" : ",") . $values;
                    if(!isEmptyString($values)) {
                        $sql .= " ON DUPLICATE KEY UPDATE " . $values;
                    }

                    $this->m_DBConn->executeSQL($sql);
                }
            }
        }
    }

    /**
     * ------------------------------------------------------------------------
     *  saveTeamsInfo :
     * ========================================================================
     *
     *
     * @param $link
     * @param $data
     * @param $match
     * @return array
     * @throws Exception
     * Updated by C.R. 7/1/2020
     *
     * ------------------------------------------------------------------------
     */
    public function saveTeamsInfo($link, $data, $match) {
        $selectedDate = getValueInArray($match, 'date_found');
        $similarTeams = array();
        foreach ($data as $teamAlias => $details) {
            $teamType = ($teamAlias == 'team_a') ? 'home_team' : 'away_team';

            var_dump($details);
            if(!isset($details['info'])){
                continue;
            }

            $country = getValueInArray($match, 'country');
            $teamName = getValueInArray($match, $teamType);
            $teamAddr = preg_replace('/[ ]{2,}/', ' ', getValueInArray($details['info'], 'address'));

            $teamLogoLink = getValueInArray($details['info'], 'logo');
            $logoFile = saveImageLinkToFile($teamLogoLink, DATA_IMG_PATH, "team_" . pathinfo($teamLogoLink, PATHINFO_FILENAME));

            $venueImgUrl = getValueInArray($details['info']['venue'], 'image');
            $venueName = getValueInArray($details['info']['venue'], 'name');
            $venueFile = saveImageLinkToFile($venueImgUrl, DATA_IMG_PATH, "venue_" . pathinfo($venueImgUrl, PATHINFO_FILENAME));

            $insertValues = array(
                'country'   => $country,

                'team_link' => getValueInArray($details, 'link'),
                'team_name' => $teamName,
                'team_logo_file' => $logoFile,
                'team_logo_url'  => $teamLogoLink,
                'team_site' => getValueInArray($details['info'], 'site'),
                'founded'   => getValueInArray($details['info'], 'founded'),
                'address'   => $teamAddr,
                'phone'     => getValueInArray($details['info'], 'phone'),
                'fax'       => getValueInArray($details['info'], 'fax'),
                'email'     => getValueInArray($details['info'], 'email'),

                'venue_link'        => getValueInArray($details['info']['venue'], 'link'),
                'venue_name'        => $venueName,
                'venue_city'        => getValueInArray($details['info']['venue'], 'city'),
                'venue_image_file'  => $venueFile,
                'venue_image_url'   => $venueImgUrl,
                'venue_capacity'    => getValueInArray($details['info']['venue'], 'capacity', '0')
            );

            $updateValues = array(
                'team_link' => getValueInArray($details, 'link'),
                'team_logo_file' => $logoFile,
                'team_logo_url'  => $teamLogoLink,
                'team_site' => getValueInArray($details['info'], 'site'),
                'founded'   => getValueInArray($details['info'], 'founded'),
                'address'   => $teamAddr,
                'phone'     => getValueInArray($details['info'], 'phone'),
                'fax'       => getValueInArray($details['info'], 'fax'),
                'email'     => getValueInArray($details['info'], 'email'),

                'venue_link'        => getValueInArray($details['info']['venue'], 'link'),
                'venue_name'        => $venueName,
                'venue_city'        => getValueInArray($details['info']['venue'], 'city'),
                'venue_image_file'  => $venueFile,
                'venue_image_url'   => $venueImgUrl,
                'venue_capacity'    => getValueInArray($details['info']['venue'], 'capacity', '0')
            );

            $sql =
                "INSERT INTO teams_info SET " . $this->m_DBConn->sqlAppendSetValues($insertValues, false) .
                " ON DUPLICATE KEY UPDATE " . $this->m_DBConn->sqlAppendSetValues($updateValues, false);
            $infoId = $this->m_DBConn->getIdAfterInsertSQL($sql);

            if($infoId == null || $infoId < 0) {
                $country = $this->m_DBConn->getEscapedStr($country);
                $teamName = $this->m_DBConn->getEscapedStr($teamName);
                $sql = "SELECT id FROM teams_info WHERE country='{$country}' AND team_name='{$teamName}'";
                $records = $this->m_DBConn->executeSQLAsArray($sql);

                $infoId = sizeof($records) > 0 ? $records[0]['id'] : null;
            }

            // Save last matches
            $historyLink = $details['info']['history']['link'];
            $lastMatches = $details['info']['history']['matches'];

            foreach ($lastMatches as $lastMatch) {
                $date = DateTime::createFromFormat('d/m/Y', getValueInArray($lastMatch, 'date'));
                $dateFormat=$date->format('Y-m-d');

                $date = '20' . substr($dateFormat, 2);
                $now = date('Y-m-d');
                if($date > $now) {
                    continue;
                }

                $matchResult = preg_replace('/[ \t\n\r]/', '', getValueInArray($lastMatch, 'result'));
                if(stripos($matchResult, '-') === false) {
                    continue;
                }

                $matchTeam = getValueInArray($lastMatch, ($teamAlias == 'team_a') ? 'team_b' : 'team_a');
                $tmpTeamType = explode('_', $teamType);
                $matchType = $tmpTeamType[0];

                $homeTeam = ($matchType == 'home') ? $teamName : $matchTeam;
                $awayTeam = ($matchType == 'away') ? $teamName : $matchTeam;

                $insertValues = array(
                    'country'       => $country,
                    'division'      => getValueInArray($lastMatch, 'division'),
                    'match_date'    => $date,
                    'match_type'    => $matchType,
                    'home_team'     => $homeTeam,
                    'away_team'     => $awayTeam,

                    'match_result'  => $matchResult
                );

                $updateValues = array(
                    'match_result'  => $matchResult
                );

                $sql =
                    "INSERT INTO teams_match_history SET " . $this->m_DBConn->sqlAppendSetValues($insertValues, false) .
                    " ON DUPLICATE KEY UPDATE " . $this->m_DBConn->sqlAppendSetValues($updateValues, false);
                $this->m_DBConn->executeSQL($sql);

                if(!isset($similarTeams[$teamName])) {
                    $similarTeams[$teamName] = array(
                        'soccerway' => getValueInArray($lastMatch, $matchType == 'home' ? 'team_a' : 'team_b')
                    );
                }
            }
        }

        $link = $this->m_DBConn->getEscapedStr($link);
        $sql = "INSERT INTO soccerway_link_logs SET date_on='{$selectedDate}', link='{$link}' ON DUPLICATE KEY UPDATE updated_at=NOW()";
        $this->m_DBConn->executeSQL($sql);

        return $similarTeams;
    }

    /**
     * ------------------------------------------------------------------------
     *  saveAnalyzedData :
     * ========================================================================
     *
     *
     * @param $match
     * @param $summary
     * @throws Exception
     * Updated by C.R. 7/1/2020
     *
     * ------------------------------------------------------------------------
     */
    public function saveAnalyzedData($match, $summary) {
        if($summary != null && is_array($summary)) {
            $sql = "INSERT INTO matches_final_summary SET match_id=" . $match['id'] . $this->m_DBConn->sqlAppendSetValues($summary, true);
            $sql.= " ON DUPLICATE KEY UPDATE " . $this->m_DBConn->sqlAppendSetValues($summary, false);

            $this->m_DBConn->executeSQL($sql);

            $sql = "SELECT IFNULL(MAX(roypick_grp), 0) max_grp FROM matches_final_summary ORDER BY match_at, match_team";
            $rec = $this->m_DBConn->executeSQLAsArray($sql);
            $lastMaxGrp = $rec[0]['max_grp'];

            if($lastMaxGrp == '0') {
                $curGrp = 1;
                $curIdx = 1;
            }
            else {
                $sql = "SELECT COUNT(match_id) cnt FROM matches_final_summary WHERE roypick_grp='{$lastMaxGrp}'";
                $rec = $this->m_DBConn->executeSQLAsArray($sql);
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
            $recMatches = $this->m_DBConn->executeSQLAsArray($sql);
            for($k = 0; $k < sizeof($recMatches); $k++) {
                $values = array(
                    'roypick_grp' => $curGrp,
                    'roypick_no' => $curIdx
                );

                $sql = "UPDATE matches_final_summary SET " .
                    $this->m_DBConn->sqlAppendSetValues($values, false) .
                    " WHERE id='{$recMatches[$k]['id']}'";
                $this->m_DBConn->executeSQL($sql);

                $curIdx ++;
                if($curIdx > 8) {
                    $curGrp ++;
                    $curIdx = 1;
                }
            }
        }
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
    public function exportDataToExcelWithValues($exportPath, $title, $values, $templateFile, $bSaveFile = false) {
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
                if($sheetIdx == 0) {
                    $currentSheet->setTitle($title);
                }

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

//            echo $spreadsheet->getSheet(0)->getCell("CY24")->getCalculatedValue() . PHP_EOL;
//            echo $spreadsheet->getSheet(0)->getCell("DH3")->getCalculatedValue() . PHP_EOL;
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
        catch(XlsxException $e) {
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
