<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once ROOT_PATH . DIRECTORY_SEPARATOR . 'application/models/Response.php';

if (strtolower(filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest') {
    $response = new Response();

    $response->m_Code = RES_C_ERR_GENERAL;
    $response->m_Msg  = $message;
    echo $response->toJSON();

    die();
}

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
    <script src="<?php echo base_url(LIB_PATH . '/jQuery/jquery-1.12.4.min.js' ); ?>" type="text/javascript"></script>
</head>
<body>
<div id="container" style="display:none">
    <form id="frmError" method="post" action="<?php echo base_url("errors/page_error")?>">
        <input id="txtErrorCode" name="txtErrorCode" value="<?php echo RES_C_ERR_GENERAL?>">
        <textarea id="txtErrorMsg" name="txtErrorMsg"><?php echo $message ?></textarea>
    </form>
</div>
</body>
</html>

<script>
    $(document).ready(function() {
        // $('#frmError').submit();
    });
</script>