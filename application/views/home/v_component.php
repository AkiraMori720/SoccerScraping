<div class="main-contents">
    <div class="row">
        <div class="col-md-6">
            <h5 class="title">Custom Components</h5>

            <div class="form-group">
                <div class="custom-checkbox">
                    <input id="CommentLV_1" type="checkbox" name="chkCommentLV_1" value="1"/><label for="CommentLV_1"><img src="<?php echo BASE_URL . "assets/img/components/fill.gif"?>"/>LV1</label>
                </div>
                <div class="custom-checkbox">
                    <input id="CommentLV_2" type="checkbox" name="chkCommentLV_2" value="2"/><label for="CommentLV_2"><img src="<?php echo BASE_URL . "assets/img/components/fill.gif"?>"/>LV2</label>
                </div>
                <div class="custom-checkbox">
                    <input id="CommentLV_3" type="checkbox" name="chkCommentLV_3" value="3"/><label for="CommentLV_3"><img src="<?php echo BASE_URL . "assets/img/components/fill.gif"?>"/>LV3</label>
                </div>
                <div class="custom-checkbox">
                    <input id="CommentLV_4" type="checkbox" name="chkCommentLV_4" value="4"/><label for="CommentLV_4"><img src="<?php echo BASE_URL . "assets/img/components/fill.gif"?>"/>LV4</label>
                </div>
            </div>

            <div class="form-group">
                <div class="custom-checkbox">
                    <input id="radioType_country" name="radioType" type="radio" value="country"/><label for="radioType_country"><img src="<?php echo BASE_URL . "assets/img/components/fill.gif"?>"/>Option1</label>
                </div>
                <div class="custom-checkbox">
                    <input id="radioType_personal" name="radioType" type="radio" value="personal"/><label for="radioType_personal"><img src="<?php echo BASE_URL . "assets/img/components/fill.gif"?>"/>Option2</label>
                </div>
            </div>

            <div class="form-group">
                <button class="btn btn-primary">Primary Button</button>
                <button class="btn btn-primary disabled">Primary Button(Disabled)</button>
            </div>

            <div class="form-group">
                <div class="switch switch-on" data-toggle="tooltip" data-placement="top" data-original-title="Enabled"></div>
                <div class="switch switch-off" data-toggle="tooltip" data-placement="top" data-original-title="Disabled"></div>
            </div>

            <div class="form-group">
                <select class="custom-select">
                    <option value="1">Option1</option>
                    <option value="2">Option2</option>
                    <option value="3">Option3</option>
                    <option value="4">Option4</option>
                </select>
            </div>

            <div class="form-group">
                <button class="btn btn-success">Show Success Notify</button>
                <button class="btn btn-warning">Show Error Notify</button>
            </div>
        </div>
        <div class="col-md-6">
            <table id="tableList" class="table table-striped table-hover">
                <thead>
                <tr>
                    <th class="cell-style">No</th>
                    <th class="cell-style">Name</th>
                    <th class="cell-style">Overview</th>
                </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td><td>Name1</td><td>Overview1</td>
                    </tr>
                    <tr>
                        <td>2</td><td>Name2</td><td>Overview2</td>
                    </tr>
                    <tr>
                        <td>3</td><td>Name3</td><td>Overview3</td>
                    </tr>
                    <tr>
                        <td>4</td><td>Name4</td><td>Overview4</td>
                    </tr>
                    <tr>
                        <td>5</td><td>Name5</td><td>Overview5</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
