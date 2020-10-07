<div id="dialog-prompt" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><span class="fa fa-exclamation-circle"></span><?php echo lang(LANG_C_DLG_ALERT_TITLE) ?></h4>
            </div>
            <div class="modal-body">
                <div class="row"><p class="col-md-12"></p></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo lang(LANG_C_DLG_BTN_CLOSE) ?></button>
            </div>
        </div>
    </div>
</div>

<div id="dialog-confirm" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><span class="fa fa-question-circle"></span><?php echo lang(LANG_C_DLG_CONF_TITLE) ?></h4>
            </div>
            <div class="modal-body">
                <div class="row"><p class="col-lg-12"></p></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang(LANG_C_DLG_BTN_CANCEL) ?></button>
                <button type="button" class="btn btn-primary btn-confirm" data-dismiss="modal"><?php echo lang(LANG_C_DLG_BTN_YES) ?></button>
            </div>
        </div>
    </div>
</div>

<div id="dialog-error" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><span class="fa fa-exclamation-triangle"></span><?php echo lang(LANG_C_DLG_ALERT_TITLE) ?></h4>
            </div>
            <div class="modal-body">
                <div class="row"><h5 class="col-lg-12 error-title"><?php echo lang(RES_C_UNKNOWN) ?></h5></div>
                <div class="row" style="margin: 0;"><div class="col-lg-12 error-content"></div></div>
                <br>
                <div class="row">
                    <div class="col-lg-12"><?php echo lang(LANG_C_MSG_REPORT_ERR) ?></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-submit-issue" data-dismiss="modal"><?php echo lang(LANG_C_DLG_BTN_SEND) ?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang(LANG_C_DLG_BTN_CANCEL) ?></button>
            </div>
        </div>
    </div>
</div>

<div id="dialog-language" class="modal fade">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title"><span class="fa fa-language"></span>&nbsp;&nbsp;<?php echo lang(LANG_C_DLG_LANG_TITLE)?></h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-lg-1"></div>
					<div class="col-lg-10" style="padding: 10px 30px;">
						<form id="frmLang" name="frmLang" action="<?php echo base_url()?>change_lang" method="post">
							<input type="hidden" id="txtCurURL" name="txtCurURL" value="">
							<div class="radio radio-active">
								<input name="optLang" id="optLangEN" value="<?php echo ENGLISH?>" type="radio">

								<label for="optLangEN">
									<span><i class="flag-icon flag-icon-us" aria-hidden="true"></i>&nbsp;<?php echo lang(LANG_C_DLG_LANG_EN)?></span>
								</label>
							</div>
<!--							<div class="radio">-->
<!--								<input name="optLang" id="optLangCN" value="--><?php //echo CHINESE?><!--" type="radio">-->
<!--								<label for="optLangCN">-->
<!--									<span><i class="flag-icon flag-icon-cn" aria-hidden="true"></i> --><?php //echo lang(LANG_C_DLG_LANG_CN)?><!--</span>-->
<!--								</label>-->
<!--							</div>-->
						</form>
					</div>
					<div class="col-lg-1"></div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang(LANG_C_DLG_BTN_CLOSE)?></button>
				<button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo lang(LANG_C_DLG_BTN_CHANGE)?></button>
			</div>
		</div>
	</div>
</div>
