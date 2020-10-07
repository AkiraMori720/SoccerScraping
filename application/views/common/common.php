<?php $viewPath = dirname(__DIR__); ?>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?php echo lang(LANG_APP_TITLE) ?></title>
    <link rel="shortcut icon" href="<?php echo base_url(IMG_PATH."/logo.png")?>" />

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <link href="<?php echo base_url(LIB_PATH . "/bootstrap/css/bootstrap.css")?>" rel="stylesheet" media="screen"/>
    <link href="<?php echo base_url(LIB_PATH . "/font-awesome/css/font-awesome.min.css")?>" rel="stylesheet" media="screen"/>
    <link href="<?php echo base_url(LIB_PATH . "/datepicker/datepicker3.css")?>" rel="stylesheet"/>
    <link href="<?php echo base_url(LIB_PATH . "/animate.css/animate.min.css")?>" rel="stylesheet"/>
    <?php
    if(isset($config['plug_css'])) {
        foreach($config['plug_css'] as $css) {
            echo "\t<link href=\"".base_url(LIB_PATH . $css) ."\" rel=\"stylesheet\"/>".PHP_EOL;
        }
    }
    ?>

	<link href="<?php echo base_url(CSS_PATH . "/fonts.css")?>" rel="stylesheet"/>
    <link href="<?php echo base_url(CSS_PATH . "/common.css")?>" rel="stylesheet"/>
    <?php
    if(isset($config['other_css'])) {
        foreach($config['other_css'] as $css) {
            echo "<link href=\"".base_url(CSS_PATH.$css."?v=" . time()) . "\" rel=\"stylesheet\"/>".PHP_EOL;
        }
    }
    ?>
</head>

<body>
    <div class="container content">
        <div class="container main-wrapper">
            <?php require_once($viewPath . '/' . "{$view}.php");?>
        </div>
    </div>


<?php require_once($viewPath . '/common/dialogs.php');?>
<?php require_once($viewPath . '/common/language.php');?>

<!-- START @BACK TOP -->
<div id="back-top" class="circle">
    <i class="fa fa-angle-up"></i>
</div>
<!--/ END BACK TOP -->


<script type="text/javascript" src="<?php echo base_url(LIB_PATH . "/jQuery/jquery-1.12.4.min.js"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url(LIB_PATH . "/js.cookie.js"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url(LIB_PATH . "/sprintf.min.js"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url(LIB_PATH . "/jquery-ui/jquery.blockui.js"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url(LIB_PATH . "/bootstrap/js/bootstrap.js"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url(LIB_PATH . "/bootstrap/js/validator.js"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url(LIB_PATH . "/datepicker/bootstrap-datepicker.js"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url(LIB_PATH . "/noty/noty.min.js"); ?>"></script>
<?php
if(isset($config['plug_js'])) {
    foreach($config['plug_js'] as $js) {
        echo "\t<script src=\"".base_url(). LIB_PATH . $js."\" type=\"text/javascript\"></script>".PHP_EOL;
    }
}
?>

<script type="text/javascript">
    var base_url = '<?php echo base_url()?>';
    var RESULT_CODE_SUCCESS = '<?php echo RES_C_SUCCESS?>';
    var RES_C_INVALID = '<?php echo RES_C_INVALID?>';
    var RES_C_UNKNOWN = '<?php echo RES_C_UNKNOWN?>';
    var RES_C_REQUIRE_LOGIN = '<?php echo RES_C_REQUIRE_LOGIN?>';
    var UI_LANGUAGE = '<?php echo getCurrentLang($this->session) ?>';
</script>

<script type="text/javascript" src="<?php echo base_url(JS_PATH . "/notify.js"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url(JS_PATH . "/app.js"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url(JS_PATH . "/action.js"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url(JS_PATH . "/dialog.js"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url(JS_PATH . "/util.js"); ?>"></script>

<?php
if(isset($config['other_js'])) {
    foreach($config['other_js'] as $js) {
        echo "\t<script type=\"text/javascript\" src=\"".base_url(JS_PATH.$js."?v=" . time()) . "\"></script>".PHP_EOL;
    }
}
?>

</body>
</html>
