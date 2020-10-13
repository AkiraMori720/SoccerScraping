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

printMessage("================= Started at " . getDateTime() . " =================", "");
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

    chdir(ROOT_PATH . "/casperjs");

    ////////////////////////////////////////////////////////////////////////////////////
    // Get Rankings
    ////////////////////////////////////////////////////////////////////////////////////
    printMessage("=> Retrieving Rankings...", "");

    $leagues = $retrieveObj->getLeaguesForRankings($_gActiveSeason_);

    foreach ($leagues as $league) {
        $country = getValueInArray($league, 'country');
        $leaguesOdds  = getValueInArray($league, 'divisions_oddsportal');
        $leaguesSoway = getValueInArray($league, 'divisions_soccerway');

        printMessage("   - Checking for [{$country}] => [{$leaguesOdds}] ...", "");

        try {
            $customSeason = getCorrectSeason($_gActiveSeason_, "soccerway");

            $strLeague = preg_replace('/[ ]/', '-', $leaguesSoway);
            $command = CMD_SCRAPER_TEAMS_RANKS . "season=\"{$customSeason}\" country=\"{$country}\" league=\"{$strLeague}\"";
            $jsonData = executeShellCommand($command);

            if($jsonData != null) {
                $totalFound = sizeof($jsonData);
                if($totalFound > 0) {
                    $saveObj->saveRankings($_gActiveSeason_, $jsonData);
                }
            }
        }
        catch(Exception $e) {
            printMessage("    Failed to fetch rankings! " . $e->getMessage(), "");
        }
    }
}
catch(Exception $e) {
    echo " Error: " . $e->getMessage() . PHP_EOL;
}
$_gDbConn_->closeDB();
printMessage("================= Finished at " . getDateTime() . " =================", "");

exit(1);