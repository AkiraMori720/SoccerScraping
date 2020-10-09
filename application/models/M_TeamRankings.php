<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 6/11/2020
 * Time: 11:57 PM
 */

require_once "M_DataTable.php";
class M_TeamRankings extends M_DataTable
{
    public function __construct()
    {
        parent::__construct();

        $this->m_strTable = "teams_rankings";
    }

    /**
     * ------------------------------------------------------------------------
     *  getLeaguesForRankings :
     * ========================================================================
     *
     *
     * @param $season
     * @return mixed
     * Updated by C.R. 8/13/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getLeaguesForRankings($season) {
        $season = $this->getEscapedStr($season);

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
        $this->executeSQLAsArray($sql);

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
        return $this->executeSQLAsArray($sql);
    }

//    public function getLeaguesForRankings() {
//        $sql = <<<EOD
//SELECT
//  recommends.`country`,
//  base_similarity.oddsportal league_oddsportal,
//  (SELECT link FROM base_leagues WHERE site='oddsportal' AND country IN (SELECT oddsportal FROM base_country WHERE country='England') AND league=base_similarity.oddsportal) link_oddsportal,
//  base_similarity.`soccervista` league_soccervista,
//  (SELECT link FROM base_leagues WHERE site='soccervista' AND country IN (SELECT soccervista FROM base_country WHERE country='England') AND league=base_similarity.soccervista) link_soccervista,
//  base_similarity.`soccerbase` league_soccerbase,
//  (SELECT link FROM base_leagues WHERE site='soccerbase' AND country IN (SELECT soccerbase FROM base_country WHERE country='England') AND league=base_similarity.soccerbase) link_soccerbase,
//  base_similarity.`predictz` league_predictz,
//  (SELECT link FROM base_leagues WHERE site='predictz' AND country IN (SELECT predictz FROM base_country WHERE country='England') AND league=base_similarity.predictz) link_predictz,
//  base_similarity.`windrawwin` league_windrawwin,
//  (SELECT link FROM base_leagues WHERE site='windrawwin' AND country IN (SELECT windrawwin FROM base_country WHERE country='England') AND league=base_similarity.windrawwin) link_windrawwin,
//  base_similarity.`soccerway` league_soccerway,
//  (SELECT link FROM base_leagues WHERE site='soccerway' AND country IN (SELECT soccerway FROM base_country WHERE country='England') AND league=base_similarity.soccerway) link_soccerway
//FROM
//(SELECT * FROM base_leagues_recommend WHERE LENGTH(division)>0) recommends
//LEFT JOIN
//base_similarity
//ON base_similarity.`type`='division' AND base_similarity.`oddsportal`=recommends.`division`
//EOD;
//        return $this->executeSQLAsArray($sql);
//    }

    /**
     * ------------------------------------------------------------------------
     *  saveRankings :
     * ========================================================================
     *
     *
     * @param $season
     * @param $ranks
     * Updated by C.R. 8/14/2020
     *
     * ------------------------------------------------------------------------
     */
    public function saveRankings($season, $ranks) {
        if($ranks != null && is_array($ranks)) {
            $season = $this->getEscapedStr($season);
            foreach ($ranks as $country => $leagues) {
                $country = $this->getEscapedStr($country);
                foreach ($leagues as $league => $teams) {
                    $league = $this->getEscapedStr($league);
                    foreach ($teams as $team) {
                        $teamName = $this->getEscapedStr(getValueInArray($team, 'team'));
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

                        $sql = "INSERT INTO {$this->m_strTable} SET season='{$season}', fetch_date=CURDATE(),";

                        $sql.= "`country`=(SELECT oddsportal FROM base_country WHERE season='{$season}' AND country='{$country}'),";
                        $sql.= "`division`=(SELECT oddsportal FROM base_similarity WHERE country='{$country}' AND `type`='division' AND soccerway='{$league}' LIMIT 1),";
                        $sql.= "`team_name`=(SELECT oddsportal FROM base_similarity WHERE country='{$country}' AND `type`='team' AND soccerway='{$teamName}' LIMIT 1)";

                        $sql.= $this->sqlAppendSetValues($insertValues, true);
                        $sql.= " ON DUPLICATE KEY UPDATE " . $this->sqlAppendSetValues($updateValues, false);

                        try {
                            $this->executeSQL($sql);
                        }
                        catch(Exception $e) {
                            echo $sql . PHP_EOL;
                        }
                    }
                }
            }
        }
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
     * Updated by C.R. 8/14/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getRankings($date, $season, $country = '', $league = '') {
        $season = $this->getEscapedStr($season);

        $sql = "SELECT * FROM {$this->m_strTable} ";
        $filter = "season='{$season}' AND fetch_date='{$date}'";
        if(!isEmptyString($country)) {
            $country = $this->getEscapedStr($country);
            $filter .= (isEmptyString($filter) ? '' : ' AND ') . "country='{$country}'";
        }

        if(!isEmptyString($league)) {
            $league = $this->getEscapedStr($league);
            $filter .= (isEmptyString($filter) ? '' : ' AND ') . "division='{$league}'";
        }

        if(!isEmptyString($filter)) {
            $sql .= "WHERE {$filter} ";
        }
        $sql .= "Order By cast(cur_rank as unsigned) ASC ";

        $records = $this->executeSQLAsArray($sql);
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
}
