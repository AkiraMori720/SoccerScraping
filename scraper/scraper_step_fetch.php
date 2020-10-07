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

printMessage("================= Started at " . getDateTime() . " =================", "", "fetch");
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
    // Get Leagues
    ////////////////////////////////////////////////////////////////////////////////////
    printMessage("=> Retrieving leagues...", "", "fetch");

    $possibleSites = $_gDbConn_->executeSQLAsArray("SELECT * FROM base_sites");
    foreach ($possibleSites as $site) {
        $siteName = getValueInArray($site, 'site');
        $siteLink = getValueInArray($site, 'league_link');

        printMessage("   - Checking on the site [{$siteName}] ...", "", "fetch");

//        $countries = $_gDbConn_->executeSQLAsArray("SELECT * FROM base_country WHERE {$siteName} NOT IN (SELECT DISTINCT country FROM base_leagues WHERE site='{$siteName}')");
        $countries = $_gDbConn_->executeSQLAsArray("SELECT * FROM base_country WHERE season='{$escapedSeason}'");

        $selectedCountries = "";
        foreach ($countries as $country) {
            $countryName = getValueInArray($country, $siteName);
            if(!isEmptyString($countryName)) {
                $selectedCountries .= "{$countryName},";
            }
        }
        $selectedCountries = trim($selectedCountries, ',');

        if(isEmptyString($selectedCountries)) {
            continue;
        }

        try {
            $command = CMD_SCRAPER_BASE_LEAGUES . "site=\"{$siteName}\" link=\"{$siteLink}\" country=\"{$selectedCountries}\"";
            $jsonData = executeShellCommand($command);

            if($jsonData != null) {
                $totalFound = sizeof($jsonData);
                printMessage("     Found {$totalFound} league(s).", "", "fetch");
                if($totalFound > 0) {
                    $saveObj->saveLeagues($siteName, $jsonData);
                }
            }
        }
        catch(Exception $e) {
            printMessage($e->getMessage(), "", "fetch");
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // Get Clubs
    ////////////////////////////////////////////////////////////////////////////////////
    printMessage("=> Retrieving Clubs...", "", "fetch");

    $possibleSites = $_gDbConn_->executeSQLAsArray("SELECT * FROM base_sites WHERE `site`<>'soccerbase'");
    $sql = <<<EOD
SELECT
  base_similarity.*
FROM
(SELECT * FROM base_leagues_recommend WHERE season='{$escapedSeason}') leagues
INNER JOIN
base_similarity
ON base_similarity.`country`=leagues.`country` AND
base_similarity.`type`='division' AND base_similarity.`oddsportal`=leagues.`division`
EOD;

    $recommendLeagues = $_gDbConn_->executeSQLAsArray($sql);

    foreach ($possibleSites as $site) {
        $siteName = getValueInArray($site, 'site');
        $customSeason = getCorrectSeason($_gActiveSeason_, $siteName);

        foreach ($recommendLeagues as $recommendLeague) {
            $recommendCountry = getValueInArray($recommendLeague, 'country');
            $recommendLeague = getValueInArray($recommendLeague, $siteName);

            $rmdCountries = $_gDbConn_->executeSQLAsArray("SELECT * FROM base_country WHERE country='{$recommendCountry}' AND season='{$escapedSeason}'");
            if(sizeof($rmdCountries) > 0) {
                $rmdCountry = $rmdCountries[0];

                $sql = <<<EOD
SELECT COUNT(id) cnt 
FROM base_clubs 
WHERE 
  season='{$escapedSeason}' AND 
  site='{$siteName}' AND 
  country='{$rmdCountry[$siteName]}' AND 
  league='{$recommendLeague}'
EOD;
                $recClubsCount = $_gDbConn_->executeSQLAsArray($sql);
                $numberOfClubs = $recClubsCount[0]['cnt'];
                if($numberOfClubs > 0) {
                    continue;
                }

                $leagues = $retrieveObj->getBaseLeagues($siteName, $rmdCountry[$siteName], $recommendLeague);
                foreach ($leagues as $league) {
                    $leagueLink = getValueInArray($league, 'link');

                    printMessage("   - Checking clubs for [{$league['site']}][{$league['country']}][{$league['league']}] ...", "", "fetch");

                    try {
                        $command = CMD_SCRAPER_BASE_CLUBS . "season=\"{$customSeason}\" site=\"{$siteName}\" link=\"{$leagueLink}\"";
                        $jsonData = executeShellCommand($command);

                        if($jsonData != null) {
                            $totalFound = sizeof($jsonData);
                            printMessage("     Found {$totalFound} club(s).", "", "fetch");
                            if($totalFound > 0) {
                                $saveObj->saveClubs($_gActiveSeason_, $siteName, $rmdCountry[$siteName], $recommendLeague, $jsonData);
                            }
                        }
                    }
                    catch(Exception $e) {
                        printMessage($e->getMessage(), "", "fetch");
                    }
                }
            }
        }
    }
}
catch(Exception $e) {
    echo " Error: " . $e->getMessage() . PHP_EOL;
}
$_gDbConn_->closeDB();

printMessage("================= Finished at " . getDateTime() . " =================", "", "fetch");

exit(1);