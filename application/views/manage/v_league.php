<div class="row">
    <div class="col-lg-12">
        <div class="view-header">
            <div class="header-title">
                <h5>
                    <span><i class="fa fa-gear"></i></span>
                    <span>Management > </span>
                    <a href="<?php echo base_url('manage/league')?>"><i class="fa fa-trophy mr-5"></i>Leagues</a>

                    <button class="btn btn-sm btn-primary btnAddNew pull-right"><i class="fa fa-plus mr-5"></i>Add New</button>
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
                <th class="cell-style">League</th>
                <th class="cell-style">#Matches</th>
<!--                <th class="cell-style">Oddsportal</th>-->
                <th class="cell-style">Soccervista</th>
                <th class="cell-style">Predictz</th>
                <th class="cell-style">Windrawwin</th>
                <th class="cell-style">Soccerway</th>
                <th class="cell-style">Soccerbase</th>
                <th class="cell-style">Action</th>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

<div id="dialog-league" class="modal fade">
    <div class="modal-dialog modal-lg" tabindex="-1" role="dialog">
        <div class="modal-content">
            <form id="frmData" name="frmData" role="form">
                <input type="hidden" id="txtLeagueID" name="id" value="">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;
                    </button>
                    <h4 class="modal-title"><i class="fa fa-trophy mr-5"></i>Add New</h4>
                </div>
                <div class="modal-body pl-20 pr-20">
                    <div class="row">
                        <div class="col-md-12 mb-10">
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group has-feedback">
                                        <label for="optCountry">Country<sup class="mask-tip">*</sup>:</label>
                                        <select id="optCountry" name="country" required class="form-control"
                                               data-required-error="Country is empty!" readonly=""
                                               data-error="Invalid Country value!">
                                        </select>
                                        <div class="help-block with-errors small"></div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group has-feedback">
                                        <label for="optOddsportal">League<sup class="mask-tip">*</sup>:</label>
                                        <select id="optOddsportal" name="oddsportal" required class="form-control"
                                                data-required-error="League is empty!"
                                                data-error="Invalid League value!">
                                            <option value="">Select</option>
                                        </select>
                                        <div class="help-block with-errors small"></div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group has-feedback">
                                        <label for="txtMatches">#Matches<sup class="mask-tip">*</sup>:</label>
                                        <input type="number" id="txtMatches" name="max_matches" required class="form-control"
                                                data-required-error="#Matches is empty!" value="34"
                                                data-error="Invalid #Matches value!" />
                                        <div class="help-block with-errors small"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group has-feedback">
                                        <label for="optSoccervista">Soccervista:</label>
                                        <select id="optSoccervista" name="soccervista" class="form-control"
                                                data-required-error="Soccervista is empty!"
                                                data-error="Invalid Soccervista value!">
                                            <option value="">Select</option>
                                        </select>
                                        <div class="help-block with-errors small"></div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group has-feedback">
                                        <label for="optSoccerway">Soccerway:</label>
                                        <select id="optSoccerway" name="soccerway" class="form-control"
                                                data-required-error="Soccerway is empty!"
                                                data-error="Invalid Soccerway value!">
                                            <option value="">Select</option>
                                        </select>
                                        <div class="help-block with-errors small"></div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group has-feedback">
                                        <label for="optPredictz">Predictz:</label>
                                        <select id="optPredictz" name="predictz" class="form-control"
                                                data-required-error="Predictz is empty!"
                                                data-error="Invalid Predictz value!">
                                            <option value="">Select</option>
                                        </select>
                                        <div class="help-block with-errors small"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group has-feedback">
                                        <label for="optWindrawwin">Windrawwin:</label>
                                        <select id="optWindrawwin" name="windrawwin" class="form-control"
                                                data-required-error="Windrawwin is empty!"
                                                data-error="Invalid Windrawwin value!">
                                            <option value="">Select</option>
                                        </select>
                                        <div class="help-block with-errors small"></div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group has-feedback">
                                        <label for="optSoccerbase">Soccerbase:</label>
                                        <select id="optSoccerbase" name="soccerbase" class="form-control"
                                                data-required-error="Soccerbase is empty!"
                                                data-error="Invalid Soccerbase value!">
                                            <option value="">Select</option>
                                        </select>
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

<script type="text/javascript">
    let _gCountryList_ = {};
    <?php
    $countries = $data['countries'];
    foreach ($countries as $country) {
    $name = getValueInArray($country, 'country');
    $code = getValueInArray($country, 'iso2_code');
    ?>
    _gCountryList_['<?php echo $name?>'] = {
        code : '<?php echo $code?>',
        icon : '<?php echo strtolower($name)?>'
    };
    <?php
    }
    ?>

    let _gAllLeagues_ = {
        oddsportal : <?php echo json_encode($data['leagues_oddsportal'])?>,
        soccervista : <?php echo json_encode($data['leagues_soccervista'])?>,
        soccerbase : <?php echo json_encode($data['leagues_soccerbase'])?>,
        soccerway : <?php echo json_encode($data['leagues_soccerway'])?>,
        windrawwin : <?php echo json_encode($data['leagues_windrawwin'])?>,
        predictz : <?php echo json_encode($data['leagues_predictz'])?>,
    }
</script>