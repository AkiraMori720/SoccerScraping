<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 8/25/2017
 * Time: 1:29 PM
 */

/**
 * Define Error Codes
 * Error Code Details : csxxx
 *   c - Category
 *   s - Sub category
 *   xxx - code
 *
 * Category : 3, 4, 5
 *    1, 2 : Reserved
 *    3 - Common Errors
 *    4 - Admin Errors
 *    5 - User Errors
 *
 *
 */

define('RES_C_SUCCESS', '0');
 
 
////////////////////////////////////////////////////////////////////
/// Define Common Error Codes
////////////////////////////////////////////////////////////////////

define('RES_C_ACCESS_DENIED', 	'RES_C_ACCESS_DENIED');
define('RES_C_INVALID', 		'RES_C_INVALID');
define('RES_C_UNKNOWN', 		'RES_C_UNKNOWN');
define('RES_C_ERR_404', 		'RES_C_ERR_404');
define('RES_C_ERR_DB', 		    'RES_C_ERR_DB');
define('RES_C_ERR_PHP', 		'RES_C_ERR_PHP');
define('RES_C_ERR_EXCEPTION', 	'RES_C_ERR_EXCEPTION');
define('RES_C_ERR_GENERAL', 	'RES_C_ERR_GENERAL');

define('RES_C_REQUIRE_LOGIN', 	'RES_C_REQUIRE_LOGIN');

define('RES_C_LOGIN_EXISTS', 			'RES_C_LOGIN_EXISTS');
define('RES_C_LOGIN_REQ_FIELDS', 		'RES_C_LOGIN_REQ_FIELDS');
define('RES_C_LOGIN_USER_INVALID', 		'RES_C_LOGIN_USER_INVALID');
define('RES_C_LOGIN_USER_PENDING', 		'RES_C_LOGIN_USER_PENDING');
define('RES_C_LOGIN_USER_INACTIVE', 	'RES_C_LOGIN_USER_INACTIVE');

define('RES_C_REG_REQ_FIELDS',      'RES_C_REG_REQ_FIELDS');
define('RES_C_REG_WRONG_PASSWORD',  'RES_C_REG_WRONG_PASSWORD');
define('RES_C_REG_WRONG_CAPTCHA',   'RES_C_REG_WRONG_CAPTCHA');
define('RES_C_REG_EXIST_EMAIL',     'RES_C_REG_EXIST_EMAIL');
define('RES_C_REG_EXIST_USER',      'RES_C_REG_EXIST_USER');
define('RES_C_REG_EXIST_QQ',        'RES_C_REG_EXIST_QQ');
define('RES_C_REG_FAILED_BY_ERR',   'RES_C_REG_FAILED_BY_ERR');

define('RES_C_UPLOAD_REQUIRE',  'RES_C_UPLOAD_REQUIRE');
define('RES_C_UPLOAD_INVALID',  'RES_C_UPLOAD_INVALID');
define('RES_C_UPLOAD_FAILED',   'RES_C_UPLOAD_FAILED');

define('RES_C_NO_XLS', 'RES_C_NO_XLS');
