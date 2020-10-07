<div class="row">
    <div class="col-lg-12">
        <div class="view-header">
            <div class="header-title">
                <h5>
                    <span><i class="fa fa-soccer-ball-o"></i></span>
                    <span>Matches > </span>
<!--                    <a href="--><?php //echo base_url('profile/index')?><!--"><i class="fa fa-briefcase mr-5"></i>Summary</a>-->
                </h5>
            </div>
        </div>
        <hr>
    </div>
</div>

<div class="row mb-10">
    <div class="col-md-10 col-md-offset-1">
        <form id="frmFilter" class="form-horizontal">
            <input type="hidden" id="txtAction" value="api/x_fetch_matches" />
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <div class="custom-checkbox">
                            <input id="radio_daily" name="radioType" type="radio" value="daily" checked/><label for="radio_daily"><img src="<?php echo BASE_URL . "assets/img/components/fill.gif"?>"/>Daily</label>
                        </div>
                        <div class="custom-checkbox">
                            <input id="radio_weekly" name="radioType" type="radio" value="weekly"/><label for="radio_weekly"><img src="<?php echo BASE_URL . "assets/img/components/fill.gif"?>"/>Weekly</label>
                        </div>
                        <div class="custom-checkbox">
                            <input id="radio_monthly" name="radioType" type="radio" value="monthly"/><label for="radio_monthly"><img src="<?php echo BASE_URL . "assets/img/components/fill.gif"?>"/>Monthly</label>
                        </div>
                        <div class="custom-checkbox">
                            <input id="radio_all" name="radioType" type="radio" value=""/><label for="radio_all"><img src="<?php echo BASE_URL . "assets/img/components/fill.gif"?>"/>All</label>
                        </div>
                    </div>
                    <div class="form-group has-feedback pl-15">
                        <label for="txtDate" class="inline-block mr-5">Date<sup class="mask-tip">*</sup>:</label>
                        <input type="text" class="form-control inline-block" value="<?php echo getDateTime('Y-m-d')?>"
                               id="txtDate" name="date" required
                               data-required-error="Date is empty!"
                               data-error="Invalid Date value!">
                        <div class="help-block with-errors small"></div>
                    </div>
                    <?php
                    $totalWeeks = date("W",strtotime('28th December '.date('Y')));
                    $curWeek = date('W');
                    ?>
                    <div class="form-group has-feedback pl-15 hidden">
                        <label for="optWeek" class="inline-block mr-5">Week<sup class="mask-tip">*</sup>:</label>
                        <select class="form-control inline-block" id="optWeek" name="optWeek">
                            <?php
                            for($w = 1; $w <= $totalWeeks; $w++) {
                                $selected = is_selected($w, $curWeek);
                                echo "<option value='{$w}' {$selected}>Week {$w}</option>";
                            }
                            ?>
                        </select>
                        <div class="help-block with-errors small"></div>
                    </div>
                    <div class="form-group has-feedback pl-15 hidden">
                        <label for="optMonth" class="inline-block mr-5">Month<sup class="mask-tip">*</sup>:</label>
                        <select class="form-control inline-block" id="optMonth" name="optMonth">
                            <?php
                            $curMonth = date('m');
                            for($m = 1; $m <= 12; $m++) {
                                $selected = is_selected($m, $curMonth);
                                $monthName = date("F", strtotime(sprintf("%s-%02d-01", date('Y'), $m)));
                                echo "<option value='{$m}' {$selected}>{$monthName}</option>";
                            }
                            ?>
                        </select>
                        <div class="help-block with-errors small"></div>
                    </div>
                </div>
                <div class="col-md-8 pt-12">
                    <div class="form-group has-feedback mb-0">
                        <label for="optCountry">Country<sup class="mask-tip">*</sup>:</label>
                        <select class="form-control full-width" required multiple
                                id="optCountry" name="country[]"
                                data-required-error="Country is empty!">
                            <option value="">Select</option>
                        </select>
                        <div class="help-block with-errors small"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-primary mr-5 btnMatches"><i class="fa fa-refresh"></i>&nbsp;Check Matches</button>
                            <button type="button" class="btn btn-info mr-5 btnTips"><i class="fa fa-info-circle"></i>&nbsp;Check Tips</button>
                            <button type="button" class="btn btn-danger mr-5 btnAnalyze"><i class="fa fa-calculator"></i>&nbsp;Analyze</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row mb-30">
    <div class="col-md-12">
        <ul class="nav nav-tabs">
            <li id="tab-step-1" class="active"><a href="#div-oddsportal" data-toggle="tab"><i class="fa fa-soccer-ball-o mr-5"></i>Matches</a></li>
            <li id="tab-step-2"><a href="#div-qualified" data-toggle="tab"><i class="fa fa-check-circle-o mr-5"></i>Qualified</a></li>
            <li id="tab-step-3"><a href="#div-analyzed" data-toggle="tab"><i class="fa fa-calculator mr-5"></i>Analyzed</a></li>
        </ul>
        <div id="myTabContent" class="tab-content" style="padding: 20px">
            <div class="tab-pane fade active in" id="div-oddsportal">
                <div class="row">
                    <div class="col-md-12">
                        <table id="tableOddsportal" class="table table-striped table-hover" width="100%">
                            <thead>
                            <tr>
                                <th class="cell-style">No</th>
                                <th class="cell-style">Date</th>
                                <th class="cell-style">Time</th>
                                <th class="cell-style">Country</th>
                                <th class="cell-style">League</th>
                                <th class="cell-style">Team A</th>
                                <th class="cell-style">Team B</th>
                                <th class="cell-style">Score</th>
                                <th class="cell-style">1</th>
                                <th class="cell-style">X</th>
                                <th class="cell-style">2</th>
                                <th class="cell-style">Bookmark</th>
                            </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade in" id="div-qualified">
                <div class="row">
                    <div class="col-md-12">
                        <table id="tableQualified" class="table table-striped table-hover" width="100%">
                            <thead>
                            <tr>
                                <th class="cell-style">No</th>
                                <th class="cell-style">Date</th>
                                <th class="cell-style">Time</th>
                                <th class="cell-style">Country</th>
                                <th class="cell-style">League</th>
                                <th class="cell-style">Home Team</th>
                                <th class="cell-style">Result</th>
                                <th class="cell-style">Away Team</th>
                                <th class="cell-style">1</th>
                                <th class="cell-style">X</th>
                                <th class="cell-style">2</th>
                                <th class="cell-style">1x2</th>
                                <th class="cell-style">O/U</th>
                                <th class="cell-style">CS</th>
                                <th class="cell-style">1x2</th>
                                <th class="cell-style">CS</th>
                                <th class="cell-style">1x2</th>
                                <th class="cell-style">CS</th>
                                <th class="cell-style">Soccer Away</th>
                            </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade in" id="div-analyzed">
                <div class="row">
                    <div class="col-md-12">
                        <table id="tableAnalyzed" class="table table-striped table-hover" width="100%">
                            <thead>
                            <tr>
                                <th class="cell-style">No</th>
                                <th class="cell-style">Week</th>
                                <th class="cell-style">At</th>
                                <th class="cell-style">League</th>
                                <th class="cell-style">Team</th>
                                <th class="cell-style">Result</th>
                                <th class="cell-style">Result</th>
                                <th class="cell-style">K1</th>
                                <th class="cell-style">%</th>
                                <th class="cell-style">K2</th>
                                <th class="cell-style">%</th>
                                <th class="cell-style">K3</th>
                                <th class="cell-style">%</th>
                                <th class="cell-style">K4</th>
                                <th class="cell-style">%</th>
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

<script type="text/javascript">
    let countryList = {};
    <?php
        $countries = $data['countries'];
        foreach ($countries as $country) {
            $name = getValueInArray($country, 'country');
            $code = getValueInArray($country, 'iso2_code');
    ?>
        countryList['<?php echo $name?>'] = {
            code : '<?php echo $code?>',
            icon : '<?php echo strtolower($name)?>'
        };
    <?php
        }
    ?>

    let recommendCountries = <?php echo json_encode($data['favor_countries'])?>;
</script>