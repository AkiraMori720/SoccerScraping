<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 3/2/2020
 * Time: 10:51 PM
 */


if(!function_exists('is_selected')) {
    function is_selected($val1, $val2)
    {
        return ($val1 == $val2) ? 'selected="selected"' : '';
    }
}

if(!function_exists('is_checked')) {
    function is_checked($val1, $val2)
    {
        return ($val1 == $val2) ? 'checked="checked"' : '';
    }
}

if(!function_exists('removeEmoji')) {
    function removeEmoji($str)
    {
        $pattern = '/[[:^print:]]/';
        $str = preg_replace($pattern, "", $str);

        return $str;
    }
}

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




if(!function_exists('isDevVersion')) {
    function isDevVersion()
    {
        return APP_STATUS_IN_DEV == 1;
    }
}

if(!function_exists('isLiveServer')) {
    function isLiveServer()
    {
        return LIVE_SERVER == 1;
    }
}

if(!function_exists('is_activeMenuGrp')) {
    function is_activeMenuGrp($menu1, $mnuGrp)
    {
        $val1 = floor($menu1 / 100);
        $val2 = floor($mnuGrp / 100);
        return ($val1 == $val2) ? 'active' : '';
    }
}

if(!function_exists('is_activeMenuItem')) {
    function is_activeMenuItem($menu1, $mnu)
    {
        return ($menu1 == $mnu) ? 'active' : '';
    }
}


if(!function_exists('is_Login')) {
    /**
     * ------------------------------------------------------------------------
     *  is_Login :
     * ========================================================================
     *
     *
     * @param $session
     * @return bool
     *
     * ------------------------------------------------------------------------
     */
    function is_Login($session)
    {
        return !empty($session) && $session->userdata(SESSION_KEY_USER);
    }
}

if(!function_exists('isAjaxRequest')) {
    function isAjaxRequest()
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return true;
        }
        return false;
    }
}

if(!function_exists('getUserName')) {
    function getUserName($session)
    {
        if (!empty($session) && $session->userdata(SESSION_KEY_USER)) {
            $user = $session->userdata[SESSION_KEY_USER];
            return trim($user['user_name']);
        }

        return '';
    }
}

if(!function_exists('getUserRealName')) {
    function getUserRealName($session)
    {
        if (!empty($session) && $session->userdata(SESSION_KEY_USER)) {
            $user = $session->userdata[SESSION_KEY_USER];
            return trim($user['real_name']);
        }

        return '';
    }
}

if(!function_exists('getUserID')) {
    function getUserID($session)
    {
        if (!empty($session) && $session->userdata(SESSION_KEY_USER)) {
            $user = $session->userdata[SESSION_KEY_USER];
            return trim($user['uid']);
        }

        return '';
    }
}

if(!function_exists('getUserType')) {
    function getUserType($session)
    {
        if (!empty($session) && $session->userdata(SESSION_KEY_USER)) {
            $user = $session->userdata[SESSION_KEY_USER];
            return trim($user['user_type']);
        }

        return '';
    }
}

if(!function_exists('isWebMaster')) {
    function isWebMaster($userData)
    {
        return $userData['user_type'] == USER_TYPE_WEBMASTER;
    }
}

if(!function_exists('isAdmin')) {
    function isAdmin($userData)
    {
        return floor(intval($userData['user_type']) / USER_TYPE_ADMIN) == 1;
    }
}

if(!function_exists('getCountryCode')) {
    function getCountryCode($country = null)
    {
        $all = array(
            lang(LANG_C_COUNTRY_US) => 'us',
            'english' => 'us',
            lang(LANG_C_COUNTRY_CN) => 'cn',
            'chinese' => 'cn',
        );

        if ($country == null || strlen(trim($country)) == 0) return "";

        return $all[$country];
    }
}

if(!function_exists('getCurrentLang')) {
    function getCurrentLang($session)
    {
        if (!empty($session) && $session->userdata(SESSION_KEY_LANG)) {
            $lang = $session->userdata[SESSION_KEY_LANG];

            return $lang;
        }

//    return get_cookie(SESSION_KEY_LANG);
        return '';
    }
}

if(!function_exists('getLangCode')) {
    function getLangCode($lang)
    {
        $all = array(ENGLISH => 'en',);

        if ($lang == null || strlen(trim($lang)) == 0) return "";

        return $all[$lang];
    }
}

if(!function_exists('removeEmoji')) {
    function removeEmoji($str)
    {
        $pattern = '/[[:^print:]]/';
        $str = preg_replace($pattern, "", $str);

        return $str;
    }
}

if(!function_exists('getValueInArrayByLang')) {
    function getValueInArrayByLang($arr, $key, $langCode = '')
    {
        $val = getValueInArray($arr, $key);
        if (!isEmptyString($langCode)) {
            $val = getValueInArray($arr, $key . "_" . $langCode, $val);
        }

        return $val;
    }
}
