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

printMessage("================= Started at " . getDateTime() . " =================", "", "matches");
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
    // Get Referees
    ////////////////////////////////////////////////////////////////////////////////////
    printMessage("=> Retrieving Referees...", "", "matches");

    foreach ($recBaseCountries as $recCountry) {
        $selectedCountry = getValueInArray($recCountry, 'country');
        if(isEmptyString($selectedCountry)) {
            continue;
        }

        $leagues = $retrieveObj->getLeaguesFromSoccerBase($selectedCountry);

        // Fetch refers
        foreach ($leagues as $league) {
            printMessage("   - Checking for the country [{$selectedCountry}][{$league['league']}] ...", "", "matches");
            try {
                $leagueID = $league['league_id'];

                $command = CMD_SCRAPER_REFEREE_LIST . "season=\"{$_gActiveSeason_}\" league=\"{$leagueID}\"";
                $jsonData = executeShellCommand($command);

                if($jsonData != null) {
                    $totalFound = sizeof($jsonData);
                    printMessage("     Found {$totalFound} referee(s).", "", "matches");
                    if($totalFound > 0) {
                        $saveObj->saveRefereeList($_gActiveSeason_, $jsonData);

                        foreach ($jsonData as $refereeItem) {
                            $link = $refereeItem['link'];

                            $tmp = preg_replace('/\//', '/20', $_gActiveSeason_);
                            $command = CMD_SCRAPER_REFEREE_DETAIL . "season=\"{$tmp}\" link=\"{$link}\"";
                            $jsonData = executeShellCommand($command);
                            if($jsonData != null) {
                                $saveObj->saveRefereeDetail($link, $_gActiveSeason_, $selectedCountry, $jsonData);
                            }
                        }
                    }
                }
            }
            catch(Exception $e) {
                printMessage($e->getMessage(), "", "matches");
            }
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // Get Rankings
    ////////////////////////////////////////////////////////////////////////////////////
    printMessage("=> Retrieving Rankings...", "", "matches");

    $leagues = $retrieveObj->getLeaguesForRankings($_gActiveSeason_);

    foreach ($leagues as $league) {
        $country = getValueInArray($league, 'country');
        $leaguesOdds  = getValueInArray($league, 'divisions_oddsportal');
        $leaguesSoway = getValueInArray($league, 'divisions_soccerway');

        printMessage("   - Checking for [{$country}] => [{$leaguesOdds}] ...", "", "matches");

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
            printMessage("    Failed to fetch rankings! " . $e->getMessage(), "", "matches");
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // Get Matches
    ////////////////////////////////////////////////////////////////////////////////////
    printMessage("=> Retrieving Matches...", "", "matches");

    $datesToCheck = $retrieveObj->getDatesToCheck();
    if(!in_array($_gActiveDate_, $datesToCheck)) {
        $datesToCheck[] = $_gActiveDate_;
    }

    $sites = array(
        'oddsportal' => CMD_SCRAPER_MATCH_ODDSPORTAL,
        'soccervista' => CMD_SCRAPER_MATCH_SOCCERVISTA
    );

    foreach ($datesToCheck as $matchDate) {
        try {
            foreach ($sites as $siteName => $command) {
                printMessage("   - Checking for [{$matchDate}][{$siteName}] ...", "", "matches");

                $country = array();
                foreach ($recBaseCountries as $item) {
                    $country[] = str_replace(" ", "-", $item[$siteName]);
                }

                $command .= "date=\"{$matchDate}\" country=\"" . implode(',', $country) . "\"";
                $jsonData = executeShellCommand($command);
                if($jsonData != null) {
                    $totalFound = sizeof($jsonData);
                    printMessage("     Found {$totalFound} match(es).", "", "matches");
                    if($totalFound > 0) {
                        eval("\$saveObj->saveMatches_$siteName(\$matchDate, \$jsonData);");
                    }
                }
            }

            // Calculate similarity
            $saveObj->checkMatches($matchDate, $country);
        }
        catch(Exception $e) {
            printMessage("    Failed to fetch matches! " . $e->getMessage(), "", "matches");
        }
    }
}
catch(Exception $e) {
    echo " Error: " . $e->getMessage() . PHP_EOL;
}
$_gDbConn_->closeDB();
printMessage("================= Finished at " . getDateTime() . " =================", "", "matches");

exit(1);