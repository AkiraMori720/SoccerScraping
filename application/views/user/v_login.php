<div class="row">
    <div class="col-md-3"></div>
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12 logo mb-20">
                <h3 class="logo"><img src="<?php echo base_url('/assets/img/logo.png')?>"/></h3>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12 login-wrapper">
                <div class="row inbox">
                    <form id="frmLogin" name="frmLogin" class="form-horizontal mb-0" role="form">
                        <div class="row"><span class="col-md-12 col-xs-12 align-center mb-25 ft-size-20">Login</span></div>
                        <div class="form-group has-feedback">
                            <div class="col-md-12 col-xs-12">
                                <div class="inner-addon left-addon">
                                    <i class="fa fa-user fa-lg"></i>
                                    <input id="txtUserName" name="txtUserName" type="text" class="form-control"
                                           placeholder="UserName" aria-label="UserName" aria-describedby="basic-addon1"
                                           value=""
                                           required
                                           pattern="[a-zA-Z0-9_]{2,20}"
                                           data-required-error="UserName can't be empty!"
                                           data-pattern-error="Invalid username!"
                                    />
                                </div>
                            </div>
                            <div class="col-md-12 col-xs-12 help-block with-errors"></div>
                        </div>
                        <div class="form-group has-feedback">
                            <div class="col-md-12 col-xs-12">
                                <div class="inner-addon left-addon">
                                    <i class="fa fa-lock fa-lg"></i>
                                    <input id="txtPassword" name="txtPassword" type="password" class="form-control"
                                           placeholder="Password" aria-label="Password" aria-describedby="basic-addon1"
                                           value="" required
                                           data-required-error="Password can't be empty!"
                                    />
                                </div>
                            </div>
                            <div class="col-md-12 col-xs-12 help-block with-errors"></div>
                        </div>
                        <div class="row mt-20">
                            <div class="col-md-7 col-xs-7">
                                <div class="form-check pt-5">
                                    <label class="form-check-label cursor-hand ft-normal">
                                        <input id="txtRemember" name="txtRemember" type="checkbox" class="form-check-input " value="1" checked>
                                        Keep login for 7 days
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-5 col-xs-5">
                                <button type="submit" name="btnLogin" id="btnLogin" class="btn btn-success cursor-hand pull-right">Login&nbsp;<i class="fa fa-sign-in"></i></button>
                            </div>
                        </div>
                    </form>
                    <div class="row mb-20 no-margin"><div class="col-md-12 col-xs-12 split"></div></div>
                    <div class="row mb-20 pl-40 pt-5">
                        <div class="col-md-12 col-xs-12">
                            <div class="mb-15">
                                <label class="ft-normal">To create a new account, please click</label>
                                <a href="<?php echo base_url('user/register')?>">Register</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3"></div>
</div>

<div class="row forgot-register">

</div>

