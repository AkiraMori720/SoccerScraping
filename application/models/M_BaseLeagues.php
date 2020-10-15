<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 6/17/2020
 * Time: 10:14 PM
 */

require_once "M_DataTable.php";
class M_BaseLeagues extends M_DataTable
{
    public function __construct()
    {
        parent::__construct();

        $this->m_strTable = "base_leagues";
    }

    /**
     * ------------------------------------------------------------------------
     *  getCountries :
     * ========================================================================
     *
     *
     * @param $season
     * @return mixed
     * Updated by C.R. 8/12/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getCountries($season = '') {
        $filter = '';
        if(isEmptyString($season)) {
            $filter = "season IN (SELECT season FROM base_seasons WHERE `status`='active')";
        }
        else {
            $escaped = $this->getEscapedStr($season);
            $filter = "season='{$escaped}'";
        }

        $sql = "SELECT DISTINCT country FROM base_leagues_recommend WHERE {$filter}";
        return $this->executeSQLAsArray($sql);
    }

    /**
     * ------------------------------------------------------------------------
     *  getLeagues :
     * ========================================================================
     *
     *
     * @param $siteName
     * @param string $country
     * @param string $league
     * @return mixed
     * Updated by C.R. 8/12/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getLeagues($siteName, $country = '', $league = '') {
        $values = array(
            'site'   => $siteName,
            'country'=> $country,
            'league' => $league
        );

        $sql = "SELECT * FROM {$this->m_strTable} WHERE " . $this->sqlAppendSetValues($values, false, ' AND ');

        return $this->executeSQLAsArray($sql);
    }

    /**
     * ------------------------------------------------------------------------
     *  saveLeagues :
     * ========================================================================
     *
     *
     * @param $siteName
     * @param $data
     * Updated by C.R. 8/12/2020
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
                    "INSERT INTO {$this->m_strTable} SET " . $this->sqlAppendSetValues($values, false) .
                    " ON DUPLICATE KEY UPDATE `link`='" . $this->getEscapedStr($link) . "'";
                $this->executeSQL($sql);
            }
        }
    }

    /**
     * ------------------------------------------------------------------------
     *  getRecommendList_DT :
     * ========================================================================
     *
     *
     * @param $params
     * @return array
     * Updated by C.R. 6/18/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getRecommendList_DT($params) {
        $fields = array(
            'index_no',
            'country',
            'division',
            'max_matches',
//            'oddsportal',
            'soccervista',
            'predictz',
            'windrawwin',
            'soccerway',
            'soccerbase',
            'id'
        );

        $season = $this->getEscapedStr(getValueInArray($params, 'season'));

        $sql_all = <<<EOD
SELECT
*
FROM        
(
    SELECT 
      recommends.id,
      recommends.`country`,
      recommends.`division`,
      recommends.max_matches,
      IFNULL(base_similarity.`oddsportal`, '') oddsportal, 
      IFNULL(base_similarity.`soccervista`, '') soccervista,
      IFNULL(base_similarity.`predictz`, '') predictz,
      IFNULL(base_similarity.`windrawwin`, '') windrawwin,
      IFNULL(base_similarity.`soccerway`, '') soccerway,
      IFNULL(base_similarity.`soccerbase`, '') soccerbase 
    FROM
    (SELECT * FROM base_leagues_recommend WHERE season='{$season}' ) recommends
    LEFT JOIN base_similarity
    ON 
      recommends.`country`=base_similarity.`country` AND 
      base_similarity.`type`='division' AND 
      base_similarity.`oddsportal`=recommends.`division`
) leagues
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

        return $data;
    }

    /**
     * ------------------------------------------------------------------------
     *  getLeaguesBySite :
     * ========================================================================
     *
     *
     * @param $site
     * @return array
     * Updated by C.R. 8/12/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getLeaguesBySite($site) {
        $sql = <<<EOD
SELECT
countries.`country`,
countries.`{$site}` country_site,
leagues.league,
leagues.id
FROM
(SELECT * FROM base_leagues WHERE site='{$site}') leagues
LEFT JOIN
(SELECT MAX(country) country, MAX({$site}) {$site} FROM base_country GROUP BY country) countries
ON countries.`{$site}`=leagues.country
EOD;
        $records = $this->executeSQLAsArray($sql);

        $leagues = array();
        foreach ($records as $record) {
            $country = $record['country'];
            if(!isset($leagues[$country])) {
                $leagues[$country] = array();
            }

            $leagues[$country][] = $record;
        }

        return $leagues;
    }

    /**
     * ------------------------------------------------------------------------
     *  saveRecommendLeague :
     * ========================================================================
     *
     *
     * @param $params
     * @return mixed|string
     * @throws Exception
     * Updated by C.R. 6/20/2020
     *
     * ------------------------------------------------------------------------
     */
    public function saveRecommendLeague($params) {
        $id = getValueInArray($params, 'id');

        $season     = getValueInArray($params, 'season');
        $country    = getValueInArray($params, 'country');
        $oddsportal = getValueInArray($params, 'oddsportal');
        $soccervista= getValueInArray($params, 'soccervista');
        $soccerway  = getValueInArray($params, 'soccerway');
        $predictz   = getValueInArray($params, 'predictz');
        $windrawwin = getValueInArray($params, 'windrawwin');
        $soccerbase = getValueInArray($params, 'soccerbase');
        $maxMatches = getValueInArray($params, 'max_matches');

        if( isEmptyString($country) || isEmptyString($oddsportal) ) {
            throw new Exception("Missing values!");
        }

        $values = array(
            'season'        => $season,
            'country'       => $country,
            'division'      => $oddsportal,
            'max_matches'   => $maxMatches
        );

        // Add on base_leagues_recommend
        if(isEmptyString($id)) {
            $sql = "INSERT IGNORE INTO base_leagues_recommend SET " . $this->sqlAppendSetValues($values, false);
            $this->executeSQL($sql);

            $id = $this->getLastInsertedID();
        } else {
        	$sql = "UPDATE base_leagues_recommend SET max_matches=$maxMatches WHERE id='$id'";
			$this->executeSQL($sql);
		}

        // Add or Update on base_similarity
        $insertValues = array(
            'country'    => $country,
            'oddsportal' => $oddsportal,
            'soccervista'=> $soccervista,
            'soccerway'  => $soccerway,
            'predictz'   => $predictz,
            'windrawwin' => $windrawwin,
            'soccerbase' => $soccerbase
        );

        $sql = "INSERT INTO base_similarity SET `type`='division' " . $this->sqlAppendSetValues($insertValues, true);

        $updateValues = array(
            'soccervista'=> $soccervista,
            'soccerway'  => $soccerway,
            'predictz'   => $predictz,
            'windrawwin' => $windrawwin,
            'soccerbase' => $soccerbase
        );
        $sql.= " ON DUPLICATE KEY UPDATE " . $this->sqlAppendSetValues($updateValues, false, ',', true);
        $this->executeSQL($sql);

        // Add Clubs to base_similarity
        $this->saveSimilarityForClubsInRecommendedLeagues($season);

        return $id;
    }

    /**
     * ------------------------------------------------------------------------
     *  importRecommendLeaguesBySeason :
     * ========================================================================
     *
     *
     * @param $season
     * Updated by C.R. 8/12/2020
     *
     * ------------------------------------------------------------------------
     */
    public function importRecommendLeaguesBySeason($season) {
        $tmp = explode('/', $season);
        $year = $tmp[0];
        $prevYear = $year - 1;

        $prevSeason = $prevYear . "/" . substr($year, 2);

        $escaped = $this->getEscapedStr($prevSeason);
        $sql = "SELECT country, division, max_matches FROM base_leagues_recommend WHERE season='{$escaped}'";
        $records = $this->executeSQLAsArray($sql);

        foreach ($records as $record) {
            $record['season'] = $season;
            $sql = "INSERT IGNORE INTO base_leagues_recommend SET " . $this->sqlAppendSetValues($record, false);
            $this->executeSQL($sql);
        }

        // Add Clubs to base_similarity
        $this->saveSimilarityForClubsInRecommendedLeagues($season);
    }

    /**
     * ------------------------------------------------------------------------
     *  saveSimilarityForClubsInRecommendedLeagues :
     * ========================================================================
     *
     *
     * @param $season
     * Updated by C.R. 8/13/2020
     *
     * ------------------------------------------------------------------------
     */
    public function saveSimilarityForClubsInRecommendedLeagues($season) {
        // Sites
        $sites = $this->executeSQLAsArray("SELECT * FROM base_sites ORDER BY order_no");

        // Countries
        $escapedSeason = $this->getEscapedStr($season);
        $sql = "SELECT * FROM base_leagues_recommend WHERE season='{$escapedSeason}' AND country NOT IN (SELECT DISTINCT(country) FROM base_clubs WHERE season='{$escapedSeason}' AND site='oddsportal')";
        $leagues = $this->executeSQLAsArray($sql);

        foreach ($leagues as $leagueItem) {
            $country = getValueInArray($leagueItem, 'country');
            $league  = getValueInArray($leagueItem, 'division');

            $country = $this->getEscapedStr($country);
            $league  = $this->getEscapedStr($league);

            $allClubs = array();
            $clubValues = array();
            foreach ($sites as $site) {
                $siteName = $site['site'];

                $sql_clubs = <<<EOD
SELECT * FROM base_clubs 
WHERE 
  season = '{$escapedSeason}' AND 
  site = '{$siteName}' AND 
  country IN (SELECT {$siteName} FROM base_country WHERE season = '{$escapedSeason}' AND country='{$country}') AND 
  league IN (SELECT {$siteName} FROM base_similarity WHERE country='{$country}' AND `type`='division' AND oddsportal='{$league}')
EOD;
                $clubsInSite = $this->executeSQLAsArray($sql_clubs);

                if($siteName == 'oddsportal') {
                    if(sizeof($clubsInSite) == 0) {
                        break;
                    }
                    else {
                        foreach ($clubsInSite as $club) {
                            $clubValues[] = array($siteName => $club['club']);
                        }
                    }
                }

                $allClubs[$siteName] = $clubsInSite;
            }

            for($i = 0; $i < sizeof($clubValues); $i++) {
                foreach ($allClubs as $site => $clubs) {
                    if($site == 'oddsportal') {
                        continue;
                    }

                    $clubValues[$i][$site] = '';
                    foreach ($clubs as $club) {
                        $clubName = $club['club'];

                        similar_text($clubValues[$i]['oddsportal'], $clubName, $percent);
                        if($percent >= 80) {
                            $clubValues[$i][$site] = $clubName;
                            break;
                        }
                        else {
                            $bMatched = false;
                            foreach($sites as $siteToCompare) {
                                if($siteToCompare == $site) {
                                    continue;
                                }

                                $compareVal = getValueInArray($clubValues[$i], $siteToCompare['site']);

                                $bMatched |= (stripos($clubName, $compareVal) !== false) || (stripos($compareVal, $clubName) !== false);
                                if($bMatched) {
                                    $clubValues[$i][$site] = $clubName;
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            foreach ($clubValues as $clubValue) {
                $sql = "
INSERT INTO `base_similarity`(country, `type`, " . implode(",", array_keys($clubValue)) . ") 
SELECT '{$country}', 'team' ";

                foreach (array_values($clubValue) as $val) {
                    $val = $this->getEscapedStr($val);
                    $sql .= ", '{$val}'";
                }

                $sql .=
                    " 
WHERE NOT EXISTS (SELECT * FROM `base_similarity` 
      WHERE country = '{$country}' AND `type` = 'team' AND oddsportal='{$clubValue['oddsportal']}' LIMIT 1) 
";
                $this->executeSQL($sql);
            }
        }
    }


    /**
     * ------------------------------------------------------------------------
     *  deleteRecommendLeague :
     * ========================================================================
     *
     *
     * @param $id
     * Updated by C.R. 6/18/2020
     *
     * ------------------------------------------------------------------------
     */
    public function deleteRecommendLeague($id) {
        $sql = "DELETE FROM base_leagues_recommend WHERE id='{$id}'";
        $this->executeSQL($sql);
    }

    /**
     * ------------------------------------------------------------------------
     *  getRecommendLeagues :
     * ========================================================================
     *
     *
     * @return array
     * Updated by C.R. 8/12/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getRecommendLeagues() {
        $sql = "SELECT * FROM base_leagues_recommend ORDER BY season, country, division";

        $records = $this->executeSQLAsArray($sql);

        $leagues = array();
        foreach ($records as $record) {
            $season = $record['season'];
            $country = $record['country'];

            if(!isset($leagues[$season])) {
                $leagues[$season] = array();
            }

            if(!isset($leagues[$season][$country])) {
                $leagues[$season][$country] = array();
            }

            $leagues[$season][$country][] = $record['division'];
        }

        return $leagues;
    }
}