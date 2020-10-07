<div class="row">
    <div class="col-lg-12">
        <div class="view-header">
            <div class="header-title">
                <h5>
                    <span><i class="fa fa-gear"></i></span>
                    <span>Management > </span>
                    <a href="<?php echo base_url('manage/country')?>"><i class="fa fa-flag mr-5"></i>Countries</a>

                    <button class="btn btn-sm btn-primary btnAddNew pull-right"><i class="fa fa-plus mr-5"></i>Add New Country</button>
                    <button class="btn btn-sm btn-warning btnAddPrev pull-right mr-10"><i class="fa fa-plus mr-5"></i>Import from previous season</button>
                </h5>
            </div>
        </div>
        <hr>
    </div>
</div>

<div class="row mb-10">
    <div class="col-md-10 col-md-offset-1">
        <div class="row">
            <div class="col-sm-3 col-xs-4">
                <div class="form-group has-feedback">
                    <label for="optSeason">Season<sup class="mask-tip">*</sup>:</label>
                    <select class="form-control full-width" required
                            id="optSeason" name="season"
                            data-required-error="Season is empty!">
                        <?php
                        $seasons = $data['seasons'];
                        foreach ($seasons as $season) {
                            $seasonName = $season['season'];
                            $seasonStatus = $season['status'];
                            ?>
                            <option value="<?php echo $seasonName?>" <?php echo $seasonStatus=='active'? 'selected' : ''?>><?php echo $seasonName?></option>
                            <?php
                        }
                        ?>
                    </select>
                    <div class="help-block with-errors small"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-10">
    <div class="col-md-10 col-md-offset-1">
        <table id="tableData" class="table table-striped table-hover" width="100%">
            <thead>
            <tr>
                <th class="cell-style">No</th>
                <th class="cell-style">Country</th>
                <th class="cell-style">ISO 2Code</th>
                <th class="cell-style">Oddsportal</th>
                <th class="cell-style">Soccervista</th>
                <th class="cell-style">Soccerway</th>
                <th class="cell-style">Predictz</th>
                <th class="cell-style">Windrawwin</th>
                <th class="cell-style">Soccerbase</th>
                <th class="cell-style">Action</th>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

<div id="dialog-country" class="modal fade">
    <div class="modal-dialog modal-md" tabindex="-1" role="dialog">
        <div class="modal-content">
            <form id="frmData" name="frmData" role="form">
                <input type="hidden" id="txtCountryID" name="id" value="">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;
                    </button>
                    <h4 class="modal-title"><i class="fa fa-flag mr-5"></i>Add New</h4>
                </div>
                <div class="modal-body pl-20 pr-20">
                    <div class="row">
                        <div class="col-md-12 mb-10">
                            <div class="row">
                                <div class="col-sm-7">
                                    <div class="form-group has-feedback">
                                        <label for="txtCountry">Country<sup class="mask-tip">*</sup>:</label>
                                        <input type="text" class="form-control"
                                               id="txtCountry" name="country" required
                                               data-required-error="Country is empty!"
                                               data-error="Invalid Country value!">
                                        <div class="help-block with-errors small"></div>
                                    </div>
                                </div>
                                <div class="col-sm-5">
                                    <div class="form-group has-feedback">
                                        <label for="txtIso2">ISO 2 Code<sup class="mask-tip">*</sup>:</label>
                                        <input type="text" class="form-control"
                                               id="txtIso2" name="iso2_code" required maxlength="2"
                                               data-required-error="ISO code is empty!"
                                               data-error="Invalid ISO code value!">
                                        <div class="help-block with-errors small"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group has-feedback">
                                        <label for="txtOddsportal">Oddsportal<sup class="mask-tip">*</sup>:</label>
                                        <input type="text" class="form-control"
                                               id="txtOddsportal" name="oddsportal" required
                                               data-required-error="Oddsportal is empty!"
                                               data-error="Invalid Oddsportal value!">
                                        <div class="help-block with-errors small"></div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group has-feedback">
                                        <label for="txtSoccerVista">Soccervista:</label>
                                        <input type="text" class="form-control"
                                               id="txtSoccerVista" name="soccervista"
                                               data-required-error="Soccervista is empty!"
                                               data-error="Invalid Soccervista value!">
                                        <div class="help-block with-errors small"></div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group has-feedback">
                                        <label for="txtSoccerWay">Soccerway:</label>
                                        <input type="text" class="form-control"
                                               id="txtSoccerWay" name="soccerway"
                                               data-required-error="Soccerway is empty!"
                                               data-error="Invalid Soccerway value!">
                                        <div class="help-block with-errors small"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group has-feedback">
                                        <label for="txtPredictz">Predictz:</label>
                                        <input type="text" class="form-control"
                                               id="txtPredictz" name="predictz"
                                               data-required-error="Predictz is empty!"
                                               data-error="Invalid Predictz value!">
                                        <div class="help-block with-errors small"></div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group has-feedback">
                                        <label for="txtWindrawwin">Windrawwin:</label>
                                        <input type="text" class="form-control"
                                               id="txtWindrawwin" name="windrawwin"
                                               data-required-error="Windrawwin is empty!"
                                               data-error="Invalid Windrawwin value!">
                                        <div class="help-block with-errors small"></div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group has-feedback">
                                        <label for="txtSoccerBase">Soccerbase:</label>
                                        <input type="text" class="form-control"
                                               id="txtSoccerBase" name="soccerbase"
                                               data-required-error="Soccerbase is empty!"
                                               data-error="Invalid Soccerbase value!">
                                        <div class="help-block with-errors small"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12"><p class="error-msg"></p></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-primary btnSave"><i class="fa fa-save mr-5"></i>Save</button>
                    <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>