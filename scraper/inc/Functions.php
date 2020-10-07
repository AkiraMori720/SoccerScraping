<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 8/16/2018
 * Time: 3:00 PM
 */
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

if(!function_exists('getArrayValueInArray')) {
    function getArrayValueInArray($arr, $key)
    {
        $val = array();

        if (hasValidValueIn($arr, $key, true)) {
            $val = $arr[$key];
        }

        return $val;
    }
}

if(!function_exists('getExcelColNameFromIndex')) {
    function getExcelColNameFromIndex($colNo)
    {
        $numeric = $colNo % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval($colNo / 26);
        if ($num2 > 0) {
            return getExcelColNameFromIndex($num2 - 1) . $letter;
        } else {
            return $letter;
        }

        // return chr(0x41 + $colNo) . "";
    }
}

if(!function_exists('get_client_ip')) {
    function get_client_ip()
    {
        $ipaddress = '';

        if (getenv('HTTP_CLIENT_IP')) $ipaddress = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR')) $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('HTTP_X_FORWARDED')) $ipaddress = getenv('HTTP_X_FORWARDED');
        else if (getenv('HTTP_FORWARDED_FOR')) $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if (getenv('HTTP_FORWARDED')) $ipaddress = getenv('HTTP_FORWARDED');
        else if (getenv('REMOTE_ADDR')) $ipaddress = getenv('REMOTE_ADDR');
        else $ipaddress = 'UNKNOWN';

        if ($ipaddress == '::1') {
            $ipaddress = '127.0.0.1';
        }

        return $ipaddress;
    }
}

if(!function_exists('getOSString')){
    function getOSString() {
        return strtoupper(substr(PHP_OS, 0, 3));
    }
}

if(!function_exists('isWindowOS')){
    function isWindowOS() {
        return getOSString() == 'WIN';
    }
}

if(!function_exists('getServerHostName')){
    function getServerHostName() {
        return gethostname();
    }
}

if(!function_exists('getServerIpAddr')){
    function getServerIpAddr() {
        return isWindowOS() ? getHostByName(php_uname('n')) : $_SERVER['SERVER_ADDR'];
    }
}

if(!function_exists('getServerMacAddr')){
    function getServerMacAddr() {
        $ipAddr = getServerIpAddr();

        $command = isWindowOS() ? 'ipconfig /all' : 'ifconfig';
        ob_start(); // Turn on output buffering
        system($command); //Execute external program to display output
        $mycom=ob_get_contents(); // Capture the output into a variable
        ob_clean(); // Clean (erase) the output buffer

        $findme = isWindowOS() ? "Physical" : $ipAddr;
        $pmac = strpos($mycom, $findme); // Find the position of Physical text
        $mac= substr($mycom,($pmac+(isWindowOS() ? 36 : -40)),17); // Get Physical Address

        return $mac;
    }
}

if(!function_exists('getUUID')) {
    function getUUID()
    {
        return uniqid("", true);
    }
}

if(!function_exists('generate_uuid')) {
    function generate_uuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0C2f) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0x2Aff), mt_rand(0, 0xffD3), mt_rand(0, 0xff4B)
        );
    }
}

if(!function_exists('getToken')) {
    function getToken($length = 16, $encode=false)
    {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet .= "0123456789";
        $max = strlen($codeAlphabet);

        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[random_int(0, $max - 1)];
        }

        return $encode ? base64_encode($token) : $token;
    }
}

if(!function_exists('encrypt_decrypt')){
    /**
     * ------------------------------------------------------------------------
     *  encrypt_decrypt :
     * ========================================================================
     *
     *
     * @param $action
     * @param $string
     * @param $secret_key
     * @return bool|string
     *
     * ------------------------------------------------------------------------
     */
    function encrypt_decrypt($action, $string, $secret_key) {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $secret_iv = $encrypt_method;
        // hash
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        if ( $action == 'encrypt' ) {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        }
        else if( $action == 'decrypt' ) {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }
}

if(!function_exists('encrypt')){
    function encrypt($plain, $key) {
        return encrypt_decrypt('encrypt', $plain, $key);
    }
}

if(!function_exists('decrypt')){
    function decrypt($encrypted, $key) {
        return encrypt_decrypt('decrypt', $encrypted, $key);
    }
}

if(!function_exists('getMilliseconds')) {
    function getMilliseconds()
    {
        return str_replace(" ", "", str_replace(".", "", "" . microtime()));
    }
}

if(!function_exists('getUtcTimeBy')) {
    function getUtcTimeBy($diffDays = 0) {
        $dt = new DateTime();
        $dt->setTimeZone(new DateTimeZone('UTC'));

        try {
            $interval = new DateInterval("P{$diffDays}D");
            $dt->sub($interval);
        }
        catch(Exception $e) {}

        return $dt->format('Y-m-d\TH:i:s\Z');
    }
}

if(!function_exists('getDateTime')) {
    function getDateTime($format = '', $incHrs = 0, $time = null)
    {
        if ($format == null || strlen(trim($format)) == 0) {
            $format = "Y-m-d H:i:s";
        }

        if ($time == null) $time = time();

        $date = date($format, $time + $incHrs * 3600);
        return $date;
    }
}

if(!function_exists('getLastDateOfMonth')) {
    function getLastDateOfMonth($year, $month)
    {
        $query_date = "$year-$month-01";

        // Last day of the month.
        $lastDate = date('Y-m-t', strtotime($query_date));

        return $lastDate;
    }
}

if(!function_exists('getDateForWeekDayOfMonth')) {
    function getDateForWeekDayOfMonth($date, $weekDay, $weekOrd)
    {
        $Names = array(0 => "Sun", 1 => "Mon", 2 => "Tue", 3 => "Wed", 4 => "Thu", 5 => "Fri", 6 => "Sat");
        $ThisMonthTS = strtotime(date("Y-m-01", strtotime($date)));
        $NextMonthTS = strtotime(date("Y-m-01", strtotime("next month", strtotime($date))));

        $DateOfInterest = (-1 == $weekOrd) ?
            strtotime("last " . $Names[$weekDay], $NextMonthTS) : // The last occurrence of the day in this month.  Calculated as "last dayname" from the first of next month, which will be the last one in this month.
            strtotime($Names[$weekDay] . " + " . ($weekOrd - 1) . " weeks", $ThisMonthTS);

        return date('Y-m-d', $DateOfInterest);
    }
}

if(!function_exists('time_diff')) {
    function time_diff(DateTimeInterface $b, DateTimeInterface $a, $absolute = false, $cap = 'H')
    {

        // Get unix timestamps, note getTimeStamp() is limited
        $b_raw = intval($b->format("U"));
        $a_raw = intval($a->format("U"));

        // Initial Interval properties
        $h = 0;
        $m = 0;
        $invert = 0;

        // Is interval negative?
        if (!$absolute && $b_raw < $a_raw) {
            $invert = 1;
        }

        // Working diff, reduced as larger time units are calculated
        $working = abs($b_raw - $a_raw);

        // If capped at hours, calc and remove hours, cap at minutes
        if ($cap == 'H') {
            $h = intval($working / 3600);
            $working -= $h * 3600;
            $cap = 'M';
        }

        // If capped at minutes, calc and remove minutes
        if ($cap == 'M') {
            $m = intval($working / 60);
            $working -= $m * 60;
        }

        // Seconds remain
        $s = $working;

        // Build interval and invert if necessary
        $interval = new DateInterval('PT' . $h . 'H' . $m . 'M' . $s . 'S');
        $interval->invert = $invert;

        return $interval;
    }
}

if(!function_exists('getDiffDays')) {
    function getDiffDays($date1, $date2)
    {
        $datetime1 = new DateTime($date1);
        $datetime2 = new DateTime($date2);

        $difference = $datetime1->diff($datetime2);

        return ($date2 < $date1 ? "-" : "") . "{$difference->days}";
    }
}

if(!function_exists('convertTimeZoneOfDate')) {
    function convertTimeZoneOfDate($date, $newTimeZone, $defaultTimeZone = '')
    {
        if (isEmptyString($defaultTimeZone)) {
            $defaultTimeZone = date_default_timezone_get();
        }
        $date = new DateTime($date, new DateTimeZone($defaultTimeZone));
        $date->setTimezone(new DateTimeZone($newTimeZone));

        return $date->format('Y-m-d');
    }
}

if(!function_exists('log_to_file')) {
    function log_to_file($data, $fileSuffix, $toJson = true)
    {
        try {
            if(isEmptyString($fileSuffix)){
                $fileName = "log_" . getDateTime('Y_m_d') .  ".txt";
            } else {
                $fileName = "log_" . getDateTime('Y_m_d') . "_" .$fileSuffix .  ".txt";
            }
            if ($toJson) {
                $data['ip'] = get_client_ip();
                $data['log_time'] = getDateTime();
            }

            $logPath = dirname(__DIR__) . "/logs/";
            createDIR($logPath);
            $myFile = fopen($logPath . $fileName, "a");
            if ($myFile) {
                fwrite($myFile, ($toJson ? json_encode($data, JSON_PRETTY_PRINT) : $data) . PHP_EOL);
                fclose($myFile);
            }
        } catch (Exception $e) {
            ;
        }
    }
}

if(!function_exists('is_pdf')) {
    function is_pdf($file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        return in_array($ext, array('pdf'));
    }
}

if(!function_exists('is_excel')) {
    function is_excel($file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        return in_array($ext, array('xls', 'xlsx'));
    }
}

if(!function_exists('is_word')) {
    function is_word($file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        return in_array($ext, array('doc', 'docx'));
    }
}

if(!function_exists('writeStreamToFile')) {
    function writeStreamToFile($path, $data)
    {
        try {
            $source = fopen($data, 'r');
            $destination = fopen($path, 'w');

            stream_copy_to_stream($source, $destination);

            fclose($source);
            fclose($destination);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}

if(!function_exists('writeToFile')) {
    function writeToFile($path, $data)
    {
        try {
            $handler = fopen($path, 'wb');
            if ($handler) {
                fwrite($handler, $data);
                fclose($handler);
            }
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}

if(!function_exists('saveImageLinkToFile')) {
    function saveImageLinkToFile($link, $path, $name = '')
    {
        $fileName = null;
        try {
            createDIR($path);
            $orgName = pathinfo($link, PATHINFO_FILENAME);
            $ext = pathinfo($link, PATHINFO_EXTENSION);

            $fileName = (isEmptyString($name) ? $orgName : $name) . ".{$ext}";
            $filePath = $path . "/" . $fileName;

            if(file_exists($filePath) && is_file($filePath)) {
                unlink($filePath);
            }

            file_put_contents($filePath, file_get_contents($link));
        }
        catch (Exception $e) {
            $fileName = null;
        }

        return $fileName;
    }
}

if(!function_exists('extractZipFile')) {
    function extractZipFile($zipFile, $toPath)
    {
        $zip = new ZipArchive;
        $res = $zip->open($zipFile);
        if ($res === TRUE) {
            // extract it to the path we determined above
            $zip->extractTo($toPath);
            $zip->close();

            return true;
        }

        return false;
    }
}

if(!function_exists('searchTree')) {
    /**
     * ------------------------------------------------------------------------
     *  searchTree :
     * ========================================================================
     *
     *
     * @param $dir
     * @param $searchExts - Search Extensions
     * @param int $curDepth
     * @param int $maxDepth
     * @param bool $fileMode
     * @return array
     *
     * ------------------------------------------------------------------------
     */
    function searchTree($dir, $searchExts, $curDepth = 1, $maxDepth = 1000, $fileMode = true)
    {
        if ($curDepth > $maxDepth) {
            return array();
        }

        $files = array_diff(scandir($dir), array('.', '..'));

        $results = array();
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $data = searchTree($path, $searchExts, $curDepth + 1, $maxDepth, $fileMode);

                if (sizeof($data) > 0) {
                    if ($fileMode) {
                        $results = array_unique(array_merge($results, $data));
                    } else {
                        $results[$file] = $data;
                    }
                }
            } else {
                if ($searchExts != null && is_array($searchExts)) {
                    $ext = pathinfo($path, PATHINFO_EXTENSION);
                    if (in_array($ext, $searchExts)) {
                        $results[] = $path;
                    }
                } else {
                    $results[] = $path;
                }
            }
        }
        return $results;
    }
}

if(!function_exists('delTree')) {
    function delTree($dir)
    {
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            exec(sprintf("rd /s /q %s", escapeshellarg($dir)));
        } else {
            exec(sprintf("rm -rf %s", escapeshellarg($dir)));
        }
    }
}

if(!function_exists('createDIR')) {
    function createDIR($path, $permission=0777, $recursive = TRUE)
    {
        $error = '';
        if(!file_exists($path) || !is_dir($path)) {
            if (!mkdir($path, $permission, $recursive)) {
                $error = error_get_last();
            }
        }

        return $error;
    }
}

if(!function_exists('executeShellCommand')) {
    function executeShellCommand($command, $toJSON = true, $assoc = TRUE)
    {
        $response = null;
        if(!isEmptyString($command)) {
            $output = shell_exec($command);

            if($toJSON) {
                $response = json_decode($output, true);
            }
            else {
                $response = $output;
            }
        }

        return $response;
    }
}

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

if(!function_exists('getCorrectSeason')) {
    function getCorrectSeason($season, $siteName)
    {
        $customSeason = $season;
        if(in_array($siteName, array('soccervista','predictz','soccerway'))) {
            $tmp = explode('/', $season);
            $nextY = $tmp[0] + 1;
            $customSeason = "{$tmp[0]}/{$nextY}";
        }

        return $customSeason;
    }
}

if(!function_exists('printMessage')) {
    function printMessage($message, $suffix=PHP_EOL, $fileSuffix='', $logToFile = true)
    {
        if($logToFile) {
            log_to_file($message . $suffix, $fileSuffix, false);
        }
        else {
            echo $message . $suffix;
        }
    }
}
