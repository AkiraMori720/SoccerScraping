<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 7/19/2020
 * Time: 2:45 PM
 */

// error_reporting(0);

require_once "inc/SaveData.php";

$retrieveObj = new RetrieveData();
$saveObj = new SaveData();

printMessage("================= Started at " . getDateTime() . " =================", "");
try {
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

    chdir(ROOT_PATH . "/casperjs");

    ////////////////////////////////////////////////////////////////////////////////////
    // Get Team Details
    ////////////////////////////////////////////////////////////////////////////////////
    printMessage("=> Retrieving team details for qualified matches...", "");

    // Fetch team details
    $matches = $retrieveObj->getQualifiedMatchesToCheck($selectedDate);

    foreach ($matches as $match) {
        printMessage("   - Checking for the match [{$match['home_team']}] : [{$match['away_team']}] ...", "");

        try {
            $country = $match['country'];
            $division= $match['division'];

            $link = $match['soccerway_link'];
            if(isEmptyString($link)) {
                continue;
            }

            printMessage("     link : {$link}", "");

            $command = CMD_SCRAPER_TEAMS_INFO . "link=\"{$link}\"";
            $output = shell_exec($command);
            $jsonData = json_decode($output, true);
            // printMessage(json_encode($jsonData, JSON_PRETTY_PRINT));

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
            printMessage( "   Failed to fetch tips! Reason: " . $e->getMessage(), "");
        }
    }
}
catch(Exception $e) {
    echo " Error: " . $e->getMessage() . PHP_EOL;
}
printMessage("================= Finished at " . getDateTime() . " =================", "");

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