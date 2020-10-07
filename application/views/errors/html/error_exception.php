<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once ROOT_PATH . DIRECTORY_SEPARATOR . 'application/models/Response.php';

if (strtolower(filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest') {
    $response = new Response();

    $response->m_Code = RES_C_ERR_EXCEPTION;
    $response->m_Msg  = $message."<br>".$exception->getFile()."<br>".$exception->getLine();
    echo $response->toJSON();

    die();
}

?>
<script src="<?php echo BASE_URL . LIB_PATH . '/jQuery/jquery-1.12.4.min.js'; ?>" type="text/javascript"></script>
<div id="container" style="display:none">
    <form id="frmError" method="post" action="<?php echo BASE_URL . "errors/page_error"?>">
        <input id="txtErrorCode" name="txtErrorCode" value="<?php echo RES_C_ERR_EXCEPTION?>">
        <textarea id="txtErrorMsg" name="txtErrorMsg"><?php echo $message."<br>".$exception->getFile()."<br>".$exception->getLine() ?></textarea>
    </form>
</div>

<script>
    $(document).ready(function() {
        // $('#frmError').submit();
    });
</script>