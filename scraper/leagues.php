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
    // Get Leagues
    ////////////////////////////////////////////////////////////////////////////////////
    printMessage("=> Retrieving leagues...", "");

    $possibleSites = $_gDbConn_->executeSQLAsArray("SELECT * FROM base_sites");
    foreach ($possibleSites as $site) {
        $siteName = getValueInArray($site, 'site');
        $siteLink = getValueInArray($site, 'league_link');

        printMessage("   - Checking on the site [{$siteName}] ...", "");

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
                printMessage("     Found {$totalFound} league(s).", "");
                if($totalFound > 0) {
                    $saveObj->saveLeagues($siteName, $jsonData);
                }
            }
        }
        catch(Exception $e) {
            printMessage($e->getMessage(), "");
        }
    }
}
catch(Exception $e) {
    echo " Error: " . $e->getMessage() . PHP_EOL;
}
$_gDbConn_->closeDB();

printMessage("================= Finished at " . getDateTime() . " =================", "");

exit(1);