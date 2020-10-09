<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 6/8/2020
 * Time: 10:02 PM
 */

require_once "C_Super.php";
class C_GlobalAPI extends C_Super
{
    private $m_ActiveSeason = '';
    public function __construct()
    {
        parent::__construct();

        $this->load->model('M_BaseSeasons', 'baseSeasons');
        $this->load->model('M_BaseSites', 'baseSites');
        $this->load->model('M_BaseCountry', 'baseCountry');
        $this->load->model('M_BaseLeagues', 'baseLeagues');
        $this->load->model('M_BaseClubs', 'baseClubs');

        $this->load->model('M_OddsPortal', 'oddsportal');
        $this->load->model('M_SoccerVista', 'soccervista');
        $this->load->model('M_SoccerWay', 'soccerway');
        $this->load->model('M_SoccerBase', 'soccerbase');
        $this->load->model('M_TeamRankings', 'rankingObj');
        $this->load->model('M_Referee', 'refereeObj');

        $this->load->model('M_MatchFinal', 'matchFinal');
        $this->load->model('M_MatchFinalSummary', 'matchSummary');

        $this->load->model('M_Similarity', 'similarityObj');

        $this->m_ActiveSeason = $this->baseSeasons->getActiveSeason();
    }

    /**
     * ------------------------------------------------------------------------
     *  x_fetch_matches :
     * ========================================================================
     *
     *
     * Updated by C.R. 5/28/2020
     *
     * ------------------------------------------------------------------------
     */
    public function x_fetch_matches() {
        $date = getValueInArray($_POST, 'date', date('Y-m-d'));
        $country = array();
        if(isset($_POST['country'])) {
            $value = $_POST['country'];
            if(!is_array($value)) {
                $value = array($value);
            }

            foreach ($value as $item) {
                $country[] = str_replace(" ", "-", $item);
            }
        }

        try {
            $sites = array(
                'oddsportal' => CMD_SCRAPER_MATCH_ODDSPORTAL,
                'soccervista' => CMD_SCRAPER_MATCH_SOCCERVISTA
            );

            $strCountries = implode("','", $country);

            $escapedSeason = $this->baseCountry->getEscapedStr($this->m_ActiveSeason);
            $sql = "SELECT * FROM base_country WHERE season='{$escapedSeason}'";
            if(!isEmptyString($strCountries)) {
                $sql .= " AND country IN ('{$strCountries}')";
            }
            $recCountries = $this->similarityObj->executeSQLAsArray($sql);

            foreach ($sites as $siteName => $command) {
                $country = array();
                foreach ($recCountries as $item) {
                    $country[] = str_replace(" ", "-", $item[$siteName]);
                }

                chdir(SCRAPER_PATH);
                $command .= "date=\"{$date}\" country=\"" . implode(',', $country) . "\"";
                $jsonData = executeShellCommand($command);

                if($jsonData != null) {
                    $this->$siteName->saveMatches($date, $jsonData);
                }
            }

            // Calculate similarity
            $this->similarityObj->checkMatches($date, $country);
        }
        catch(Exception $e) {
            $this->response->setResponse(-1, "Failed to fetch matches!");
        }

        $this->printResponse();
    }

    /**
     * ------------------------------------------------------------------------
     *  x_fetch_tips :
     * ========================================================================
     *
     *
     * Updated by C.R. 5/29/2020
     *
     * ------------------------------------------------------------------------
     */
    public function x_fetch_tips() {
        $selectedDate   = getValueInArray($_POST, 'date');

        $selectedCountry = array();
        if(isset($_POST['country'])) {
            $value = $_POST['country'];
            if(!is_array($value)) {
                $value = array($value);
            }

            foreach ($value as $item) {
                $selectedCountry[] = $item;
            }
        }

        try {
            $strCountries = implode("','", $selectedCountry);

            $escapedSeason = $this->baseCountry->getEscapedStr($this->m_ActiveSeason);
            $sql = "SELECT * FROM base_country WHERE season='{$escapedSeason}'";
            if(!isEmptyString($strCountries)) {
                $sql .= " AND country IN ('{$strCountries}')";
            }
            $recCountries = $this->matchFinal->executeSQLAsArray($sql);

            $selectedCountryScraper = array(
                'predictz'   => array(),
                'windrawwin' => array(),
                'soccerway'  => array(),
            );
            foreach ($recCountries as $item) {
                foreach (array_keys($selectedCountryScraper) as $siteName) {
                    $selectedCountryScraper[$siteName][] = str_replace(" ", "-", $item[$siteName]);
                }
            }

            $today = getDateTime('Y-m-d');
            $jsonTips = array();
            $matches = $this->similarityObj->getMatchesInSimilarity($selectedDate, $selectedCountry, MIN_ODDS_VALUE);

            if($matches == null || sizeof($matches) == 0) {
                throw new Exception("No matches to check.");
            }

            chdir(SCRAPER_PATH);

            $teamsInTips = array();
            $divsInTips = array();
            // Predictz
            if($selectedDate >= $today) {
                $command = CMD_SCRAPER_PREDICTZ . "date=\"{$selectedDate}\" country=\"" . implode(',', $selectedCountryScraper['predictz']) . "\"";
                $jsonData = executeShellCommand($command);
                $jsonTips['predictz'] = $jsonData;
                foreach ($jsonData as $predictz) {
                    $country   = getValueInArray($predictz, 'country');
                    $division  = str_replace('-', '. ', getValueInArray($predictz, 'division'));
                    $team_1    = getValueInArray($predictz, 'team_1');
                    $team_2    = getValueInArray($predictz, 'team_2');

                    if(!isset($divsInTips[$country])) {
                        $divsInTips[$country] = array();
                    }

                    if(!isset($teamsInTips[$country])) {
                        $teamsInTips[$country] = array();
                    }

                    $findResult = $this->findSimilarMatchBy($country, $division, $team_1, $team_2, $matches, 'predictz');
                    if($findResult != null) {
                        $foundIndex = $findResult['index'];
                        $division_oddsportal= $matches[$foundIndex]['division'];
                        $team_1_oddsportal  = $matches[$foundIndex]['team1'];
                        $team_2_oddsportal  = $matches[$foundIndex]['team2'];

                        if(!isset($divsInTips[$country][$division_oddsportal])) {
                            $divsInTips[$country][$division_oddsportal] = array();
                        }

                        if(!isset($teamsInTips[$country][$team_1_oddsportal])) {
                            $teamsInTips[$country][$team_1_oddsportal] = array();
                        }

                        if(!isset($teamsInTips[$country][$team_2_oddsportal])) {
                            $teamsInTips[$country][$team_2_oddsportal] = array();
                        }

                        $foundSimilarity = $findResult['similarity'];

                        $divsInTips[$country][$division_oddsportal]['predictz'] = getValueInArray($predictz, 'division');
                        $teamsInTips[$country][$team_1_oddsportal]['predictz'] = $foundSimilarity[$team_1_oddsportal];
                        $teamsInTips[$country][$team_2_oddsportal]['predictz'] = $foundSimilarity[$team_2_oddsportal];

                        $matches[$foundIndex]['predictz_result']= getValueInArray($predictz, 'result');
                        $matches[$foundIndex]['predictz_score'] = str_replace('-', ':', getValueInArray($predictz, 'score'));
                    }
                }
            }

            // WinDrawWin
//            $dateForWindrawwin = LIVE_SERVER ? $selectedDate : convertTimeZoneOfDate($selectedDate, 'Europe/Madrid', 'Asia/Shanghai');
            $todayForWindrawwin= LIVE_SERVER ? $today : convertTimeZoneOfDate($today, 'Europe/Madrid', 'Asia/Shanghai');
            if($selectedDate == $todayForWindrawwin) {
                $paramDate = 'today';
            }
            else if($selectedDate > $todayForWindrawwin) {
                $paramDate = 'future/' . str_replace('-', '', $selectedDate);
            }
            else {
                $paramDate = 'history/' . str_replace('-', '', $selectedDate);
            }

            $command = CMD_SCRAPER_WINDRAWWIN . "date=\"{$paramDate}\" country=\"" . implode(',', $selectedCountryScraper['windrawwin']) . "\"";
            $jsonData = executeShellCommand($command);

            $jsonTips['windrawwin'] = $jsonData;
            if($jsonData != null && is_array($jsonData)) {
                foreach ($jsonData as $windrawwin) {
                    $country = getValueInArray($windrawwin, 'country');
                    $division = getValueInArray($windrawwin, 'division');
                    $team_1 = getValueInArray($windrawwin, 'team_1');
                    $team_2 = getValueInArray($windrawwin, 'team_2');

                    if(!isset($divsInTips[$country])) {
                        $divsInTips[$country] = array();
                    }

                    if(!isset($teamsInTips[$country])) {
                        $teamsInTips[$country] = array();
                    }

                    $findResult = $this->findSimilarMatchBy($country, $division, $team_1, $team_2, $matches, 'windrawwin');
                    if ($findResult != null) {
                        $foundIndex = $findResult['index'];
                        $division_oddsportal= $matches[$foundIndex]['division'];
                        $team_1_oddsportal  = $matches[$foundIndex]['team1'];
                        $team_2_oddsportal  = $matches[$foundIndex]['team2'];

                        if(!isset($divsInTips[$country][$division_oddsportal])) {
                            $divsInTips[$country][$division_oddsportal] = array();
                        }

                        if(!isset($teamsInTips[$country][$team_1_oddsportal])) {
                            $teamsInTips[$country][$team_1_oddsportal] = array();
                        }

                        if(!isset($teamsInTips[$country][$team_2_oddsportal])) {
                            $teamsInTips[$country][$team_2_oddsportal] = array();
                        }

                        $foundSimilarity = $findResult['similarity'];

                        $divsInTips[$country][$division_oddsportal]['windrawwin'] = $division;
                        $teamsInTips[$country][$team_1_oddsportal]['windrawwin'] = $foundSimilarity[$team_1_oddsportal];
                        $teamsInTips[$country][$team_2_oddsportal]['windrawwin'] = $foundSimilarity[$team_2_oddsportal];

                        $matches[$foundIndex]['windrawwin_1x1']= $windrawwin['result'];
                        $matches[$foundIndex]['windrawwin_score'] = str_replace('-', ':', $windrawwin['score']);
                        $matches[$foundIndex]['windrawwin_result']= $windrawwin['real_score'];
                    }
                }
            }

            // SoccerWay
            $command = CMD_SCRAPER_SOCCERWAY . "date=\"{$selectedDate}\" country=\"" . implode(',', $selectedCountryScraper['soccerway']) . "\"";
            $jsonData = executeShellCommand($command);

            $jsonTips['soccerway'] = $jsonData;
            if($jsonData != null && is_array($jsonData)) {
                foreach ($jsonData as $soccerway) {
                    $country = getValueInArray($soccerway, 'country');
                    $division = getValueInArray($soccerway, 'division');
                    $team_1 = getValueInArray($soccerway, 'team_1');
                    $team_2 = getValueInArray($soccerway, 'team_2');

                    if(!isset($divsInTips[$country])) {
                        $divsInTips[$country] = array();
                    }

                    if(!isset($teamsInTips[$country])) {
                        $teamsInTips[$country] = array();
                    }

                    $findResult = $this->findSimilarMatchBy($country, $division, $team_1, $team_2, $matches, 'soccerway');
                    if ($findResult != null) {
                        $foundIndex = $findResult['index'];
                        $division_oddsportal= $matches[$foundIndex]['division'];
                        $team_1_oddsportal  = $matches[$foundIndex]['team1'];
                        $team_2_oddsportal  = $matches[$foundIndex]['team2'];

                        if(!isset($divsInTips[$country][$division_oddsportal])) {
                            $divsInTips[$country][$division_oddsportal] = array();
                        }

                        if(!isset($teamsInTips[$country][$team_1_oddsportal])) {
                            $teamsInTips[$country][$team_1_oddsportal] = array();
                        }

                        if(!isset($teamsInTips[$country][$team_2_oddsportal])) {
                            $teamsInTips[$country][$team_2_oddsportal] = array();
                        }

                        $foundSimilarity = $findResult['similarity'];
                        $divsInTips[$country][$division_oddsportal]['soccerway'] = $division;
                        $teamsInTips[$country][$team_1_oddsportal]['soccerway'] = $foundSimilarity[$team_1_oddsportal];
                        $teamsInTips[$country][$team_2_oddsportal]['soccerway'] = $foundSimilarity[$team_2_oddsportal];

                        $matches[$foundIndex]['soccerway'] = getValueInArray($soccerway, 'link');
                    }
                }
            }

            $this->matchFinal->saveMatches($matches);

            foreach ($matches as $match) {
                $country_oddsportal = getValueInArray($match, 'country');
                $division_oddsportal= $match['division'];
                $team_1_oddsportal  = $match['team1'];
                $team_2_oddsportal  = $match['team2'];

                if(!isset($divsInTips[$country_oddsportal][$division_oddsportal])) {
                    $divsInTips[$country_oddsportal][$division_oddsportal] = array();
                }

                if(!isset($teamsInTips[$country_oddsportal][$team_1_oddsportal])) {
                    $teamsInTips[$country_oddsportal][$team_1_oddsportal] = array();
                }

                if(!isset($teamsInTips[$country_oddsportal][$team_2_oddsportal])) {
                    $teamsInTips[$country_oddsportal][$team_2_oddsportal] = array();
                }
            }

            $this->similarityObj->updateDivisionSimilarity($divsInTips);
            $this->similarityObj->updateTeamSimilarity($teamsInTips);

            log_to_file(array('date' => $selectedDate, 'tips' => $jsonTips, 'similarity' => array('division' => $divsInTips, 'team' => $teamsInTips)));
        }
        catch(Exception $e) {
            $this->response->setResponse(-1, "Failed to fetch tips!<br/>Reason: " . $e->getMessage());
        }

        $this->printResponse();
    }

    /**
     * ------------------------------------------------------------------------
     *  x_analyze_matches :
     * ========================================================================
     *
     *
     * Updated by C.R. 6/16/2020
     *
     * ------------------------------------------------------------------------
     */
    public function x_analyze_matches() {
        $date = getValueInArray($_POST, 'date');
        if(isEmptyString($date)) {
            $date = getValueInArray($_GET, 'date', getDateTime("Y-m-d"));
        }

        // Get Matches to analyze
        try {
            $matches    = $this->matchFinal->getMatchesToAnalyze($date);
            $rankings   = $this->rankingObj->getRankings($date, $this->m_ActiveSeason);

            $teamsInfo  = $this->soccerway->getTeamInfo();
            $referees   = $this->refereeObj->getRefereeDetailsBy($date, $this->m_ActiveSeason);

            $matchesToCheck = array();
            $totalMatches = sizeof($matches);
            for($i = 0; $i < $totalMatches; $i++) {
                $match = $matches[$i];

                $id = getValueInArray($match, 'id');

                $country    = getValueInArray($match, 'country');
                $league     = getValueInArray($match, 'division');
                $homeTeam   = getValueInArray($match, 'home_team');
                $awayTeam   = getValueInArray($match, 'away_team');
                $result     = getValueInArray($match, 'result');
                $refereeID  = getValueInArray($match, 'referee_id');

                $lastMatches = array(
                    'home' => $this->soccerway->getLastMatchesOfTeam($date, 'home', $country, $homeTeam),
                    'away' => $this->soccerway->getLastMatchesOfTeam($date, 'away', $country, $awayTeam)
                );

//                $tmp_1 = explode(':', $result);
//                $tmp_2 = explode(':', ($totalMatches >= 20) ? getValueInArray($matches[$totalMatches - 1 - $i], 'result', '0:0') : '0:0');
//                $result_detail = array(
//                    'hm_1' => $tmp_1[0],
//                    'aw_1' => $tmp_1[1],
//                    'hm_2' => $tmp_2[0],
//                    'aw_2' => $tmp_2[1],
//                );

                // Last 20 Matches for home, away
                $tblAnalyzedMatches = array();
                $excelValues = array(
                    array(),
                    array()
                );

                $windrawwin1x1 = strtolower(getValueInArray($match, 'windrawwin_1x1'));
                if($windrawwin1x1 == 'home win') {
                    $windrawwin1x1 = '1';
                }
                else if($windrawwin1x1 == 'away win') {
                    $windrawwin1x1 = '2';
                }
                else {
                    $windrawwin1x1 = 'X';
                }

                $predictz_result = strtolower(getValueInArray($match, 'predictz_result'));
                if($predictz_result == 'home') {
                    $predictz_result = '1';
                }
                else if($predictz_result == 'away') {
                    $predictz_result = '2';
                }
                else {
                    $predictz_result = 'X';
                }

                $excelValues[1][4] = array(
                    1  => $i + 1,
                    2  => "{$match['country']} >> {$match['division']}",
                    3  => $match['match_time'],
                    4  => "{$match['home_team']} - {$match['away_team']}",
                    5  => getValueInArray($match, 'result'),
                    6  => getValueInArray($match, 'odds_1'),
                    7  => getValueInArray($match, 'odds_x'),
                    8  => getValueInArray($match, 'odds_2'),
                    9  => getValueInArray($match, 'bookmark'),
                    10 => getValueInArray($match, 'soccervista_1x2'),
                    11 => getValueInArray($match, 'soccervista_goal'),
                    12 => getValueInArray($match, 'soccervista_cs'),
                    13 => $windrawwin1x1,
                    14 => getValueInArray($match, 'windrawwin_cs'),
                    15 => $predictz_result,
                    16 => getValueInArray($match, 'predictz_score'),
                    17 => "p" . ($i + 1),
                    18 => getValueInArray($match, 'soccerway_link'),
                );

                $excelValues[0][1] = array(
                    0  => $i + 1,
                    22 => $homeTeam,
                    23 => $awayTeam
                );

                $excelValues[0][2] = array(
                    4  => '',
                    5  => '',
                );

                for($k = 0; $k < 20; $k++) {
                    $tblAnalyzedMatches[] = array(
                        'date' => '',
                        'home_team' => "",
                        'away_team' => "",

                        'score_hm1' => "0",
                        'score_hm2' => "0",
                        'score_aw1' => "0",
                        'score_aw2' => "0",

                        'reverse_hm1' => "0",
                        'reverse_hm2' => "0",
                        'reverse_aw1' => "0",
                        'reverse_aw2' => "0",

                        'sum_hm_12' => "0",
                        'sum_aw_12' => "0",
                        '1x2'       => "0",
                        '1x3'       => "0"
                    );

                    $excelValues[0][$k + 2] = array(
                        21 => '',
                        22 => '',
                        23 => '',
                        24 => '',
                        25 => '',
                        26 => '',
                        27 => '',
                    );
                }

                // For Rankings
                $ranksForThisTeam = $rankings[$country][$league];

                $row = 1;
                foreach($ranksForThisTeam as $teamName => $rankingItem) {
                    $curRank = $rankingItem['cur_rank'];

                    if(!isset($excelValues[0][$row + 32])) {
                        $excelValues[0][$row + 32] = array();
                    }

                    $excelValues[0][$row + 32][0]  = $curRank;
                    $excelValues[0][$row + 32][1]  = $rankingItem['prev_rank'];
                    $excelValues[0][$row + 32][2]  = $teamName;
                    $excelValues[0][$row + 32][3]  = $rankingItem['matches'];
                    $excelValues[0][$row + 32][4]  = $rankingItem['wins'];
                    $excelValues[0][$row + 32][5]  = $rankingItem['draws'];
                    $excelValues[0][$row + 32][6]  = $rankingItem['loses'];
                    $excelValues[0][$row + 32][7]  = $rankingItem['total_gf'];
                    $excelValues[0][$row + 32][8]  = $rankingItem['total_ga'];
                    $excelValues[0][$row + 32][9]  = $rankingItem['total_gd'];
                    $excelValues[0][$row + 32][10] = $rankingItem['total_pt'];
                    $excelValues[0][$row + 32][11] = $rankingItem['last_5'];

                    if($teamName == $homeTeam || $teamName == $awayTeam) {
                        $rowIndex = $teamName == $homeTeam ? 35 : 36;
                        if(!isset($excelValues[0][$rowIndex])) {
                            $excelValues[0][$rowIndex] = array();
                        }

                        $excelValues[0][2][$teamName == $homeTeam ? 4 : 5] = $curRank;

                        $excelValues[0][$rowIndex][22] = $curRank;
                        $excelValues[0][$rowIndex][23]  = $teamName;
                        $excelValues[0][$rowIndex][24]  = $rankingItem['matches'];
                        $excelValues[0][$rowIndex][25]  = $rankingItem['wins'];
                        $excelValues[0][$rowIndex][26]  = $rankingItem['draws'];
                        $excelValues[0][$rowIndex][27]  = $rankingItem['loses'];
                        $excelValues[0][$rowIndex][28]  = $rankingItem['total_gf'];
                        $excelValues[0][$rowIndex][29]  = $rankingItem['total_ga'];
                        $excelValues[0][$rowIndex][30]  = $rankingItem['total_gd'];
                        $excelValues[0][$rowIndex][31] = $rankingItem['total_pt'];
                        $excelValues[0][$rowIndex][32] = $rankingItem['last_5'];
                    }
					$row++;
                }

                foreach ($lastMatches as $type => $matchesPerType) {
                    $teamName = $type == 'home' ? $homeTeam : $awayTeam;
                    if( ($type == 'home' && $teamName == $homeTeam) ||
                        ($type == 'away' && $teamName == $awayTeam) ) {

                        for ($m = 0; $m < min(sizeof($matchesPerType), 20); $m++) {
                            $tmp = explode('-', getValueInArray($matchesPerType[$m], 'result', '0-0'));

                            $tblAnalyzedMatches[$m]["{$type}_team"] = $matchesPerType[$m]['team'];

                            if($type == 'home') {
                                $tblAnalyzedMatches[$m]['date'] = $matchesPerType[$m]['date'];
                                $tblAnalyzedMatches[$m]["score_hm1"] = $tmp[0];
                                $tblAnalyzedMatches[$m]["score_hm2"] = $tmp[1];
                            }
                            else {
                                $tblAnalyzedMatches[$m]["score_aw1"] = $tmp[0];
                                $tblAnalyzedMatches[$m]["score_aw2"] = $tmp[1];
                            }
                        }
                    }
                }

                for($k = 0; $k < 20; $k++) {
                    $excelValues[0][$k + 2][21] = $tblAnalyzedMatches[$k]['date'];
                    $excelValues[0][$k + 2][22] = $tblAnalyzedMatches[$k]['home_team'];
                    $excelValues[0][$k + 2][23] = $tblAnalyzedMatches[$k]['away_team'];
                    $excelValues[0][$k + 2][24] = $tblAnalyzedMatches[$k]['score_hm1'];
                    $excelValues[0][$k + 2][25] = $tblAnalyzedMatches[$k]['score_hm2'];
                    $excelValues[0][$k + 2][26] = $tblAnalyzedMatches[$k]['score_aw1'];
                    $excelValues[0][$k + 2][27] = $tblAnalyzedMatches[$k]['score_aw2'];
                }

                $excelValues[0][101] = array(
                    //                1 => sprintf("RP#%02d, %s", date("W", strtotime($match['date_found'])), $match['date_found']),
                    1 => sprintf("%d", date("W", strtotime($match['date_found']))),
                    2 => "{$match['date_found']} {$match['match_time']}:00"
                );

                try {
                    $calculatedResult = $this->exportDataToExcelWithValues(TEMP_PATH . "/{$id}.xlsx", "{$homeTeam} v {$awayTeam}", $excelValues, XLS_TPL_PATH . "/calculator_v1.xlsx", true);

                    // Save
//                    $calculatedResult['match_week']     = sprintf("RP#%02d, %s", date("W", strtotime($match['date_found'])), $match['date_found']);
//                    $calculatedResult['match_at']       = $excelValues[0][101][2];
//                    $calculatedResult['competition']    = "{$match['country']} >> {$match['division']}";
//                    $calculatedResult['match_time']     = $match['match_time'];
//                    $calculatedResult['match_team']     = "{$match['home_team']} - {$match['away_team']}";
//                    $calculatedResult['match_result']   = getValueInArray($match, 'result');
//                    $calculatedResult['match_odds_1']   = getValueInArray($match, 'odds_1');
//                    $calculatedResult['match_odds_x']   = getValueInArray($match, 'odds_x');
//                    $calculatedResult['match_odds_2']   = getValueInArray($match, 'odds_2');
//                    $calculatedResult['match_bookmark'] = getValueInArray($match, 'bookmark');
//                    $calculatedResult['match_sv_1x2']   = getValueInArray($match, 'soccervista_1x2');
//                    $calculatedResult['match_sv_ou']    = getValueInArray($match, 'soccervista_goal');
//                    $calculatedResult['match_sv_cs']    = getValueInArray($match, 'soccervista_cs');
//                    $calculatedResult['match_wdw_1x2']  = $windrawwin1x1;
//                    $calculatedResult['match_wdw_cs']   = getValueInArray($match, 'windrawwin_cs');
//                    $calculatedResult['match_rp2_1x2']  = $predictz_result;
//                    $calculatedResult['match_rp2_cs']   = getValueInArray($match, 'predictz_score');
//                    $calculatedResult['match_p_idx']    = "p" . ($i + 1);
//                    $calculatedResult['match_sw_link']  = getValueInArray($match, 'soccerway_link');

                    $this->matchSummary->saveSummary($match, $calculatedResult);
                    // echo json_encode($calculatedResult, JSON_PRETTY_PRINT) . PHP_EOL;
                }
                catch(Exception $e) {
                    echo $e->getMessage() . PHP_EOL;
                }

                $matchesToCheck[] = $match;
            }

            $this->matchSummary->arrangeRoyPickGrp();
        }
        catch (Exception $e) {
            $this->response->setResponse(-1, $e->getMessage());
        }

        $this->printResponse();
    }

    /**
     * ------------------------------------------------------------------------
     *  findSimilarMatchBy :
     * ========================================================================
     *
     *
     * @param $country
     * @param $division
     * @param $team_1
     * @param $team_2
     * @param $inMatches
     * @param string $site
     * @param bool $showLog
     * @return array|null
     * Updated by C.R. 6/17/2020
     *
     * ------------------------------------------------------------------------
     */
    private function findSimilarMatchBy($country, $division, $team_1, $team_2, $inMatches, $site = '', $showLog = false ) {
        $result = null;

        for($i = 0; $i < sizeof($inMatches); $i++) {
            $country_s   = getValueInArray($inMatches[$i], 'country');

            $division_odds = getValueInArray($inMatches[$i], 'division');
            $division_s  = getValueInArray($inMatches[$i], "division_{$site}");
            if(isEmptyString($division_s)) {
                $division_s = $division_odds;
            }

            $team_1_odds = getValueInArray($inMatches[$i], 'team1');
            $team_1_s = getValueInArray($inMatches[$i], "team1_{$site}");
            if(isEmptyString($team_1_s)) {
                $team_1_s = $team_1_odds;
            }

            $team_1_s_ali= getValueInArray($inMatches[$i], 'team1_soccervista');

            $team_2_odds = getValueInArray($inMatches[$i], 'team2');
            $team_2_s = getValueInArray($inMatches[$i], "team2_{$site}");
            if(isEmptyString($team_2_s)) {
                $team_2_s = $team_2_odds;
            }

            $team_2_s_ali= getValueInArray($inMatches[$i], 'team2_soccervista');

            if(strtolower($country) == strtolower($country_s)) {
                $bSameDivision = isSimilarDivision($country, $division, $division_s);
                if($bSameDivision) {
                    $similarity = checkMatchesSimilarity($team_1_s, $team_2_s, $team_1, $team_2);
                    if($similarity == null) {
                        if( !isEmptyString($team_1_s_ali) && $team_1_s != $team_1_s_ali &&
                            !isEmptyString($team_2_s_ali) && $team_2_s != $team_2_s_ali) {
                            $similarity = checkMatchesSimilarity($team_1_s_ali, $team_2_s_ali, $team_1, $team_2);
                        }
                        else if(!isEmptyString($team_1_s_ali) && $team_1_s != $team_1_s_ali) {
                            $similarity = checkMatchesSimilarity($team_1_s_ali, $team_2_s, $team_1, $team_2);
                        }
                        else if(!isEmptyString($team_2_s_ali) && $team_2_s != $team_2_s_ali) {
                            $similarity = checkMatchesSimilarity($team_1_s_ali, $team_2_s_ali, $team_1, $team_2);
                        }
                    }

                    if($similarity != null) {
                        $foundSimilarity = array();
                        if(isset($similarity[$team_1_s])) {
                            $foundSimilarity[$team_1_odds] = $similarity[$team_1_s];
                        }
                        else if(isset($similarity[$team_1_s_ali])) {
                            $foundSimilarity[$team_1_odds] = $similarity[$team_1_s_ali];
                        }

                        if(isset($similarity[$team_2_s])) {
                            $foundSimilarity[$team_2_odds] = $similarity[$team_2_s];
                        }
                        else if(isset($similarity[$team_2_s_ali])) {
                            $foundSimilarity[$team_2_odds] = $similarity[$team_2_s_ali];
                        }

                        $result = array('index' => $i, 'similarity' => $foundSimilarity);
                        break;
                    }
                }
            }
        }

        if($showLog) {
            echo "$team_1 --- $team_2" . PHP_EOL;
            var_dump($result);
        }

        return $result;
    }
}
