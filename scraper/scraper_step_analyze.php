<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 7/19/2020
 * Time: 2:45 PM
 */

// error_reporting(0);

require_once "inc/SaveData.php";

$_gDbConn_ = new Database();

printMessage("================= Started at " . getDateTime() . " =================", "", "analyze");
try {
    $_gDbConn_->openDB();

    $retrieveObj = new RetrieveData($_gDbConn_);
    $saveObj = new SaveData($_gDbConn_);

    $_gActiveDate_ = '';
    if($argc > 1) {
        $tmp = explode("=", $argv[1]);

        if($tmp[0] == 'date') {
            $_gActiveDate_ = $tmp[sizeof($tmp) - 1];
        }
    }

    if(isEmptyString($_gActiveDate_)) {
        $_gActiveDate_ = getDateTime('Y-m-d');
    }

    $newSeasons = array(
        (date('Y')-1) . "/" . substr(date('Y'), 2),
        date('Y') . "/" . substr(date('Y') + 1, 2),
    );
    $retrieveObj->saveNewSeasons($newSeasons);

    $recSeasons = $_gDbConn_->executeSQLAsArray("SELECT * FROM base_seasons WHERE `status`='active' ORDER BY season DESC LIMIT 1;");
    $_gActiveSeason_ = '';
    if(sizeof($recSeasons) > 0) {
        $_gActiveSeason_ = $recSeasons[0]['season'];
    }
    else {
        throw new Exception("No active season!");
    }
    $escapedSeason = $_gDbConn_->getEscapedStr($_gActiveSeason_);

    $recBaseCountries = $_gDbConn_->executeSQLAsArray("SELECT * FROM base_country WHERE season='{$escapedSeason}'");

    chdir(ROOT_PATH . "/casperjs");

    ////////////////////////////////////////////////////////////////////////////////////
    // Get Tips
    ////////////////////////////////////////////////////////////////////////////////////
    printMessage("=> Retrieving Tips for matches...", "", "analyze");

    $selectedCountry = array();
    $selectedCountryScraper = array(
        'predictz'   => array(),
        'windrawwin' => array(),
        'soccerway'  => array(),
    );

	foreach ($recBaseCountries as $item) {
		$selectedCountry[] = $item['country'];
	}

    try {
        $today = getDateTime('Y-m-d');
        $jsonTips = array();
        $matches = $retrieveObj->getMatchesInSimilarity($_gActiveDate_, $_gActiveSeason_, $selectedCountry, MIN_ODDS_VALUE);

        if($matches == null || sizeof($matches) == 0) {
            throw new Exception("No matches to check.");
        }

		$matchCountries = [];
		foreach ($matches as $match){
			if(!in_array($match['country'], $matchCountries)){
				$matchCountries[] = $match['country'];
			}
		}
		foreach ($recBaseCountries as $item) {
			if(in_array($item['country'], $matchCountries)){
				foreach (array_keys($selectedCountryScraper) as $siteName) {
					$selectedCountryScraper[$siteName][] = str_replace(" ", "-", $item[$siteName]);
				}
			}
		}

        $teamsInTips = array();
        $divsInTips = array();
        // Predictz
        printMessage("   - Checking on Predictz ...", "", "analyze");

        if($_gActiveDate_ >= $today) {
            $command = CMD_SCRAPER_PREDICTZ . "date=\"{$_gActiveDate_}\" country=\"" . implode(',', $selectedCountryScraper['predictz']) . "\"";
            $jsonData = executeShellCommand($command);
            $jsonTips['predictz'] = $jsonData;
            if($jsonData != null && is_array($jsonData)) {
                foreach ($jsonData as $predictz) {
                    $country = getValueInArray($predictz, 'country');
                    $division = str_replace('-', '. ', getValueInArray($predictz, 'division'));
                    $team_1 = getValueInArray($predictz, 'team_1');
                    $team_2 = getValueInArray($predictz, 'team_2');

                    if (!isset($divsInTips[$country])) {
                        $divsInTips[$country] = array();
                    }

                    if (!isset($teamsInTips[$country])) {
                        $teamsInTips[$country] = array();
                    }

                    $findResult = findSimilarMatchBy($country, $division, $team_1, $team_2, $matches, 'predictz');
                    if ($findResult != null) {
                        $foundIndex = $findResult['index'];
                        $division_oddsportal = $matches[$foundIndex]['division'];
                        $team_1_oddsportal = $matches[$foundIndex]['team1'];
                        $team_2_oddsportal = $matches[$foundIndex]['team2'];

                        if (!isset($divsInTips[$country][$division_oddsportal])) {
                            $divsInTips[$country][$division_oddsportal] = array();
                        }

                        if (!isset($teamsInTips[$country][$team_1_oddsportal])) {
                            $teamsInTips[$country][$team_1_oddsportal] = array();
                        }

                        if (!isset($teamsInTips[$country][$team_2_oddsportal])) {
                            $teamsInTips[$country][$team_2_oddsportal] = array();
                        }

                        $foundSimilarity = $findResult['similarity'];

                        $divsInTips[$country][$division_oddsportal]['predictz'] = getValueInArray($predictz, 'division');
                        $teamsInTips[$country][$team_1_oddsportal]['predictz'] = $foundSimilarity[$team_1_oddsportal];
                        $teamsInTips[$country][$team_2_oddsportal]['predictz'] = $foundSimilarity[$team_2_oddsportal];

                        $matches[$foundIndex]['predictz_result'] = getValueInArray($predictz, 'result');
                        $matches[$foundIndex]['predictz_score'] = str_replace('-', ':', getValueInArray($predictz, 'score'));
                    }
                }
            }
        }

        // WinDrawWin
        printMessage("   - Checking on Windrawwin ...", "", "analyze");
//            $dateForWindrawwin = LIVE_SERVER ? $_gActiveDate_ : convertTimeZoneOfDate($_gActiveDate_, 'Europe/Madrid', 'Asia/Shanghai');
        $todayForWindrawwin= LIVE_SERVER ? $today : convertTimeZoneOfDate($today, 'Europe/Madrid', 'Asia/Shanghai');
        if($_gActiveDate_ == $todayForWindrawwin) {
            $paramDate = 'today';
        }
        else if($_gActiveDate_ > $todayForWindrawwin) {
            $paramDate = 'future/' . str_replace('-', '', $_gActiveDate_);
        }
        else {
            $paramDate = 'history/' . str_replace('-', '', $_gActiveDate_);
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

                $findResult = findSimilarMatchBy($country, $division, $team_1, $team_2, $matches, 'windrawwin');
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
        printMessage("   - Checking on SoccerWay ...", "", "analyze");
        $command = CMD_SCRAPER_SOCCERWAY . "date=\"{$_gActiveDate_}\" country=\"" . implode(',', $selectedCountryScraper['soccerway']) . "\"";
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

                $findResult = findSimilarMatchBy($country, $division, $team_1, $team_2, $matches, 'soccerway');
                if ($findResult != null) {
                    $foundIndex = $findResult['index'];
					//printMessage("   - Checking on findSimilarMatchBy ... {$country}, {$division}, {$team_1}, {$team_2}, {$foundIndex } " . count($matches), "", "analyze");

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

        $saveObj->saveQualifiedMatches($matches);

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

        $saveObj->updateDivisionSimilarity($divsInTips);
        $saveObj->updateTeamSimilarity($teamsInTips);

        // log_to_file(array('date' => $_gActiveDate_, 'tips' => $jsonTips, 'similarity' => array('division' => $divsInTips, 'team' => $teamsInTips)));
    }
    catch(Exception $e) {
        printMessage( "   Failed to fetch tips! Reason: " . $e->getMessage(), "", "analyze");
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // Get Team Details
    ////////////////////////////////////////////////////////////////////////////////////
    printMessage("=> Retrieving team details for qualified matches...", "", "analyze");

    // Fetch team details
    $matches = $retrieveObj->getQualifiedMatchesToCheck($_gActiveDate_, $_gActiveSeason_);

    foreach ($matches as $match) {
        printMessage("   - Checking for the match [{$match['home_team']}] : [{$match['away_team']}] ...", "", "analyze");

        try {
            $country = $match['country'];
            $division= $match['division'];

            $link = $match['soccerway_link'];
            if(isEmptyString($link)) {
                continue;
            }

            $command = CMD_SCRAPER_TEAMS_INFO . "link=\"{$link}\"";
            $jsonData = executeShellCommand($command);

            if($jsonData != null) {
                $similarTeams = $saveObj->saveTeamsInfo(
                    $link,
                    array(
                        "team_a" => $jsonData["team_a"],
                        "team_b" => $jsonData["team_b"],
                    ),
                    $match
                );
                $saveObj->updateTeamSimilarity(array($country => $similarTeams));

                // Referee
                $refereeName = ucfirst(getValueInArray($jsonData, 'referee'));
                $found = $retrieveObj->findRefereeByName($refereeName);

                $refereeID = ($found == null) ? -1 : $found['id'];
                $saveObj->saveReferee($match['id'], $refereeID, $refereeName);
            }
        }
        catch(Exception $e) {
            printMessage( "   Failed to fetch tips! Reason: " . $e->getMessage(), "", "analyze");
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // Analyzing Qualified Matches
    ////////////////////////////////////////////////////////////////////////////////////
    printMessage("=> Analyzing qualified matches...", "", "analyze");

    try {
        $defaultSeason = (date('Y')-1) . "/" . substr(date('Y'), 2);

        $datesToAnalyze = $retrieveObj->getDatesToAnalyze();
        if(!in_array($_gActiveDate_, $datesToAnalyze)) {
            $datesToAnalyze[] = $_gActiveDate_;
        }

        foreach ($datesToAnalyze as $analyzeDate) {
            $matches    = $retrieveObj->getQualifiedMatchesToAnalyze($analyzeDate, $_gActiveSeason_);
            $rankings   = $retrieveObj->getRankings($analyzeDate, $_gActiveSeason_);

            $teamsInfo  = $retrieveObj->getTeamInfo();
            $referees   = $retrieveObj->getRefereeDetailsBy($analyzeDate, $defaultSeason);

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
                    'home' => $retrieveObj->getLastMatchesOfTeam($analyzeDate, 'home', $country, $homeTeam),
                    'away' => $retrieveObj->getLastMatchesOfTeam($analyzeDate, 'away', $country, $awayTeam)
                );

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

                //fix bugs when rankings are not imported
                if(!isset($rankings[$country])){
                    continue;
                }

                // For Rankings
                $ranksForThisTeam = $rankings[$country][$league];

                foreach($ranksForThisTeam as $teamName => $rankingItem) {
                    $curRank = $rankingItem['cur_rank'];

                    if(!isset($excelValues[0][$curRank + 32])) {
                        $excelValues[0][$curRank + 32] = array();
                    }

                    $excelValues[0][$curRank + 32][0]  = $curRank;
                    $excelValues[0][$curRank + 32][1]  = $rankingItem['prev_rank'];
                    $excelValues[0][$curRank + 32][2]  = $teamName;
                    $excelValues[0][$curRank + 32][3]  = $rankingItem['matches'];
                    $excelValues[0][$curRank + 32][4]  = $rankingItem['wins'];
                    $excelValues[0][$curRank + 32][5]  = $rankingItem['draws'];
                    $excelValues[0][$curRank + 32][6]  = $rankingItem['loses'];
                    $excelValues[0][$curRank + 32][7]  = $rankingItem['total_gf'];
                    $excelValues[0][$curRank + 32][8]  = $rankingItem['total_ga'];
                    $excelValues[0][$curRank + 32][9]  = $rankingItem['total_gd'];
                    $excelValues[0][$curRank + 32][10] = $rankingItem['total_pt'];
                    $excelValues[0][$curRank + 32][11] = $rankingItem['last_5'];

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
                    $calculatedResult = $saveObj->exportDataToExcelWithValues(TEMP_PATH . "/{$id}.xlsx", "{$homeTeam} v {$awayTeam}", $excelValues, XLS_TPL_PATH . "/calculator_v1.xlsx", true);

                    $saveObj->saveAnalyzedData($match, $calculatedResult);
                    // echo json_encode($calculatedResult, JSON_PRETTY_PRINT) . PHP_EOL;
                }
                catch(Exception $e) {
                    printMessage($e->getMessage(), "", "analyze");
                }

                $matchesToCheck[] = $match;
            }
        }
    }
    catch (Exception $e) {
        printMessage("   Failed to analyze! Reason: " . $e->getMessage(), "", "analyze");
    }
}
catch(Exception $e) {
    echo " Error: " . $e->getMessage() . PHP_EOL;
}
$_gDbConn_->closeDB();
printMessage("================= Finished at " . getDateTime() . " =================", "", "analyze");

function findSimilarMatchBy($country, $division, $team_1, $team_2, $inMatches, $site = '', $showLog = false ) {
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

exit(1);
