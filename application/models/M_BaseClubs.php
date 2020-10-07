<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 6/17/2020
 * Time: 10:14 PM
 */

require_once "M_DataTable.php";
class M_BaseClubs extends M_DataTable
{
    public function __construct()
    {
        parent::__construct();

        $this->m_strTable = "base_clubs";
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
     * Updated by C.R. 8/13/2020
     *
     * ------------------------------------------------------------------------
     */
    public function saveClubs($season, $siteName, $country, $league, $data) {
        if(sizeof($data) > 0) {
            $season  = $this->getEscapedStr($season);
            $siteName= $this->getEscapedStr($siteName);
            $country = $this->getEscapedStr($country);
            $league  = $this->getEscapedStr($league);

            for($i = 0; $i < sizeof($data); $i++) {
                $club = $data[$i];

                $club = $this->getEscapedStr($club);

                $sql = <<<EOD
INSERT INTO base_clubs(`season`, `site`, `country`, `league`, `club`)
SELECT '{$season}', '{$siteName}', '{$country}', '{$league}', '{$club}'
WHERE NOT EXISTS (SELECT * FROM base_clubs WHERE `season`='{$season}' AND `site`='{$siteName}' AND `country`='{$country}' AND `club`='{$club}')
EOD;
                $this->executeSQL($sql);
            }
        }
    }

    /**
     * ------------------------------------------------------------------------
     *  getUsedClubList_DT :
     * ========================================================================
     *
     *
     * @param $params
     * @return array
     * Updated by C.R. 6/18/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getUsedClubList_DT($params) {
        $season  = $this->getEscapedStr(getValueInArray($params, 'season'));
        $country = $this->getEscapedStr(getValueInArray($params, 'country'));
        $league  = $this->getEscapedStr(getValueInArray($params, 'league'));

        $fields = array(
            'index_no',
            'oddsportal',
            'soccervista',
            'soccerway',
            'predictz',
            'windrawwin',
            'soccerbase',
            'id'
        );

        $sql_all = <<<EOD
SELECT
 all_leagues.league,
 used_leagues.*
FROM
(SELECT *
FROM base_clubs
WHERE site='oddsportal' AND season='{$season}' AND country='{$country}' AND league='{$league}') all_leagues
INNER JOIN
(SELECT * FROM base_similarity WHERE `type`='team' AND country='{$country}') used_leagues
ON all_leagues.club=used_leagues.oddsportal
EOD;
        $filter = "";
//        if(isset($params['date'])) {
//            $value = $this->getEscapedStr(trim($params['date']));
//            if(strlen($value) > 0) { $filter .= (strlen($filter) > 0 ? " AND " : "")."`date_found`='{$value}'"; }
//        }

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

        // Get Clubs
        $allClubs = array();
        if(!isEmptyString($country) && !isEmptyString($league)) {
            $sql = "SELECT * FROM base_country WHERE country='{$country}' AND season='{$season}'";
            $records = $this->executeSQLAsArray($sql);
            $selectedCountryInfo = sizeof($records) > 0 ? $records[0] : null;

            $sql = "SELECT * FROM base_similarity WHERE country='{$country}' AND `type`='division' AND oddsportal='{$league}'";
            $records = $this->executeSQLAsArray($sql);
            $selectedLeagueInfo = sizeof($records) > 0 ? $records[0] : null;

            if($selectedCountryInfo != null && $selectedLeagueInfo != null) {
                $sites = $this->executeSQLAsArray("SELECT * FROM base_sites");

                foreach ($sites as $site) {
                    $siteName = $site['site'];

                    if(!isset($allClubs[$siteName])) {
                        $allClubs[$siteName] = array();
                    }

                    $selectedCountry = $this->getEscapedStr(getValueInArray($selectedCountryInfo, $siteName));
                    $selectedLeague  = $this->getEscapedStr(getValueInArray($selectedLeagueInfo, $siteName));

                    $siteName = $this->getEscapedStr($siteName);
                    $sql = "SELECT * FROM base_clubs WHERE site='{$siteName}' AND season='{$season}' AND country='{$selectedCountry}' AND league='{$selectedLeague}'";
                    $records = $this->executeSQLAsArray($sql);

                    foreach ($records as $record) {
                        $clubName = $record['club'];

                        $allClubs[$siteName][] = $clubName;
                    }
                }
            }
        }

        $data['clubs'] = $allClubs;

        return $data;
    }
}