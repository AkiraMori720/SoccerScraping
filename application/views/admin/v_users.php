<div class="row">
    <div class="col-lg-12">
        <div class="view-header">
            <div class="header-title">
                <h5>
                    <span><i class="fa fa-arrow-right"></i></span>
                    <span>Management > </span>
                    <a href="<?php echo base_url()?>admin/users"><i class="fa fa-gears mr-5"></i>User Management</a>
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
                    <span><i class="fa fa-user mr-5"></i>Registered Users</span>
                </div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12">
                        <table id="tableList" class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th class="cell-style">No</th>
                                <th class="cell-style">ID</th>
                                <th class="cell-style">User Name</th>
                                <th class="cell-style">Type</th>
                                <th class="cell-style">Full Name</th>
                                <th class="cell-style">EMail</th>
                                <th class="cell-style">Status</th>
                                <th class="cell-style">Created At</th>
                                <th class="cell-style">Action</th>
                            </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="dialog-upt-pwd" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <form class="frmPassword" method="post" role="form">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><span class="fa fa-key mr-5"></span>Change Password</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-2"></div>
                        <div class="col-md-8">
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
                        <div class="col-md-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn-save">Save</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>