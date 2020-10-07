<?php
$_gCurLanguage_ = getCurrentLang($this->session);
$_gCurLangCode_ = getLangCode($_gCurLanguage_);
$_gCurUserData_ = null;

$viewPath = dirname(__DIR__);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?php echo lang(LANG_APP_TITLE) ?></title>
    <link rel="shortcut icon" href="<?php echo base_url(IMG_PATH."/logo.png")?>" />

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="<?php echo base_url(LIB_PATH . "/font-awesome/css/font-awesome.min.css")?>"/>
	<link rel="stylesheet" href="<?php echo base_url(LIB_PATH . "/flag-icon/css/flag-icon.min.css")?>"/>
    <link rel="stylesheet" href="<?php echo base_url(LIB_PATH . "/bootstrap/css/bootstrap.css")?>" media="screen"/>
    <link rel="stylesheet" href="<?php echo base_url(LIB_PATH . "/datepicker/datepicker3.css")?>"/>
    <link rel="stylesheet" href="<?php echo base_url(LIB_PATH . "/animate.css/animate.min.css")?>"/>
	<link rel="stylesheet" href="<?php echo base_url(LIB_PATH . "/noty/noty.css")?>"/>
    <?php
    if(isset($config['plug_css'])) {
        foreach($config['plug_css'] as $css) {
            echo "\t<link href=\"".base_url(). LIB_PATH . $css."\" rel=\"stylesheet\"/>".PHP_EOL;
        }
    }
    ?>

    <link rel="stylesheet" href="<?php echo base_url(CSS_PATH . "/common.css?v=" . time())?>"/>
	<link rel="stylesheet" href="<?php echo base_url(CSS_PATH . "/fonts.css?v=" . time())?>"/>
	<link rel="stylesheet" href="<?php echo base_url(CSS_PATH . "/components.css?v=" . time())?>"/>
    <?php
    if(isset($config['other_css'])) {
        foreach($config['other_css'] as $css) {
            echo "\t<link href=\"".base_url(CSS_PATH.$css."?v=" . time()) . "\" rel=\"stylesheet\"/>".PHP_EOL;
        }
    }

    $activeMenu = getValueInArray($data, 'active_menu', '0');
    $curUserID = null;
    $curUserType = null;
    ?>
</head>

<body>

<div class="container content mb-10">
    <nav class="navbar navbar-default navbar-fixed-top">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <div class="navbar-brand cursor-hand">
                    <img class="logo pull-left" src="<?php echo base_url(IMG_PATH . "/logo.png")?>" title="Profile">
					<span class="title">&nbsp<?php echo lang(LANG_APP_TITLE) ?></span>
                </div>
            </div>
            <div class="collapse navbar-collapse">
                <?php
                if(is_Login($this->session)) {
                    $_gCurUserData_ = $this->session->userdata(SESSION_KEY_USER);
                    $curUserID  = getUserID($this->session);
                    $curUserType= getUserType($this->session);
                    ?>
                <ul class="nav navbar-nav nav-main-menus">
                    <li class="nav-item <?php echo is_activeMenuGrp($activeMenu, MENU_TOP_DASHBOARD) ?>">
                        <a class="nav-link" href="<?php echo base_url('home/index')?>"><i class="fa fa-home mr-5"></i><?php echo lang(LANG_NAV_HOME)?><span class="sr-only">(current)</span></a>
                    </li>
                    <?php if(isAdmin($_gCurUserData_)){?>
                        <li class="dropdown <?php echo is_activeMenuGrp($activeMenu, MENU_TOP_MANAGE) ?>">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-gear mr-5"></i><?php echo lang(LANG_NAV_MANAGE)?><span class="caret ml-5"></span></a>
                            <ul class="dropdown-menu" role="menu">
                                <li class="m-tab-tip">▲</li>
                                <li class="mnu-seasons <?php echo is_activeMenuItem($activeMenu, MENU_TOP_MANAGE_SEASON) ?>"><a href="<?php echo base_url('manage/season')?>"><i class="fa fa-calendar mr-5"></i><?php echo lang(LANG_NAV_SETTING_SEASON)?></a></li>
                                <li class="mnu-countries <?php echo is_activeMenuItem($activeMenu, MENU_TOP_MANAGE_COUNTRY) ?>"><a href="<?php echo base_url('manage/country')?>"><i class="fa fa-flag mr-5"></i><?php echo lang(LANG_NAV_SETTING_COUNTRY)?></a></li>
                                <li class="mnu-leagues <?php echo is_activeMenuItem($activeMenu, MENU_TOP_MANAGE_LEAGUE) ?>"><a href="<?php echo base_url('manage/league')?>"><i class="fa fa-trophy mr-5"></i><?php echo lang(LANG_NAV_SETTING_LEAGUE)?></a></li>
                                <li class="mnu-clubs <?php echo is_activeMenuItem($activeMenu, MENU_TOP_MANAGE_CLUB) ?>"><a href="<?php echo base_url('manage/club')?>"><i class="fa fa-group mr-5"></i><?php echo lang(LANG_NAV_SETTING_CLUB)?></a></li>
                            </ul>
                        </li>
                        <li class="nav-item <?php echo is_activeMenuGrp($activeMenu, MENU_TOP_MATCHES) ?>">
                            <a class="nav-link" href="<?php echo base_url('match/index')?>"><i class="fa fa-soccer-ball-o mr-5"></i><?php echo lang(LANG_NAV_MATCH)?><span class="sr-only">(current)</span></a>
                        </li>
                    <?php }?>
                </ul>
                <?php }?>
                <ul class="nav navbar-nav navbar-right nav-main-menus">
                     <li class="nav-item nav-lang current-language"><a href="#"><span class="flag-icon flag-icon-<?php echo getCountryCode($_gCurLanguage_)?>"></span></a></li>
                    <?php
                    if(is_Login($this->session)) {
                        ?>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
								<i class="fa fa-user-circle"></i>
								<span class="user-name"><?php echo getUserRealName($this->session)?></span>
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu logout-menu" role="menu">
                                <li class="m-tab-tip">▲</li>
                                <?php if(isAdmin($_gCurUserData_)){?>
                                    <li class="mnu-users <?php echo is_activeMenuItem($activeMenu, MENU_TOP_USER_LIST) ?>"><a href="<?php echo base_url('admin/users')?>"><i class="fa fa-user mr-5"></i><?php echo lang(LANG_NAV_SETTING_USERS)?></a></li>
                                <?php }?>
                                <li class="mnu-setting <?php echo is_activeMenuItem($activeMenu, MENU_TOP_USER_SETTING) ?>"><a href="<?php echo base_url('user/setting')?>"><i class="fa fa-credit-card mr-5"></i><?php echo lang(LANG_NAV_SETTING_PERSONAL)?></a></li>
                                <li>
                                    <a href="<?php echo base_url("logout")?>">
                                        <span class="fa fa-sign-out"></span>
                                        <span><?php echo lang(LANG_NAV_SETTING_LOGOUT) ?></span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <?php
                    }
                    else {?>
                        <li><a href="<?php echo base_url("login")?>"><?php echo lang(LANG_C_LOGIN) ?></a></li>
                        <?php
                    } ?>

                </ul>
            </div>
        </div>
    </nav>

    <div class="container main-wrapper">
    <?php require_once($viewPath . '/' . "{$view}.php");?>
    </div>

</div>

<?php require_once($viewPath . '/common/dialogs.php');?>
<?php require_once($viewPath . '/common/language.php');?>


<!-- START @BACK TOP -->
<!--<div id="back-top" class="circle">-->
<!--    <i class="fa fa-angle-up"></i>-->
<!--</div>-->
<div id="back-top" class="arrow-up">
	<img src="<?php echo base_url('assets/img/up.png')?>" />
</div>
<!--/ END BACK TOP -->

<script type="text/javascript" src="<?php echo base_url(LIB_PATH . '/jQuery/jquery-1.12.4.min.js')?>"></script>
<script type="text/javascript" src="<?php echo base_url(LIB_PATH . '/js.cookie.js')?>"></script>
<script type="text/javascript" src="<?php echo base_url(LIB_PATH . '/sprintf.min.js')?>"></script>
<script type="text/javascript" src="<?php echo base_url(LIB_PATH . '/jquery-ui/jquery.blockui.js')?>"></script>
<script type="text/javascript" src="<?php echo base_url(LIB_PATH . '/bootstrap/js/bootstrap.js')?>"></script>
<script type="text/javascript" src="<?php echo base_url(LIB_PATH . '/datepicker/bootstrap-datepicker.js')?>"></script>
<script type="text/javascript" src="<?php echo base_url(LIB_PATH . '/noty/noty.min.js')?>"></script>

<?php
if(isset($config['plug_js'])) {
    foreach($config['plug_js'] as $js) {
        echo "\t<script src=\"".base_url(LIB_PATH . $js) ."\" type=\"text/javascript\"></script>".PHP_EOL;
    }
}
?>

<script type="text/javascript">
    var base_url = '<?php echo base_url()?>';
    var RESULT_CODE_SUCCESS = '<?php echo RES_C_SUCCESS?>';
    var RES_C_INVALID = '<?php echo RES_C_INVALID?>';
    var RES_C_UNKNOWN = '<?php echo RES_C_UNKNOWN?>';
    var RES_C_REQUIRE_LOGIN = '<?php echo RES_C_REQUIRE_LOGIN?>';
    var UI_LANGUAGE = '<?php echo $_gCurLanguage_ ?>';
	var UI_LANGCODE = '<?php echo $_gCurLangCode_ ?>';
</script>
<script type="text/javascript" src="<?php echo base_url(JS_PATH . "/notify.js"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url(JS_PATH . "/app.js"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url(JS_PATH . "/action.js"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url(JS_PATH . "/dialog.js"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url(JS_PATH . "/util.js"); ?>"></script>

<script type="text/javascript">
    $(document).ready(function(){
        $('.nav .dropdown').hover(
            function(){
                $(this).addClass('open');
            },
            function(){
                $(this).removeClass('open');
            }
        );
    });
</script>

<?php
if(isset($config['other_js'])) {
    foreach($config['other_js'] as $js) {
        echo "\t<script src=\"".base_url(JS_PATH.$js."?v=" . time()) . "\" type=\"text/javascript\"></script>".PHP_EOL;
    }
}
?>

</body>
</html>
