<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 11/20/2017
 * Time: 8:55 PM
 */

$actURL = isset($data['act_url']) ? $data['act_url'] : '';
$errCode= isset($data['err_code']) ? $data['err_code'] : '';
$errMsg = isset($data['err_msg']) ? $data['err_msg'] : '';
?>

<div class="row">
    <div class="col-md-2"></div>
    <div class="col-md-8">
        <div class="error-wrapper">
            <h1><?php echo lang(LANG_C_PAGE_ERR_TITLE) ?></h1>
            <br>
            <h3><?php echo lang(LANG_C_PAGE_ERR_MSG1) ?></h3>
            <h4><?php echo lang(LANG_C_PAGE_ERR_MSG2) ?></h4>

            <br>
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <form id="frmError" class="form-horizontal" method="post">
                        <input type="hidden" readonly class="form-control input-sm" id="txtErrorCode" name="txtErrorCode" value="<?php echo $errCode ?>">
                        <input type="hidden" readonly class="form-control input-sm" id="txtActionURL" name="txtActionURL" value="<?php echo $actURL ?>">

                        <div class="form-group">
                            <label for="txtErrorMsg"><?php echo lang(LANG_C_PAGE_ERR_CODE) ?> <?php echo $errCode ?></label>
                            <div class="form-control input-sm long-text" id="txtErrorMsg" readonly>
                                <?php echo $errMsg ?>
                            </div>
                        </div>
                    </form>
                    <button class="btn btn-sm btn-info btn-go pull-left"><?php echo lang(LANG_C_PAGE_ERR_GOMAIN) ?></button>
                    <button class="btn btn-sm btn-danger btn-report pull-right"><?php echo lang(LANG_C_PAGE_ERR_SUBMIT) ?></button>
                </div>
                <div class="col-md-2"></div>
            </div>
        </div>
    </div>
    <div class="col-md-2"></div>
</div>


<script>
    $(document).ready(function() {
        $('.btn-report').click(function(e) {
            e.preventDefault();
            var data = {
                errorCode   : '<?php echo $errCode ?>',
                errorMsg    : "<?php echo $errMsg ?>",
                actionUrl   : "<?php echo $actURL ?>",
                postData    : ''
            };

            postError(data);
        });

        $('.btn-go').click(function(e) {
            e.preventDefault();
            window.location.href = '<?php echo base_url("index")?>';
        });
    });
</script>