<?php
$allSeasons = $data['seasons'];
?>
<div class="row">
    <div class="col-lg-12">
        <div class="view-header">
            <div class="header-title">
                <h5>
                    <span><i class="fa fa-gear"></i></span>
                    <span>Management > </span>
                    <a href="<?php echo base_url('manage/season')?>"><i class="fa fa-calendar mr-5"></i>Seasons</a>
                </h5>
            </div>
        </div>
        <hr>
    </div>
</div>
<div class="row mb-10">
    <div class="col-md-10 col-md-offset-1">
        <div class="form-group">
        <?php
        foreach ($allSeasons as $season) {
            $id = $season['id'];
            $name = $season['season'];
            $status = $season['status'];
        ?>
            <div class="custom-checkbox">
                <input id="radioSeason_<?php echo $id?>" name="season" type="radio" value="<?php echo $name?>" <?php echo $status=='active' ? 'checked' : ''?>/><label for="radioSeason_<?php echo $id?>"><img src="<?php echo BASE_URL . "assets/img/components/fill.gif"?>"/><?php echo $name?></label>
            </div>
        <?php
        }
        ?>
        </div>
        <button class="btn btn-warning btnSave"><i class="fa fa-check mr-5"></i>Set current season as active one</button>
    </div>
</div>