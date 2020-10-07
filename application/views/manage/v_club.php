<?php
$allLeagues = $data['leagues'];
$seasons = $data['seasons'];
?>
<div class="row">
    <div class="col-lg-12">
        <div class="view-header">
            <div class="header-title">
                <h5>
                    <span><i class="fa fa-gear"></i></span>
                    <span>Management > </span>
                    <a href="<?php echo base_url('manage/club')?>"><i class="fa fa-group mr-5"></i>Clubs</a>

                    <button class="btn btn-sm btn-primary btnAddNew pull-right"><i class="fa fa-plus mr-5"></i>Add New</button>
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
                        $activeSeason = '';
                        foreach ($seasons as $season) {
                            $seasonName = $season['season'];
                            $seasonStatus = $season['status'];

                            if($seasonStatus=='active') {
                                $activeSeason = $seasonName;
                            }
                            ?>
                            <option value="<?php echo $seasonName?>" <?php echo $seasonStatus=='active'? 'selected' : ''?>><?php echo $seasonName?></option>
                        <?php
                        }
                        ?>
                    </select>
                    <div class="help-block with-errors small"></div>
                </div>
            </div>
            <div class="col-sm-3 col-xs-4">
                <div class="form-group has-feedback">
                    <label for="optCountry">Country<sup class="mask-tip">*</sup>:</label>
                    <select class="form-control full-width" required
                            id="optCountry" name="country"
                            data-required-error="Country is empty!">
                        <?php
                        $activeCountry = '';
                        $activeLeagues = array();
                        if(!isEmptyString($activeSeason)) {
                            $count = 0;
                            foreach ($allLeagues[$activeSeason] as $country => $leagues) {
                                if($count == 0) {
                                    $activeCountry = $country;
                                    $activeLeagues = $leagues;
                                }
                                ?>
                                <option value="<?php echo $country?>" <?php echo $count == 0 ? 'selected' : ''?>><?php echo $country?></option>
                        <?php
                                $count ++;
                            }
                        }
                        ?>
                    </select>
                    <div class="help-block with-errors small"></div>
                </div>
            </div>
            <div class="col-sm-3 col-xs-4">
                <div class="form-group has-feedback">
                    <label for="optLeague">League<sup class="mask-tip">*</sup>:</label>
                    <select class="form-control full-width" required
                            id="optLeague" name="league"
                            data-required-error="League is empty!">
                        <?php
                        if(!isEmptyString($activeCountry)) {
                            $count = 0;
                            foreach ($activeLeagues as $activeLeague) {
                            ?>
                                <option value="<?php echo $activeLeague?>" <?php echo $count == 0 ? 'selected' : ''?>><?php echo $activeLeague?></option>
                        <?php
                                $count ++;
                            }
                        }
                        ?>
                    </select>
                    <div class="help-block with-errors small"></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <table id="tableData" class="table table-striped table-hover" width="100%">
                    <thead>
                    <tr>
                        <th class="cell-style">No</th>
                        <th class="cell-style">Oddsportal</th>
                        <th class="cell-style">Soccervista</th>
                        <th class="cell-style">Soccerway</th>
                        <th class="cell-style">Predictz</th>
                        <th class="cell-style">Windrawwin</th>
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

<div id="dialog-club" class="modal fade">
    <div class="modal-dialog modal-md" tabindex="-1" role="dialog">
        <div class="modal-content">
            <form id="frmData" name="frmData" role="form">
                <input type="hidden" id="txtClubID" name="id" value="">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;
                    </button>
                    <h4 class="modal-title"><i class="fa fa-group mr-5"></i>Add New</h4>
                </div>
                <div class="modal-body pl-20 pr-20">
                    <div class="row">
                        <div class="col-md-12 mb-10">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group has-feedback">
                                        <label for="optOddsportal">Oddsportal<sup class="mask-tip">*</sup>:</label>
                                        <select id="optOddsportal" name="oddsportal" required class="form-control"
                                                data-required-error="Oddsportal is empty!"
                                                data-error="Invalid Oddsportal value!">
                                            <option value="">Select</option>
                                        </select>
                                        <div class="help-block with-errors small"></div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
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
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
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
                                <div class="col-sm-6">
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
                                <div class="col-sm-6">
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
<!--                                <div class="col-sm-6">-->
<!--                                    <div class="form-group has-feedback">-->
<!--                                        <label for="optSoccerbase">Soccerbase:</label>-->
<!--                                        <select id="optSoccerbase" name="soccerbase" class="form-control"-->
<!--                                                data-required-error="Soccerbase is empty!"-->
<!--                                                data-error="Invalid Soccerbase value!">-->
<!--                                            <option value="">Select</option>-->
<!--                                        </select>-->
<!--                                        <div class="help-block with-errors small"></div>-->
<!--                                    </div>-->
<!--                                </div>-->
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
    let _gAllLeagues_ = <?php echo json_encode($data['leagues'])?>;
</script>