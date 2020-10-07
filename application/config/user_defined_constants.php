<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 7/8/2017
 * Time: 12:01 AM
 */
error_reporting(0);

define('ASSETS_PATH',   'assets');
define('CSS_PATH',      ASSETS_PATH.'/css');
define('JS_PATH',       ASSETS_PATH.'/js');
define('IMG_PATH',      ASSETS_PATH.'/img');
define('LIB_PATH',      ASSETS_PATH.'/library');

define('DATA_PATH',     ASSETS_PATH.'/data');
define('XLS_TPL_PATH',  DATA_PATH . '/template');
define('DATA_IMG_PATH',  DATA_PATH . '/images');

define('UPLOAD_PATH',   ASSETS_PATH . '/upload');
define('TEMP_PATH',     ASSETS_PATH . '/temp');

define('USER_TYPE_WEBMASTER', '101');
define('USER_TYPE_ADMIN',  100);
define('USER_TYPE_COMMON', 200);

define('USER_STATUS_ACTIVE',     1);    // Active Account
define('USER_STATUS_PENDING',   -1);    // Require to Verify
define('USER_STATUS_UNPAID',    -3);    // Require to Pay
define('USER_STATUS_INACTIVE',  -4);    // Closed Account

define('ENGLISH', 'english');

define('SESSION_KEY_USER', 'soccer_bet_user');
define('SESSION_KEY_LANG', 'soccer_bet_language');

define('SESSION_CAPTCHA_WORD', 'soccer_bet_captcha_word');
define('SESSION_CAPTCHA_FILE', 'soccer_bet_captcha_img_name');

define('DEFAULT_PORTFOLIO', 1);

///////////////////////////////////////////////////////////
///  Menu
///////////////////////////////////////////////////////////
define('MENU_TOP_DASHBOARD', 100);

define('MENU_TOP_MATCHES', 200);

define('MENU_TOP_MANAGE', 300);
define('MENU_TOP_MANAGE_SEASON', 301);
define('MENU_TOP_MANAGE_COUNTRY', 302);
define('MENU_TOP_MANAGE_LEAGUE', 303);
define('MENU_TOP_MANAGE_CLUB', 304);

define('MENU_TOP_USER', 1000);
define('MENU_TOP_USER_SETTING', 1001);
define('MENU_TOP_USER_LIST', 1002);

///////////////////////////////////////////////////////////
///  Command
///////////////////////////////////////////////////////////

define('CMD_SCRAPER_BASE_LEAGUES', 'casperjs ' . 'fetch_leagues.js ');
define('CMD_SCRAPER_BASE_CLUBS', 'casperjs ' . 'fetch_clubs.js ');

define('CMD_SCRAPER_MATCH_ODDSPORTAL', 'casperjs ' . 'match_oddsportal.js ');
define('CMD_SCRAPER_MATCH_SOCCERVISTA', 'casperjs ' . 'match_soccervista.js ');

define('CMD_SCRAPER_PREDICTZ',  'casperjs ' . 'predictz.js ');
define('CMD_SCRAPER_WINDRAWWIN','casperjs ' . 'windrawwin.js ');
define('CMD_SCRAPER_SOCCERWAY', 'casperjs ' . 'soccerway.js ');
define('CMD_SCRAPER_TEAMS_INFO', 'casperjs ' . 'soccerway_match.js ');
define('CMD_SCRAPER_TEAMS_RANKS', 'casperjs ' . 'soccerway_ranks.js ');
define('CMD_SCRAPER_REFEREE_LIST', 'casperjs ' . 'soccerbase_list.js ');
define('CMD_SCRAPER_REFEREE_DETAIL', 'casperjs ' . 'soccerbase_detail.js ');


///////////////////////////////////////////////////////////
///  Other Constants
///////////////////////////////////////////////////////////

define('SCRAPER_PATH', ROOT_PATH . "/scraper/casperjs");
define('MIN_ODDS_VALUE', 2.0);