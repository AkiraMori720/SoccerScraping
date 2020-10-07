<?php
$userInf = $data['user_data'];
?>

<div class="row">
    <div class="col-lg-12">
        <div class="view-header">
            <div class="header-title">
                <h5>
                    <span><i class="fa fa-arrow-right"></i></span>
                    <span>Management > </span>
                    <a href="<?php echo base_url()?>admin/users"><i class="fa fa-user mr-5"></i>My Details</a>
                </h5>
            </div>
        </div>
        <hr>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="panel-tools">
                    <span><i class="fa fa-user mr-5"></i>User Details</span>
                </div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-3"></div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <form id="frmRegister" name="frmRegister" role="form">
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6 col-sm-6">
                                            <div class="form-group full-width">
                                                <label for="txtUserName">User Name<sup class="mask-tip">*</sup>:</label>
                                                <div class="form-control disabled"><?php echo $userInf['user_name']?></div>
                                            </div>
                                            <div class="form-group full-width has-feedback">
                                                <label for="txtCurPwd">Current Password<sup class="mask-tip">*</sup>:</label>
                                                <input type="password" class="form-control"
                                                       id="txtCurPwd" name="txtCurPwd"
                                                       minlength="3" maxlength="16" required
                                                       data-required-error="Please input current password."
                                                       data-pattern-error="Password length might be 3 ~ 12 letters." />
                                                <div class="help-block with-errors small"></div>
                                            </div>
                                            <div class="form-group full-width has-feedback">
                                                <label for="txtPassword">New Password<sup class="mask-tip">*</sup>:</label>
                                                <input type="password" class="form-control"
                                                       id="txtPassword" name="txtPassword"
                                                       minlength="3" maxlength="16"
                                                       data-error="Password length might be 3 ~ 12 letters."/>
                                                <div class="help-block with-errors small"></div>
                                            </div>
                                            <div class="form-group full-width has-feedback">
                                                <label for="txtRepeatPwd">Confirm Password<sup class="mask-tip">*</sup>:</label>
                                                <input type="password" class="form-control"
                                                       id="txtRepeatPwd" name="txtRepeatPwd" data-match="#txtPassword"
                                                       data-match-error="Does not match confirm password!"
                                                       data-error="Password length might be 3 ~ 12 letters."
                                                       minlength="3" maxlength="16">
                                                <div class="help-block with-errors small"></div>
                                            </div>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-sm-6">
                                            <div class="form-group full-width has-feedback">
                                                <label for="txtRealName">Full name<sup class="mask-tip">*</sup>:</label>
                                                <input type="text" class="form-control" value="<?php echo $userInf['real_name']?>"
                                                       id="txtRealName" name="txtRealName" required minlength="1"
                                                       data-required-error="Please input full name!">
                                                <div class="help-block with-errors small"></div>
                                            </div>
                                            <div class="form-group full-width has-feedback">
                                                <label for="txtEmail">EMail<sup class="mask-tip">*</sup>:</label>
                                                <input type="email" class="form-control" value="<?php echo $userInf['email']?>"
                                                       id="txtEmail" name="txtEmail" required data-type="email"
                                                       data-required-error="Please input email address!"
                                                       data-type-error="Invalid email address!">
                                                <div class="help-block with-errors small"></div>
                                            </div>
                                            <!--
                                            <div class="form-group full-width has-feedback">
                                                <label for="txtPhone">联系电话<sup class="mask-tip">*</sup>:</label>
                                                <input type="text" class="form-control" value="<?php echo $userInf['phone']?>"
                                                       id="txtPhone" name="txtPhone" required pattern="^1[1-9]{1}[0-9]{9}"
                                                       data-required-error="联系电话不为空!"
                                                       data-pattern-error="错误的联系电话!">
                                                <div class="help-block with-errors small"></div>
                                            </div>
                                            <div class="form-group full-width has-feedback">
                                                <label for="txtQQ">联系QQ<sup class="mask-tip">*</sup>:</label>
                                                <input type="text" class="form-control" required value="<?php echo $userInf['qq']?>"
                                                       id="txtQQ" name="txtQQ" pattern="[1-9]{1}[0-9]{4,9}"
                                                       data-required-error="联系QQ不为空!"
                                                       data-pattern-error="错误的联系QQ!">
                                                <div class="help-block with-errors small"></div>
                                            </div>
                                            -->
                                            <div class="form-group full-width has-feedback">
                                                <div class="row">
                                                    <div class="col-xs-6">
                                                        <label for="txtCaptcha">Verify Code<sup class="mask-tip">*</sup>:</label>
                                                        <input id="txtCaptcha" name="txtCaptcha" type="text" class="form-control"
                                                               autocomplete="off" required pattern="[A-Za-z0-9]{4}"
                                                               data-required-error="Please input verify code!"
                                                               data-pattern-error="Invalid verify code!"/>
                                                    </div>
                                                    <div class="col-xs-6">
                                                        <div class="captcha-image"></div>
                                                    </div>
                                                </div>
                                                <div class="help-block with-errors small"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-20 no-margin"><div class="col-md-12 col-xs-12 split"></div></div>
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <?php
                                            if($curUserType != USER_TYPE_ADMIN){?>
                                            <button type="button" class="btn btn-danger pull-left btn-close"><i class="fa fa-close mr-5"></i>Close Account</button>
                                            <?php }?>
                                            <button type="submit" class="btn btn-success pull-right btn-update"><i class="fa fa-save mr-5"></i>Update Account</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>


