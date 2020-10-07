<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 6/9/2020
 * Time: 2:56 AM
 */

require_once "M_DataTable.php";
class M_SoccerBase extends M_DataTable
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * ------------------------------------------------------------------------
     *  getLeagues :
     * ========================================================================
     *
     *
     * @param $country
     * @param string $league
     * @return mixed
     * Updated by C.R. 6/9/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getLeagues($country, $league = '') {
//        $country = $this->getExactCountry($country);

        $country = $this->getEscapedStr($country);
        $league  = $this->getEscapedStr($league);

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

        return $this->executeSQLAsArray($sql);
    }

    /**
     * ------------------------------------------------------------------------
     *  saveRefereeList :
     * ========================================================================
     *
     *
     * @param $selectedSeason
     * @param $data
     * Updated by C.R. 6/12/2020
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
                "INSERT INTO base_referee SET " . $this->sqlAppendSetValues($insertValues, false) .
                " ON DUPLICATE KEY UPDATE " . $this->sqlAppendSetValues($updateValues, false);
            $this->executeSQL($sql);
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
     * Updated by C.R. 6/12/2020
     *
     * ------------------------------------------------------------------------
     */
    public function saveRefereeDetail($link, $selectedSeason, $selectedCountry, $matches) {
        $leaguesInCountry = array();
        $possibleLeagues = $this->getLeagues($selectedCountry);
        foreach ($possibleLeagues as $possibleLeague) {
            $leaguesInCountry[] = $possibleLeague['league'];
        }

        $refLink = $this->getEscapedStr($link);
        $sql = "SELECT id FROM base_referee WHERE referee_link='{$refLink}' AND season='{$selectedSeason}'";
        $records = $this->executeSQLAsArray($sql);
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
                "INSERT INTO soccerbase_referee SET " . $this->sqlAppendSetValues($insertValues, false) .
                " ON DUPLICATE KEY UPDATE " . $this->sqlAppendSetValues($updateValues, false);
            $this->executeSQL($sql);
        }
    }


//    private function getExactCountry($country) {
//        if($country == 'Germany') {
//            $country = 'German';
//        }
//
//        return $country;
//    }
}