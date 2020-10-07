<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 7/19/2020
 * Time: 2:45 PM
 */

// error_reporting(0);

require_once "../inc/SaveData.php";

$_gDbConn_ = new Database();

printMessage("================= Started at " . getDateTime() . " =================", "", "team_detail");
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
    // Get Team Details
    ////////////////////////////////////////////////////////////////////////////////////
    printMessage("=> Retrieving team details for qualified matches...", "", "team_detail");

    // Fetch team details
    $matches = $retrieveObj->getQualifiedMatchesToCheck($_gActiveDate_, $_gActiveSeason_);

    foreach ($matches as $match) {
        printMessage("   - Checking for the match [{$match['home_team']}] : [{$match['away_team']}] ...", "", "team_detail");

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
            printMessage( "   Failed to fetch tips! Reason: " . $e->getMessage(), "", "team_detail");
        }
    }
}
catch(Exception $e) {
    echo " Error: " . $e->getMessage() . PHP_EOL;
}
$_gDbConn_->closeDB();
printMessage("================= Finished at " . getDateTime() . " =================", "", "team_detail");

exit(1);