<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once ROOT_PATH . DIRECTORY_SEPARATOR . 'application/models/Response.php';

if (strtolower(filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest') {
    $response = new Response();

    $response->m_Code = RES_C_ERR_404;
    $response->m_Msg  = $message;
    echo $response->toJSON();

    die();
}


?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
    <link href="<?php echo BASE_URL .(CSS_PATH . "/font-awesome/css/font-awesome.css")?>" rel="stylesheet"/>
    <link href="<?php echo BASE_URL .( LIB_PATH . "/bootstrap/css/bootstrap.css")?>" rel="stylesheet" media="screen"/>

    <script src="<?php echo BASE_URL .(LIB_PATH . '/jQuery/jquery-1.12.4.min.js' ); ?>" type="text/javascript"></script>
    <script src="<?php echo BASE_URL .( LIB_PATH . "/js.cookie.js" ); ?>" type="text/javascript"></script>
</head>
<body>
	<div id="container">

	</div>
</body>
</html>


<script>
    $(document).ready(function() {
        window.location.href = '<?php echo BASE_URL .('errors/page_404')?>';
    });
</script>