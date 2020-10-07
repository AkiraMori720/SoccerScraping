<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 6/16/2020
 * Time: 1:35 AM
 */

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