<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 6/1/2020
 * Time: 4:56 PM
 */

require_once "M_DataTable.php";
class M_Similarity extends M_DataTable
{
    public function __construct()
    {
        parent::__construct();

        $this->m_strTable = 'base_similarity';
    }

    /**
     * ------------------------------------------------------------------------
     *  checkMatches :
     * ========================================================================
     *
     *
     * @param $date
     * @param $selectedCountry
     * Updated by C.R. 6/2/2020
     *
     * ------------------------------------------------------------------------
     */
    public function checkMatches($date, $selectedCountry) {
        if(!is_array($selectedCountry)) {
            $selectedCountry = array($selectedCountry);
        }
        $selectedCountry = implode("','", $selectedCountry);

        $sql = "SELECT * FROM matches_oddsportal WHERE date_found='{$date}' AND country IN('{$selectedCountry}')";
        $matches_1 = $this->executeSQLAsArray($sql);

        $sql = "SELECT * FROM matches_soccervista WHERE date_found='{$date}' AND country IN('{$selectedCountry}')";
        $matches_2 = $this->executeSQLAsArray($sql);

        $sql = "SELECT * FROM {$this->m_strTable} WHERE country IN('{$selectedCountry}')";
        $records = $this->executeSQLAsArray($sql);

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
                    $newDiv_odds  = $this->getEscapedStr($oddsportal);
                    $newDiv_vista = $this->getEscapedStr($soccervista);
                    $sql = "INSERT INTO base_similarity SET country='{$country}', `type`='division', oddsportal='{$newDiv_odds}', soccervista='{$newDiv_vista}'";
                    if(!isEmptyString($newDiv_vista)) {
                        $sql .= " ON DUPLICATE KEY UPDATE soccervista='{$newDiv_vista}'";
                    }
                    $this->executeSQL($sql);
                }
            }
        }

        if(sizeof($newTeams) > 0) {
            foreach ($newTeams as $country => $teams) {
                foreach ($teams as $newTeam_odds => $newTeam_vista) {
                    $newTeam_odds = $this->getEscapedStr($newTeam_odds);
                    $newTeam_vista = $this->getEscapedStr($newTeam_vista);
                    $sql = "INSERT INTO base_similarity SET country='{$country}', `type`='team', oddsportal='{$newTeam_odds}', soccervista='{$newTeam_vista}' ";
                    if(!isEmptyString($newTeam_vista)) {
                        $sql .= " ON DUPLICATE KEY UPDATE soccervista='{$newTeam_vista}'";
                    }
                    $this->executeSQL($sql);
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
                $sql.= $this->sqlAppendSetValues($values, false);
                $sql.= " ON DUPLICATE KEY UPDATE similarity=VALUES(`similarity`)";

                $this->executeSQL($sql);
            }
        }
    }

    /**
     * ------------------------------------------------------------------------
     *  getMatchesInSimilarity :
     * ========================================================================
     *
     *
     * @param $date
     * @param $country
     * @param int $minOdds
     * @return mixed
     * Updated by C.R. 6/12/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getMatchesInSimilarity($date, $country, $minOdds = MIN_ODDS_VALUE) {
        if(!is_array($country)) {
            $country = array($country);
        }
        $country = implode("','", $country);
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
                  matches.bookmark,
                  matches.date_found,
                  matches.match_time,
                  matches_soccervista.`inf_1x2`,
                  matches_soccervista.`goals`,
                  matches_soccervista.`score` cs
                FROM
                (SELECT * FROM matches_oddsportal WHERE 
                  (`date_found`='{$date}' AND country IN('{$country}') AND 
                  division IN (SELECT division FROM base_leagues_recommend WHERE country IN('{$country}')) AND 
                  odds_1 >= {$minOdds} AND odds_x >= {$minOdds} AND odds_2 >= {$minOdds}) 
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

        $records = $this->executeSQLAsArray($sql);

        return $records;
    }

    /**
     * ------------------------------------------------------------------------
     *  updateDivisionSimilarity :
     * ========================================================================
     *
     *
     * @param $data
     * Updated by C.R. 6/5/2020
     *
     * ------------------------------------------------------------------------
     */
    public function updateDivisionSimilarity($data) {
        if($data != null && is_array($data) && sizeof($data) > 0) {
            foreach ($data as $country => $similarities) {
                foreach ($similarities as $divOnOddsportal => $similarDivs) {
                    $values = $this->sqlAppendSetValues($similarDivs, false);

                    $oddsportal = $this->getEscapedStr($divOnOddsportal);
                    $sql = "INSERT " . (isEmptyString($values) ? "IGNORE" : "") . " INTO {$this->m_strTable} SET country='{$country}', `type`='division', oddsportal='{$oddsportal}' ". (isEmptyString($values) ? "" : ",") . $values;
                    if(!isEmptyString($values)) {
                        $sql .= " ON DUPLICATE KEY UPDATE " . $values;
                    }

                    $this->executeSQL($sql);
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
     * Updated by C.R. 6/5/2020
     *
     * ------------------------------------------------------------------------
     */
    public function updateTeamSimilarity($data) {
        if($data != null && is_array($data) && sizeof($data) > 0) {
            foreach ($data as $country => $similarities) {
                foreach ($similarities as $teamOnOddsportal => $similarTeams) {
                    $values = $this->sqlAppendSetValues($similarTeams, false);

                    $oddsportal = $this->getEscapedStr($teamOnOddsportal);
                    $sql = "INSERT " . (isEmptyString($values) ? "IGNORE" : "") . " INTO {$this->m_strTable} SET country='{$country}', `type`='team', oddsportal='{$oddsportal}' " . (isEmptyString($values) ? "" : ",") . $values;
                    if(!isEmptyString($values)) {
                        $sql .= " ON DUPLICATE KEY UPDATE " . $values;
                    }

                    $this->executeSQL($sql);
                }
            }
        }
    }

    /**
     * ------------------------------------------------------------------------
     *  deleteTeamSimilarity :
     * ========================================================================
     *
     *
     * @param $id
     * Updated by C.R. 6/18/2020
     *
     * ------------------------------------------------------------------------
     */
    public function deleteTeamSimilarity($id) {
        $this->executeSQL("DELETE FROM {$this->m_strTable} WHERE id='{$id}'");
    }
}