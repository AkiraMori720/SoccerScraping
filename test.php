<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 5/29/2020
 * Time: 1:53 PM
 */

$year = '2020';
echo $year.' => ' .date("W",strtotime('28th December '.$year)) .' Weeks' . PHP_EOL;

echo date("F",strtotime("$year-01-01")) . PHP_EOL;


$tmp = array(array(1,0,1,2,0,3,0,3,0,1,1,0,1,0,2,0,1,0,3,1),
array(1,0,2,2,1,1,1,1,2,1,1,2,0,2,1,0,2,1,1,3));

echo date("W", strtotime('2020-06-19')) . PHP_EOL;

$date = new DateTime('2020-06-19', new DateTimeZone('Asia/Shanghai'));
$date->setTimezone(new DateTimeZone('Europe/Madrid'));
echo $date->format('Y-m-d H:i:s') . "\n";


echo preg_replace('/(\d){1} /', '$1.', "1  Bundesliga") . PHP_EOL;

$score = preg_replace('/[a-zA-Z ]/', '', "2E : 3E");
echo preg_replace('/[:]/', '-', $score) . PHP_EOL;

echo (stripos("European Championships", "German") === false) ? 'no' : 'yes' . PHP_EOL;

$date = DateTime::createFromFormat('dM Y', "09Nov 2019");
$dateFormat=$date->format('Y-m-d');
$date = '20' . substr($dateFormat, 2);
echo $date . PHP_EOL;

$percent = 0;
similar_text(strtolower('Superliga'), strtolower('2. Superliga'), $percent);
echo $percent . PHP_EOL;

$percent = calMatchesSimilarity(
    "fc kickers wurzburg", "magdeburg", "Wurzburger Kickers", "Magdeburg");
echo $percent;

echo trim("1. Bundesliga", '/[1.]/');


function calMatchesSimilarity($match_1_team_a, $match_1_team_b, $match_2_team_a, $match_2_team_b)
{
    $percent_1 = 0;
    $percent_2 = 0;

    $match_1_team_a = strtolower($match_1_team_a);
    $match_1_team_b = strtolower($match_1_team_b);
    $match_2_team_a = strtolower($match_2_team_a);
    $match_2_team_b = strtolower($match_2_team_b);

    similar_text("{$match_1_team_a}  v  {$match_1_team_b}", "{$match_2_team_a}  v  {$match_2_team_b}", $percent_1);
    similar_text("{$match_1_team_a}  v  {$match_1_team_b}", "{$match_2_team_b}  v  {$match_2_team_a}", $percent_2);

    return max($percent_1, $percent_2);
}

/*
if(!function_exists('isEmptyString')) {
    function isEmptyString($value)
    {
        return $value == null || strlen(trim($value)) == 0;
    }
}

if(!function_exists('hasValidValueIn')) {
    function hasValidValueIn($arr, $key, $isArray = false)
    {
        return isset($arr[$key]) && (!$isArray ? !isEmptyString($arr[$key]) : true);
    }
}

if(!function_exists('getValueInArray')) {
    function getValueInArray($arr, $key, $default = '')
    {
        $val = $default;

        if (hasValidValueIn($arr, $key)) {
            $val = trim($arr[$key]);
            if (strlen($val) == 0) {
                $val = $default;
            }
        }

        return $val;
    }
}

$jsonStr = file_get_contents("sample.json");

$jsonObj = json_decode($jsonStr, true);

$data = $jsonObj['data'];
$matches = $data['matches'];

foreach ($data['predictz'] as $predictz) {
    $country   = getValueInArray($predictz, 'country');
    $division  = str_replace('-', '. ', getValueInArray($predictz, 'division'));
    $team_1    = getValueInArray($predictz, 'team_1');
    $team_2    = getValueInArray($predictz, 'team_2');

    for($i = 0; $i < sizeof($matches); $i++) {
        $country_s   = getValueInArray($matches[$i], 'country');
        $division_s  = getValueInArray($matches[$i], 'division');
        $team_1_s    = getValueInArray($matches[$i], 'team1');
        $team_2_s    = getValueInArray($matches[$i], 'team2');

        if(strtolower($country) == strtolower($country_s)) {
            if(strtolower($country) == 'germany') {
                $division = trim($division, '/[1.]/');
            }

            $percent = 0;
            similar_text(strtolower($division), strtolower($division_s), $percent);
            $bSameDivision = $percent >= 95;

            if($bSameDivision) {
                echo sprintf("%s --- %s : %.02f", $division, $division_s, $percent) . PHP_EOL;
                $similarity = calMatchesSimilarity($team_1, $team_2, $team_1_s, $team_2_s);
                echo sprintf("%s:%s --- %s:%s === %.02f", $team_1, $team_2, $team_1_s, $team_2_s, $similarity) . PHP_EOL;
                if($similarity >= 80) {
                    $matches[$i]['predictz_result']= getValueInArray($predictz, 'result');
                    $matches[$i]['predictz_score'] = getValueInArray($predictz, 'score');
                    break;
                }
            }
        }
    }
}

foreach ($data['windrawwin'] as $windrawwin) {
    $country   = getValueInArray($windrawwin, 'country');
    $division  = getValueInArray($windrawwin, 'division');
    $team_1    = getValueInArray($windrawwin, 'team_1');
    $team_2    = getValueInArray($windrawwin, 'team_2');

    for($i = 0; $i < sizeof($matches); $i++) {
        $country_s   = getValueInArray($matches[$i], 'country');
        $division_s  = getValueInArray($matches[$i], 'division');
        $team_1_s    = getValueInArray($matches[$i], 'team1');
        $team_2_s    = getValueInArray($matches[$i], 'team2');

        if(strtolower($country) == strtolower($country_s)) {
            if(strtolower($country) == 'germany') {
                $division = trim($division, '/[1.]/');
            }

            $percent = 0;
            similar_text(strtolower($division), strtolower($division_s), $percent);
            $bSameDivision = $percent >= 95;

            if($bSameDivision) {
                $similarity = calMatchesSimilarity($team_1, $team_2, $team_1_s, $team_2_s);
                if($similarity >= 80) {
                    $matches[$i]['windrawwin_result']= $windrawwin['result'];
                    $matches[$i]['windrawwin_score'] = $windrawwin['score'];
                    break;
                }
            }
        }
    }
}

foreach ($data['soccerway'] as $soccerway) {
    $country   = getValueInArray($soccerway, 'country');
    $division  = getValueInArray($soccerway, 'division');
    $team_1    = getValueInArray($soccerway, 'team_1');
    $team_2    = getValueInArray($soccerway, 'team_2');

    for($i = 0; $i < sizeof($matches); $i++) {
        $country_s   = getValueInArray($matches[$i], 'country');
        $division_s  = getValueInArray($matches[$i], 'division');
        $team_1_s    = getValueInArray($matches[$i], 'team1');
        $team_2_s    = getValueInArray($matches[$i], 'team2');

        if(strtolower($country) == strtolower($country_s)) {
            if(strtolower($country) == 'germany') {
                $division = trim($division, '/[1.]/');
            }

            $percent = 0;
            similar_text(strtolower($division), strtolower($division_s), $percent);
            $bSameDivision = $percent >= 95;

            if($bSameDivision) {
                $similarity = calMatchesSimilarity($team_1, $team_2, $team_1_s, $team_2_s);
                if($similarity >= 80) {
                    $matches[$i]['soccerway']= getValueInArray($soccerway, 'link');
                    break;
                }
            }
        }
    }
}

echo json_encode($matches, JSON_PRETTY_PRINT);
*/