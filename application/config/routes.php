<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller']= 'C_Home/index';
$route['index']         = 'C_Home/index';
$route['home/index']    = 'C_Home/index';

$route['user/index']        = 'C_User/index';
$route['user/login']        = 'C_User/index';
$route['login']             = 'C_User/index';
$route['user/_login']       = 'C_User/ajax_login';
$route['_login']            = 'C_User/ajax_login';
$route['user/logout']       = 'C_User/logout';
$route['logout']            = 'C_User/logout';
$route['user/_logout']      = 'C_User/ajax_logout';
$route['_logout']           = 'C_User/ajax_logout';
$route['user/register']     = 'C_User/page_register';
$route['user/x_register']   = 'C_User/x_register';
$route['user/x_get_captcha']= 'C_User/x_get_captcha';
$route['user/setting']      = 'C_User/page_setting';
$route['user/x_update_info']= 'C_User/x_update_info';
$route['user/x_deactivate'] = 'C_User/x_deactivate';
$route['user/x_check_login']= 'C_User/x_check_login';

$route['admin/index']           = 'C_Admin/index';
$route['admin/users']           = 'C_Admin/page_users';
$route['admin/x_users']         = 'C_Admin/x_users';
$route['admin/x_upt_pwd']       = 'C_Admin/x_upt_pwd';
$route['admin/x_activate']      = 'C_Admin/x_activate';
$route['admin/x_deactivate']    = 'C_Admin/x_deactivate';

$route['common/download']       = 'C_Super/download';
$route['change_lang']   		= 'C_Super/change_lang';

///////////////////////////////////////////////////////
/// ERRORS
///////////////////////////////////////////////////////
$route['404_override']  = '';
$route['errors/page_404']    = 'C_Error/page_404';
$route['errors/page_error']  = 'C_Error/page_error';
$route['errors/_error_add']  = 'C_Error/ajax_add_error';

$route['translate_uri_dashes'] = FALSE;


///////////////////////////////////////////////////////
/// Dashboard
///////////////////////////////////////////////////////
$route['home']                  = 'C_Home/index';
$route['home/index']            = 'C_Home/index';


///////////////////////////////////////////////////////
/// Matches
///////////////////////////////////////////////////////
$route['match']                     = 'C_Match/index';
$route['match/index']               = 'C_Match/index';
$route['match/x_list_soccervista']  = 'C_Match/x_list_soccervista';
$route['match/x_list_oddsportal']   = 'C_Match/x_list_oddsportal';
$route['match/x_list_qualified']    = 'C_Match/x_list_qualified';
$route['match/x_export_qualified']  = 'C_Match/x_export_qualified';
$route['match/x_list_analyzed']     = 'C_Match/x_list_analyzed';
$route['match/x_export_analyzed']   = 'C_Match/x_export_analyzed';

///////////////////////////////////////////////////////
/// Matches
///////////////////////////////////////////////////////
$route['manage']                    = 'C_Manage/index';
$route['manage/index']              = 'C_Manage/index';

$route['manage/season']             = 'C_Manage/page_season';
//$route['manage/x_season_add']       = 'C_Manage/x_season_add';
//$route['manage/x_season_del']       = 'C_Manage/x_season_del';
$route['manage/x_season_select']    = 'C_Manage/x_season_select';

$route['manage/country']            = 'C_Manage/page_country';
$route['manage/x_country_list']     = 'C_Manage/x_country_list';
$route['manage/x_country_save']     = 'C_Manage/x_country_save';
$route['manage/x_country_del']      = 'C_Manage/x_country_del';
$route['manage/x_country_import_prev'] = 'C_Manage/x_country_import_prev';

$route['manage/league']             = 'C_Manage/page_league';
$route['manage/x_league_list']      = 'C_Manage/x_league_list';
$route['manage/x_league_save']      = 'C_Manage/x_league_save';
$route['manage/x_league_del']       = 'C_Manage/x_league_del';
$route['manage/x_league_import_prev'] = 'C_Manage/x_league_import_prev';

$route['manage/club']               = 'C_Manage/page_club';
$route['manage/x_club_list']        = 'C_Manage/x_club_list';
$route['manage/x_club_save']         = 'C_Manage/x_club_save';
$route['manage/x_club_del']         = 'C_Manage/x_club_del';


///////////////////////////////////////////////////////
/// Global API
///////////////////////////////////////////////////////
$route['api/x_fetch_matches']       = 'C_GlobalAPI/x_fetch_matches';
$route['api/x_fetch_tips']          = 'C_GlobalAPI/x_fetch_tips';
$route['api/x_analyze_matches']     = 'C_GlobalAPI/x_analyze_matches';
