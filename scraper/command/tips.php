<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 7/19/2020
 * Time: 2:45 PM
 */

// error_reporting(0);
// command: php scraper_step_fetch.php [date=2020-10-09] [update_similarity]

require_once "../inc/SaveData.php";

$_gDbConn_ = new Database();

printMessage("================= Started at " . getDateTime() . " =================", "", "tips");
try {
    $_gDbConn_->openDB();

    $retrieveObj = new RetrieveData($_gDbConn_);
    $saveObj = new SaveData($_gDbConn_);

    $_gActiveDate_ = '';
    $forceSimilarityUpdate = false;
    if($argc > 1) {
        $tmp = explode("=", $argv[1]);

        if($tmp[0] == 'date') {
            $_gActiveDate_ = $tmp[sizeof($tmp) - 1];
        }
    	if(isset($argv[2]) && $argv[2] == 'update_similarity'){
    		$forceSimilarityUpdate = true;
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
	printMessage("=> Retrieving Tips for matches...", "", "tips");

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
		printMessage("   - Checking on Predictz ...", "", "tips");

		if($_gActiveDate_ >= $today) {
			$command = CMD_SCRAPER_PREDICTZ . "date=\"{$_gActiveDate_}\" country=\"" . implode(',', $selectedCountryScraper['predictz']) . "\"";
			var_dump($command);
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
		printMessage("   - Checking on Windrawwin ...", "", "tips");
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
		printMessage("   - Checking on SoccerWay ...", "", "tips");
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

				$findResult = findSimilarMatchBy($country, $division, $team_1, $team_2, $matches, 'soccerway', true);

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
		printMessage( "   Failed to fetch tips! Reason: " . $e->getMessage(), "", "tips");
	}
}
catch(Exception $e) {
    echo " Error: " . $e->getMessage() . PHP_EOL;
}
$_gDbConn_->closeDB();

printMessage("================= Finished at " . getDateTime() . " =================", "", "tips");

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
			if($showLog){
				echo ("division: {$division}, division_s: {$division_s}, team_1: {$team_1}, team_2: {$team_2}, team_1_s: {$team_1_s}, team_2_s: {$team_2_s}");
			}
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

	if($showLog && $result != null) {
		echo "$team_1 --- $team_2" . PHP_EOL;
		var_dump($result);
	}

	return $result;
}

exit(1);
