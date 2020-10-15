<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 7/19/2020
 * Time: 2:45 PM
 */

// error_reporting(0);
// Command: php leagues.php data=2020-10-13 country=Netherlands

require_once "../inc/SaveData.php";

$_gDbConn_ = new Database();

printMessage("================= Started at " . getDateTime() . " =================", "", "league");
try {
    $_gDbConn_->openDB();

    $retrieveObj = new RetrieveData($_gDbConn_);
    $saveObj = new SaveData($_gDbConn_);

    $_gActiveDate_ = '';
	$selectCountry = '';
    if($argc > 1) {
    	for($i = 1; $i<$argc; $i++){
			$tmp = explode("=", $argv[$i]);
			switch ($tmp[0]){
				case 'date':
					$_gActiveDate_ = $tmp[sizeof($tmp) - 1];
					break;
				case 'country':
					$selectCountry = $tmp[sizeof($tmp) - 1];
					break;
			}
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
    printMessage("=> Retrieving leagues...", "", "league");

    $possibleSites = $_gDbConn_->executeSQLAsArray("SELECT * FROM base_sites");
    foreach ($possibleSites as $site) {
        $siteName = getValueInArray($site, 'site');
        $siteLink = getValueInArray($site, 'league_link');

        printMessage("   - Checking on the site [{$siteName}] ...", "", "league");

//        $countries = $_gDbConn_->executeSQLAsArray("SELECT * FROM base_country WHERE {$siteName} NOT IN (SELECT DISTINCT country FROM base_leagues WHERE site='{$siteName}')");
		if(isEmptyString($selectCountry)){
        	$countries = $_gDbConn_->executeSQLAsArray("SELECT * FROM base_country WHERE season='{$escapedSeason}'");
		} else {
			$countries = $_gDbConn_->executeSQLAsArray("SELECT * FROM base_country WHERE season='{$escapedSeason}' AND country='{$selectCountry}'");
		}

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
            echo $command . PHP_EOL;
            $jsonData = executeShellCommand($command);
			var_dump($jsonData);

            if($jsonData != null) {
                $totalFound = sizeof($jsonData);
                printMessage("     Found {$totalFound} league(s).", "", "league");
                if($totalFound > 0) {
                    $saveObj->saveLeagues($siteName, $jsonData);
                }
            }
        }
        catch(Exception $e) {
            printMessage($e->getMessage(), "", "league");
        }
    }
}
catch(Exception $e) {
    echo " Error: " . $e->getMessage() . PHP_EOL;
}
$_gDbConn_->closeDB();

printMessage("================= Finished at " . getDateTime() . " =================", "", "league");

exit(1);
