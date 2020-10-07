<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 5/2/2017
 * Time: 10:49 PM
 */

define('LIVE_SERVER', false);

define('ROOT_PATH', dirname(__DIR__));

define('ASSETS_PATH', dirname(ROOT_PATH) . "/assets");
define('DATA_PATH',     ASSETS_PATH.'/data');
define('XLS_TPL_PATH',  DATA_PATH . '/template');
define('DATA_IMG_PATH',  DATA_PATH . '/images');

define('UPLOAD_PATH',   ASSETS_PATH . '/upload');
define('TEMP_PATH',     ASSETS_PATH . '/temp');


// Error Codes, Messages.
if(!defined('RES_C_SUCCESS')) {
    define('RES_C_SUCCESS', '0');
}

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

define('MIN_ODDS_VALUE', 2.0);


require_once "Functions.php";
require_once "vendor/autoload.php";

