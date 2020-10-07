<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 7/1/2020
 * Time: 9:29 AM
 */

error_reporting(0);

require_once "inc/SaveData.php";

$_gDbConn_ = new Database();

printMessage("================= Started at " . getDateTime() . " =================", "");
try {
    $_gDbConn_->openDB();

    $retrieveObj = new RetrieveData($_gDbConn_);
    $saveObj = new SaveData($_gDbConn_);

    $selectedDate = '';
    if($argc > 1) {
        $tmp = explode("=", $argv[1]);

        if($tmp[0] == 'date') {
            $selectedDate = $tmp[sizeof($tmp) - 1];
        }
    }

    if(isEmptyString($selectedDate)) {
        $selectedDate = getDateTime('Y-m-d');
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

    ////////////////////////////////////////////////////////////////////////////////////
    // Analyzing Qualified Matches
    ////////////////////////////////////////////////////////////////////////////////////
    printMessage("=> Analyzing qualified matches...", "");

    try {
        $defaultSeason = (date('Y')-1) . "/" . substr(date('Y'), 2);

        $datesToAnalyze = $retrieveObj->getDatesToAnalyze();
        if(!in_array($selectedDate, $datesToAnalyze)) {
            $datesToAnalyze[] = $selectedDate;
        }

        foreach ($datesToAnalyze as $analyzeDate) {
            printMessage("   - [{$analyzeDate}]...");
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
                    printMessage($e->getMessage(), "");
                }

                $matchesToCheck[] = $match;
            }
        }
    }
    catch (Exception $e) {
        printMessage("   Failed to analyze! Reason: " . $e->getMessage(), "");
    }
}
catch(Exception $e) {
    echo " Error: " . $e->getMessage() . PHP_EOL;
}
printMessage("================= Finished at " . getDateTime() . " =================", "");

exit(1);