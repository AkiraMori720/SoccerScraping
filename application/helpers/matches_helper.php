<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 6/16/2020
 * Time: 1:32 AM
 */


if(!function_exists('isSimilarDivision')) {
    function isSimilarDivision($country, $division_oddsportal, $division_soccervista, $defaultPercent = '95')
    {
        $percent = 0;
        if($country == 'Germany') {
            $division_soccervista = trim($division_soccervista, '/[1.]/');
        }
        if($country == 'Denmark') {
            if($division_oddsportal == 'Superliga') {
                $defaultPercent = '90';
            }
        }

        similar_text(strtolower($division_oddsportal), strtolower($division_soccervista), $percent);

        return $percent >= $defaultPercent;
    }
}


if(!function_exists('checkMatchesSimilarity')) {
    function checkMatchesSimilarity($param_1_team_a, $param_1_team_b, $param_2_team_a, $param_2_team_b, $defaultPercent = '80')
    {
        $percent_1 = 0;
        $percent_2 = 0;
        $percent_3 = 0;

        $match_1_team_a = preg_replace('/[-.]/', ' ', strtolower($param_1_team_a));
        $match_1_team_b = preg_replace('/[-.]/', ' ', strtolower($param_1_team_b));
        $match_2_team_a = preg_replace('/[-.]/', ' ', strtolower($param_2_team_a));
        $match_2_team_b = preg_replace('/[-.]/', ' ', strtolower($param_2_team_b));

        similar_text("{$match_1_team_a}  v  {$match_1_team_b}", "{$match_2_team_a}  v  {$match_2_team_b}", $percent_1);

        $tmp = explode(' ', $match_1_team_a);
        $tmp_1_team_a = '';
        for($k = sizeof($tmp) -1; $k >=0; $k--) {
            $tmp_1_team_a .= " {$tmp[$k]}";
        }
        $tmp_1_team_a = trim($tmp_1_team_a);
        similar_text("{$tmp_1_team_a}  v  {$match_1_team_b}", "{$match_2_team_a}  v  {$match_2_team_b}", $percent_2);

        $tmp = explode(' ', $match_1_team_b);
        $tmp_1_team_b = '';
        for($k = sizeof($tmp) -1; $k >=0; $k--) {
            $tmp_1_team_b .= " {$tmp[$k]}";
        }
        $tmp_1_team_b = trim($tmp_1_team_b);
        similar_text("{$match_1_team_a}  v  {$tmp_1_team_b}", "{$match_2_team_a}  v  {$match_2_team_b}", $percent_3);

        $percent_4 = 0;
        $percent_5 = 0;
        $percent_6 = 0;
        similar_text("{$match_1_team_a}  v  {$match_1_team_b}", "{$match_2_team_b}  v  {$match_2_team_a}", $percent_2);

        $tmp = explode(' ', $match_1_team_a);
        $tmp_1_team_a = '';
        for($k = sizeof($tmp) -1; $k >=0; $k--) {
            $tmp_1_team_a .= " {$tmp[$k]}";
        }
        $tmp_1_team_a = trim($tmp_1_team_a);
        similar_text("{$tmp_1_team_a}  v  {$match_1_team_b}", "{$match_2_team_b}  v  {$match_2_team_a}", $percent_5);

        $tmp = explode(' ', $match_1_team_b);
        $tmp_1_team_b = '';
        for($k = sizeof($tmp) -1; $k >=0; $k--) {
            $tmp_1_team_b .= " {$tmp[$k]}";
        }
        $tmp_1_team_b = trim($tmp_1_team_b);
        similar_text("{$match_1_team_a}  v  {$tmp_1_team_b}", "{$match_2_team_b}  v  {$match_2_team_a}", $percent_6);

        $percent = max($percent_1, $percent_2, $percent_3, $percent_4);

        $teams = null;
        if($percent >= $defaultPercent) {
            $teams = array();
            if($percent == $percent_1 || $percent == $percent_2 || $percent == $percent_3) {
                $teams[$param_1_team_a] = $param_2_team_a;
                $teams[$param_1_team_b] = $param_2_team_b;
            }
            else if($percent == $percent_4 || $percent == $percent_5 || $percent == $percent_6) {
                $teams[$param_1_team_a] = $param_2_team_b;
                $teams[$param_1_team_b] = $param_2_team_a;
            }

            $teams['similarity'] = $percent;
        }
        else {
            $tmp_1_team_a = '';
            $tmp_2_team_a = '';
            $tmp_2_team_b = '';
            if($match_1_team_a == $match_2_team_a) {
                $tmp_1_team_a = $match_1_team_a;
                $tmp_1_team_b = $match_1_team_b;
                $tmp_2_team_b = $match_2_team_b;
            }
            else if($match_1_team_a == $match_2_team_b) {
                $tmp_1_team_a = $match_1_team_a;
                $tmp_1_team_b = $match_1_team_b;
                $tmp_2_team_b = $match_2_team_a;
            }

            if(!isEmptyString($tmp_1_team_a)) {
                $arr_1_b = preg_split('/[ -]/', $tmp_1_team_b);
                $arr_2_b = preg_split('/[ -]/', $tmp_2_team_b);
                if( (sizeof($arr_1_b) == 1 && in_array($arr_1_b[0], $arr_2_b)) ||
                    (sizeof($arr_2_b) == 1 && in_array($arr_2_b[0], $arr_1_b)) ) {
                    if($match_1_team_a == $match_2_team_a) {
                        $teams[$param_1_team_a] = $param_2_team_a;
                        $teams[$param_1_team_b] = $param_2_team_b;
                    }
                    else if($match_1_team_a == $match_2_team_b) {
                        $teams[$param_1_team_a] = $param_2_team_b;
                        $teams[$param_1_team_b] = $param_2_team_a;
                    }

                    $percent = $defaultPercent;
                    $teams['similarity'] = $percent;
                }
            }
        }

        if($teams != null) {
            // echo "{$match_1_team_a} - {$match_1_team_b}, {$match_2_team_a} - {$match_2_team_b}, {$percent}" . PHP_EOL;
        }

        return $teams;
    }
}


if(!function_exists('removeLettersInScore')) {
    function removeLettersInScore($score)
    {
        $score = preg_replace('/[a-zA-Z ]/', '', $score);
        $score = preg_replace('/[:]/', '-', $score);

        return $score;
    }
}