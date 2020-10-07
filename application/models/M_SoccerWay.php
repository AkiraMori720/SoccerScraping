<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 6/8/2020
 * Time: 10:09 PM
 */

require_once "M_DataTable.php";
class M_SoccerWay extends M_DataTable
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * ------------------------------------------------------------------------
     *  getMatchesToCheck :
     * ========================================================================
     *
     *
     * @param $date
     * @param $countries
     * @return mixed
     * Updated by C.R. 6/8/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getMatchesToCheck($date, $countries) {
        $sql = "SELECT id, oddsportal_id, country, division, home_team, away_team, soccerway_link, date_found 
                FROM matches_final WHERE 
                  `date_found`='{$date}' AND
                  LENGTH(soccerway_link) > 0 AND 
                  soccerway_link NOT IN(SELECT link FROM soccerway_link_logs WHERE DATEDIFF(NOW(), date_on) >= 2) AND 
                  division IN (SELECT division FROM base_leagues_recommend)";

        if($countries != null) {
            if(!is_array($countries)) {
                $countries = array($countries);
            }

            $strCountries = $this->getEscapedStr(implode("','", $countries));
            $sql .= " AND country IN('{$strCountries}')";
        }

        $records = $this->executeSQLAsArray($sql);

        return $records;
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
     * Updated by C.R. 6/10/2020
     *
     * ------------------------------------------------------------------------
     */
    public function saveTeamsInfo($link, $data, $match) {
        $selectedDate = getValueInArray($match, 'date_found');
        $similarTeams = array();
        foreach ($data as $teamAlias => $details) {
            $teamType = ($teamAlias == 'team_a') ? 'home_team' : 'away_team';

            $country = getValueInArray($match, 'country');
            $teamName = getValueInArray($match, $teamType);
            $teamAddr = preg_replace('/[ ]{2,}/', ' ', getValueInArray($details['info'], 'address'));

            $teamLogoLink = getValueInArray($details['info'], 'logo');
            $logoFile = saveImageLinkToFile($teamLogoLink, ROOT_PATH . "/" . DATA_IMG_PATH, "team_" . pathinfo($teamLogoLink, PATHINFO_FILENAME));

            $venueImgUrl = getValueInArray($details['info']['venue'], 'image');
            $venueName = getValueInArray($details['info']['venue'], 'name');
            $venueFile = saveImageLinkToFile($venueImgUrl, ROOT_PATH . "/" . DATA_IMG_PATH, "venue_" . pathinfo($venueImgUrl, PATHINFO_FILENAME));

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
                "INSERT INTO teams_info SET " . $this->sqlAppendSetValues($insertValues, false) .
                " ON DUPLICATE KEY UPDATE " . $this->sqlAppendSetValues($updateValues, false);
            $this->executeSQL($sql);
            $infoId = $this->getLastInsertedID();
            if($infoId == null || $infoId < 0) {
                $country = $this->getEscapedStr($country);
                $teamName = $this->getEscapedStr($teamName);
                $sql = "SELECT id FROM teams_info WHERE country='{$country}' AND team_name='{$teamName}'";
                $records = $this->executeSQLAsArray($sql);

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

                $matchResult = getValueInArray($lastMatch, 'result');
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
                    "INSERT INTO teams_match_history SET " . $this->sqlAppendSetValues($insertValues, false) .
                    " ON DUPLICATE KEY UPDATE " . $this->sqlAppendSetValues($updateValues, false);
                $this->executeSQL($sql);

                if(!isset($similarTeams[$teamName])) {
                    $similarTeams[$teamName] = array(
                        'soccerway' => getValueInArray($lastMatch, $matchType == 'home' ? 'team_a' : 'team_b')
                    );
                }
            }
        }

        $link = $this->getEscapedStr($link);
        $sql = "INSERT INTO soccerway_link_logs SET date_on='{$selectedDate}', link='{$link}' ON DUPLICATE KEY UPDATE updated_at=NOW()";
        $this->executeSQL($sql);

        return $similarTeams;
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
     * Updated by C.R. 6/16/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getTeamInfo($country = '', $team = '') {
        $filter = "";
        if(!isEmptyString($country)) {
            $country = $this->getEscapedStr($country);
            $filter .= (isEmptyString($filter) ? '' : ' AND ') . "country='{$country}'";
        }

        if(!isEmptyString($team)) {
            $team = $this->getEscapedStr($team);
            $filter .= (isEmptyString($filter) ? '' : ' AND ') . "team_name='{$team}'";
        }

        $records = $this->executeSQLAsArray("SELECT * FROM teams_info " . (isEmptyString($filter) ? '' : "WHERE {$filter}"));

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
     * @param string $type
     * @param string $country
     * @param string $team
     * @return array
     * Updated by C.R. 6/16/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getLastMatchesOfTeam($date, $type, $country, $team) {
        $filter = "`match_date`<'{$date}' ";

        $country = $this->getEscapedStr($country);
        $filter .= "AND country='{$country}' ";

        $team = $this->getEscapedStr($team);
//        $filter .= "AND {$type}_team IN (SELECT soccerway FROM base_similarity WHERE `type`='team' AND country='{$country}' AND oddsportal='{$team}') ";
        $filter .= "AND {$type}_team = '{$team}' ";

        $type = $this->getEscapedStr($type);
        $filter .= "AND `match_type`='{$type}'";

        $sql = "SELECT * FROM teams_match_history " . (isEmptyString($filter) ? '' : "WHERE {$filter}");
        $sql.= " ORDER BY country, match_type, match_date DESC";

        $records = $this->executeSQLAsArray($sql);

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
}