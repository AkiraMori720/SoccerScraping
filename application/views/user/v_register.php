<div class="row">
    <div class="col-md-3"></div>
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12  mb-20">
                <h3 class="logo"><img src="<?php echo base_url("/assets/img/logo.png")?>"/></h3>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12 login-wrapper">
                <div class="inbox">
                    <form id="frmRegister" name="frmRegister" role="form">
                        <div class="row"><span class="col-md-12 col-xs-12 align-center mb-25 ft-size-20"><i class="fa fa-user mr-10"></i>User Registration</span></div>
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <div class="form-group full-width has-feedback">
                                    <label for="txtUserName">User Name<sup class="mask-tip">*</sup>:</label>
                                    <input type="text" class="form-control"
                                           id="txtUserName" name="txtUserName" placeholder="User Name"
                                           minlength="2" required pattern="[a-zA-Z0-9_]{2,15}"
                                           data-required-error="Please input user name!"
                                           data-pattern-error="User name's length might be 2 ~ 12 letters.">
                                    <div class="help-block with-errors small"></div>
                                </div>
                                <div class="form-group full-width has-feedback">
                                    <label for="txtPassword">Password<sup class="mask-tip">*</sup>:</label>
                                    <input type="password" class="form-control"
                                           id="txtPassword" name="txtPassword"
                                           minlength="3" maxlength="16" required
                                           data-required-error="Please input password!"
                                           data-error="Password length might be 3 ~ 12 letters." />
                                    <div class="help-block with-errors small"></div>
                                </div>
                                <div class="form-group full-width has-feedback">
                                    <label for="txtRepeatPwd">Confirm Password<sup class="mask-tip">*</sup>:</label>
                                    <input type="password" class="form-control"
                                           id="txtRepeatPwd" name="txtRepeatPwd" data-match="#txtPassword"
                                           data-required-error="Please confirm your password!"
                                           data-match-error="Password does not match!"
                                           data-error="Password length might be 3 ~ 12 letters."
                                           minlength="3" maxlength="16" required>
                                    <div class="help-block with-errors small"></div>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <div class="form-group full-width has-feedback">
                                    <label for="txtEmail">EMail<sup class="mask-tip">*</sup>:</label>
                                    <input type="email" class="form-control"
                                           id="txtEmail" name="txtEmail" required data-type="email"
                                           data-required-error="Please input email!"
                                           data-type-error="Invalid Email address!">
                                    <div class="help-block with-errors small"></div>
                                </div>
                                <div class="form-group full-width has-feedback">
                                    <label for="txtRealName">Full Name<sup class="mask-tip">*</sup>:</label>
                                    <input type="text" class="form-control"
                                           id="txtRealName" name="txtRealName" required minlength="1"
                                           data-required-error="Please input full name!">
                                    <div class="help-block with-errors small"></div>
                                </div>
                                <!--
                                <div class="form-group full-width has-feedback">
                                    <label for="txtPhone">联系电话<sup class="mask-tip">*</sup>:</label>
                                    <input type="text" class="form-control"
                                           id="txtPhone" name="txtPhone" required pattern="^1[1-9]{1}[0-9]{9}"
                                           data-required-error="联系电话不为空!"
                                           data-pattern-error="错误的联系电话!">
                                    <div class="help-block with-errors small"></div>
                                </div>
                                <div class="form-group full-width has-feedback">
                                    <label for="txtQQ">联系QQ<sup class="mask-tip">*</sup>:</label>
                                    <input type="text" class="form-control" required
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
                                <a class="btn btn-primary pull-left" href='<?php echo base_url('user/login')?>'>Login</a>
                                <button type="submit" class="btn btn-danger pull-right btn-register">Register</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3"></div>
</div>
