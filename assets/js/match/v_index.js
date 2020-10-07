$(document).ready(function(){
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href");
        if($(target).find('.dataTables_wrapper').length > 0) {
            App.resizeDataTable();
        }
    });

    $('#txtDate')
        .datepicker({
            format: "yyyy-mm-dd",
            autoclose: true,
        })
        .on('changeDate',function(event){
            reloadAllTables();
        });

    // ------- Country Selection -------
    // let countryList = {
    //     'England'   : { code: 'en', icon: 'england' },
    //     'Germany'   : { code: 'de', icon: 'germany' },
    //     'Spain'     : { code: 'es', icon: 'spain' },
    //     'Italy'     : { code: 'it', icon: 'italy' },
    //     'France'    : { code: 'fr', icon: 'france' },
    //     'Netherlands'    : { code: 'nl', icon: 'netherlands' },
    //     'Denmark'   : { code: 'dk', icon: 'denmark' }
    // };

    // let countryData = window.intlTelInputGlobals.getCountryData();

    let _html = '';
    $.each(countryList, function(name, countryItem){
        _html += '<option value="' + name + '">' + name + '</option>';
    });
    $('#optCountry').html(_html);

    $("#optCountry").select2({
        placeholder: 'Select countries',
        allowClear: true,
        templateResult: function (state) {
            if (!state.id || state.id.length == 0) { return state.text; }
            let $state = $("<span class='fifa-flag " + countryList[state.id]['icon'] + "'></span><span style='margin-left:10px'>" + state.text + "</span>");
            return $state;
        },
        templateSelection: function(selection) {
            if(selection.selected) {
                return $.parseHTML("<span class='ml-5 mt-2 fifa-flag " + countryList[selection.text]['icon'] + "'></span><span class='ml-5'>" + selection.text + "</span>");
            }
            else {
                return $.parseHTML('<span class="">' + selection.text + '</span>');
            }
        }
    });

    let favCountries = [];
    $.each(recommendCountries, function(idx, row){
        favCountries.push(row.country);
    });

    $('#optCountry').val(favCountries).trigger('change');

    $(".select2-container").prop("style", "width:100% !important;");

    // $('#optCountry').on("select2:select", function(event) {
    //     let value = $(event.currentTarget).find("option:selected").val();
    //
    //     let _html = '';
    //     if(value.length == 0) {
    //         _html = "<span style=''>Select Country</span>";
    //     }
    //     else {
    //         _html = "<span class='fifa-flag " + countryList[value]['icon'] + "'></span><span style='margin-left:10px'>" + value + "</span>";
    //     }
    //
    //     $('#select2-optCountry-container').html(_html);
    // });

    $("#optCountry").change(function(e){
        reloadAllTables();
    });

    function reloadAllTables() {
        setTimeout(
            function() {
                App.reloadDataTable(tableOddsportal, true);
                App.reloadDataTable(tableQualified, true);
                App.reloadDataTable(tableAnalyzed, true);
            },
            500
        );
    }

    //////////////////////////////////////////////
    // Fetch
    //////////////////////////////////////////////
    $('#frmFilter').validator('update');

    $('#frmFilter').validator().on('submit', function (e) {
        if (e.isDefaultPrevented()) {
            // handle the invalid form...
            console.log('invalid form data');
        }
        else {
            e.preventDefault();

            let formData = new FormData(this);

            // Ajax
            let action = $('#txtAction').val();
            Action.uploadRequest(
                action,
                formData,
                function(responseData) {
                    setTimeout(
                        function() {
                            if(action == 'api/x_fetch_matches') {
                                App.reloadDataTable(tableOddsportal, true);
                                App.reloadDataTable(tableQualified, true);
                                App.reloadDataTable(tableAnalyzed, true);

                                setTimeout(function(){
                                    $('#tab-step-1 > a').trigger('click');
                                }, 500);
                            }
                            else if(action == 'api/x_fetch_tips') {
                                App.reloadDataTable(tableQualified, true);
                                setTimeout(function(){
                                    $('#tab-step-2 > a').trigger('click');
                                }, 500);
                            }
                            else if(action == 'api/x_analyze_matches') {
                                App.reloadDataTable(tableAnalyzed, true);

                                setTimeout(function(){
                                    $('#tab-step-3 > a').trigger('click');
                                }, 500);
                            }
                        },
                        500
                    );
                },
                null,
                {block_element:$('#frmFilter')}
            );
        }
    });

    $('.btnMatches').off().on('click', function(e){
        e.preventDefault();

        $('#txtAction').val('api/x_fetch_matches');
        $('#frmFilter').submit();
    });

    $('.btnTips').off().on('click', function(e){
        e.preventDefault();

        $('#txtAction').val('api/x_fetch_tips');
        $('#frmFilter').submit();
    });

    $('.btnAnalyze').off().on('click', function(e){
        e.preventDefault();

        $('#txtAction').val('api/x_analyze_matches');
        $('#frmFilter').submit();
    });

    //////////////////////////////////////////////
    // Oddsportal
    //////////////////////////////////////////////
    let tableOddsportal = $('#tableOddsportal').dataTable({
        "serverSide": true,
        "ajax" : function(data, callback, settings) {
            Action.sendRequest(
                'match/x_list_oddsportal',
                $.extend(data, {
                    date    : $('#txtDate').val(),
                    country : $('#optCountry').val(),
                    week    : $('#optWeek').val(),
                    month   : $('#optMonth').val(),
                    dateType: $('input[name="radioType"]:checked').val()
                }),
                function(jsonData) {
                    callback(jsonData);
                    if(jsonData['others'] != undefined) {
                        let others = jsonData['others'];
                    }
                },
                null,
                null,
                false
            );
        },
        "dom": "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>t<'row'<'col-sm-6'i><'col-sm-6'p>>",
        "lengthMenu": [ [10, 20, 30, 50], [10, 20, 30, 50] ],
        "iDisplayLength": 10,
        "columns": [
            { "data": "index_no" },
            { "data": "date_found" },
            { "data": "match_time" },
            { "data": "country" },
            { "data": "division" },
            { "data": "team1" },
            { "data": "team2" },
            { "data": "score" },
            { "data": "odds_1" },
            { "data": "odds_x" },
            { "data": "odds_2" },
            { "data": "bookmark" }
        ],
        "columnDefs": [
            { "targets": [ 0 ], "searchable": false, "orderable": false },
        ],
        "order": [ [1, "desc"] ],
        "language" : { "url": base_url + "/assets/library/datatable/lang/english.json" },
        "createdRow": function( row, data, dataIndex ) {
            let style = 'cell-style';
            let _html = '';

            $('td', row).eq(0).html('<div class="' + style + '">' + Util.getString(data['index_no']) + '</div>');

            _html = '<span class="mr-5 fifa-flag ' + countryList[data['country']].icon + '"></span>';
            _html+= '<span class="">' + Util.getString(data['country']) + '</span>';
            $('td', row).eq(3).html('<div class="cell-text">' + _html + '</div>');

            let teamA = Util.getString(data['team1']);
            let teamB = Util.getString(data['team2']);
            let teamAway = Util.getString(data['away_team']);

            if(teamAway.length > 0 && teamA != teamAway) {
                _html = '<span class="ft-bold">' + teamA + '</span>';
            }
            else {
                _html = teamA;
            }
            $('td', row).eq(5).html('<div class="cell-text">' + _html + '</div>');

            if(teamAway.length > 0 && teamB != teamAway) {
                _html = '<span class="ft-bold">' + teamB + '</span>';
            }
            else {
                _html = teamB;
            }
            $('td', row).eq(6).html('<div class="cell-text">' + _html + '</div>');


            // _html = '<button class="btn btn-sm btn-warning btnEdit" data-toggle="tooltip" data-placement="top" data-original-title="Edit"><i class="fa fa-pencil"></i></button>';
            // _html+= '<button class="btn btn-sm btn-danger btnDelete ml-5" data-toggle="tooltip" data-placement="top" data-original-title="Delete"><i class="fa fa-trash"></i></button>';
        },
        responsive: {
            details: {
                renderer: function ( api, rowIdx, columns ) {
                    let rowData = tableOddsportal.fnGetData(rowIdx);
                    let data = $.map( columns, function ( col, i ) {
                        let colData = Util.getString(col.data);

                        let _html = "";
                        if(i == 12) {
                            // _html = '<button class="btn btn-sm btn-warning btnEdit" data-toggle="tooltip" data-placement="top" data-original-title="Edit"><i class="fa fa-pencil"></i> Edit</button>';
                            // _html+= '<button class="btn btn-sm btn-danger btnDelete ml-10" data-toggle="tooltip" data-placement="top" data-original-title="Delete"><i class="fa fa-trash"></i> Delete</button>';
                        }
                        else {
                            _html = colData;
                        }

                        return col.hidden ?
                            '<tr data-dt-row="' + col.rowIndex+'" data-dt-column="' + col.columnIndex+'">' +
                            '<td>' + col.title + ':' + '</td> ' +
                            '<td>' + (_html.length > 0 ? _html : col.data) + '</td>' +
                            '</tr>' :
                            '';
                    } ).join('');

                    return data ? $('<table/>').append( data ) : false;
                }
            }
        },
        "rowCallback": function ( row, data, index ) {},
        "drawCallback": function( settings ) {
            let api = this.api();

            setTimeout(function() {
                    App.resizeDataTable();
                    App.handleDataToolTip();
                }, 200
            );
        },
        "initComplete": function( settings, json ) {
            let api = this.api();

            $('#tableOddsportal_filter input')
                .off('.DT')
                .on('keyup.DT', function (e) { if (e.keyCode == App.KEYS.ENTER) { api.search(this.value).draw(); } });
        }
    });

    tableOddsportal.on( 'responsive-display.dt', function ( e, datatable, row, showHide, update ) {
        // console.log( 'Details for row '+row.index()+' '+(showHide ? 'shown' : 'hidden') );
        if(showHide) {
            App.handleDataToolTip();
        }
    });


    //////////////////////////////////////////////
    // Qualified
    //////////////////////////////////////////////
    let tableQualified = $('#tableQualified').dataTable({
        "serverSide": true,
        "ajax" : function(data, callback, settings) {
            Action.sendRequest(
                'match/x_list_qualified',
                $.extend(data, {
                    date    : $('#txtDate').val(),
                    country : $('#optCountry').val(),
                    week    : $('#optWeek').val(),
                    month   : $('#optMonth').val(),
                    dateType: $('input[name="radioType"]:checked').val()
                }),
                function(jsonData) {
                    callback(jsonData);
                    if(jsonData['others'] != undefined) {
                        let others = jsonData['others'];
                    }
                },
                null,
                null,
                false
            );
        },
        "dom": "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>t<'row'<'col-sm-6'i><'col-sm-6'p>>",
        "lengthMenu": [ [10, 20, 30, 50], [10, 20, 30, 50] ],
        "iDisplayLength": 10,
        "columns": [
            { "data": "index_no" },
            { "data": "date_found" },
            { "data": "match_time" },
            { "data": "country" },
            { "data": "division" },
            { "data": "home_team" },
            { "data": "result" },
            { "data": "away_team" },
            { "data": "odds_1" },
            { "data": "odds_x" },
            { "data": "odds_2" },
            { "data": "soccervista_1x2" },
            { "data": "soccervista_goal" },
            { "data": "soccervista_cs" },
            { "data": "predictz_result" },
            { "data": "predictz_score" },
            { "data": "windrawwin_1x1" },
            { "data": "windrawwin_cs" },
            { "data": "soccerway_link" }
        ],
        "columnDefs": [
            { "targets": [ 0 ], "searchable": false, "orderable": false },
        ],
        "order": [ [1, "desc"] ],
        "language" : { "url": base_url + "/assets/library/datatable/lang/english.json" },
        "createdRow": function( row, data, dataIndex ) {
            let style = 'cell-style';
            let _html = '';

            $('td', row).eq(0).html('<div class="' + style + '">' + Util.getString(data['index_no']) + '</div>');

            _html = '<span class="mr-5 fifa-flag ' + countryList[data['country']].icon + '"></span>';
            _html+= '<span class="">' + Util.getString(data['country']) + '</span>';
            $('td', row).eq(3).html('<div class="cell-text">' + _html + '</div>');

            _html = '';
            let link = Util.getString(data['soccerway_link']);
            if(link.length > 0) {
                _html = '<a target="_blank" href="' + link + '" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" data-original-title="View"><i class="fa fa-eye"></i></a>';
            }
            $('td', row).eq(18).html(_html);
        },
        responsive: {
            details: {
                renderer: function ( api, rowIdx, columns ) {
                    let rowData = tableQualified.fnGetData(rowIdx);
                    let data = $.map( columns, function ( col, i ) {
                        let colData = Util.getString(col.data);

                        let _html = "";
                        if(i == 18) {
                            if(colData.length > 0) {
                                _html = '<a target="_blank" href="' + colData + '" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" data-original-title="View"><i class="fa fa-eye"></i></a>';
                            }
                        }
                        else {
                            _html = colData;
                        }

                        return col.hidden ?
                            '<tr data-dt-row="' + col.rowIndex+'" data-dt-column="' + col.columnIndex+'">' +
                            '<td>' + col.title + ':' + '</td> ' +
                            '<td>' + (_html.length > 0 ? _html : col.data) + '</td>' +
                            '</tr>' :
                            '';
                    } ).join('');

                    return data ? $('<table/>').append( data ) : false;
                }
            }
        },
        "rowCallback": function ( row, data, index ) {},
        "drawCallback": function( settings ) {
            let api = this.api();

            setTimeout(function() {
                    App.resizeDataTable();
                    App.handleDataToolTip();

                    let pageInfo = api.table().page.info();

                    if(pageInfo.pages > 0) {
                        // Add Custom Buttons;
                        let _html = '<div class="align-center">'
                            + '<button class="btn btn-sm btn-warning btnExportQualified mb-5" style=""><i class="fa fa-download"></i>&nbsp;Export to Excel</button>'
                            + '</div>';
                        $('#tableQualified_wrapper .text-center').html(_html);

                        $('.btnExportQualified').off().on('click', function (e) {
                            e.preventDefault();

                            let date = $('#txtDate').val();

                            if(date.trim().length > 0) {
                                Action.sendRequest(
                                    'match/x_export_qualified',
                                    {
                                        date    : $('#txtDate').val(),
                                        country : $('#optCountry').val(),
                                        week    : $('#optWeek').val(),
                                        month   : $('#optMonth').val(),
                                        dateType: $('input[name="radioType"]:checked').val()
                                    },
                                    function (responseData) {
                                        console.log(responseData);
                                        if (responseData.file !== undefined) {
                                            Action.downloadFile(responseData.file, responseData.name, responseData.doDelete);
                                        }
                                    },
                                    null,
                                    {block_element: $('#div-final')}
                                );
                            }
                            else {
                                Dialog.commonPromptWith('', 'Please select a date');
                            }
                        });
                    }
                    else {
                        $('#tableQualified_wrapper .text-center').html("");
                    }
                }, 200
            );
        },
        "initComplete": function( settings, json ) {
            let api = this.api();

            $('#tableQualified_filter input')
                .off('.DT')
                .on('keyup.DT', function (e) { if (e.keyCode == App.KEYS.ENTER) { api.search(this.value).draw(); } });
        }
    });

    tableQualified.on( 'responsive-display.dt', function ( e, datatable, row, showHide, update ) {
        // console.log( 'Details for row '+row.index()+' '+(showHide ? 'shown' : 'hidden') );
        if(showHide) {
            App.handleDataToolTip();
        }
    });

    //////////////////////////////////////////////
    // Analyzed
    //////////////////////////////////////////////
    let tableAnalyzed = $('#tableAnalyzed').dataTable({
        "serverSide": true,
        "ajax" : function(data, callback, settings) {
            Action.sendRequest(
                'match/x_list_analyzed',
                $.extend(data, {
                    date    : $('#txtDate').val(),
                    country : $('#optCountry').val(),
                    week    : $('#optWeek').val(),
                    month   : $('#optMonth').val(),
                    dateType: $('input[name="radioType"]:checked').val()
                }),
                function(jsonData) {
                    callback(jsonData);
                    if(jsonData['others'] != undefined) {
                        let others = jsonData['others'];
                    }
                },
                null,
                null,
                false
            );
        },
        "dom": "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>t<'row'<'col-sm-6'i><'col-sm-6'p>>",
        "lengthMenu": [ [10, 20, 30, 50], [10, 20, 30, 50] ],
        "iDisplayLength": 10,
        "columns": [
            { "data": "index_no" },
            { "data": "roypick_grp" },
            { "data": "match_at" },
            { "data": "competition" },
            { "data": "match_team" },
            { "data": "result_1" },
            { "data": "result_2" },
            { "data": "c_spic1" },
            { "data": "c_spic1_p" },
            { "data": "c_spic2" },
            { "data": "c_spic2_p" },
            { "data": "c_spic3" },
            { "data": "c_spic3_p" },
            { "data": "c_spic4" },
            { "data": "c_spic4_p" },
            { "data": "id" }
        ],
        "columnDefs": [
            { "targets": [ 0, 15 ], "searchable": false, "orderable": false },
        ],
        "order": [ [1, "desc"] ],
        "language" : { "url": base_url + "/assets/library/datatable/lang/english.json" },
        "createdRow": function( row, data, dataIndex ) {
            let style = 'cell-style';
            let _html = '';

            $('td', row).eq(0).html('<div class="' + style + '">' + Util.getString(data['index_no']) + '</div>');

            _html = '<button class="btn btn-sm btn-warning btnExportAnalyzedItem" data-toggle="tooltip" data-placement="top" data-original-title="Download"><i class="fa fa-download"></i></button>';
            $('td', row).eq(15).html('<div class="' + style + '">' + _html + '</div>');
        },
        responsive: {
            details: {
                renderer: function ( api, rowIdx, columns ) {
                    let rowData = tableAnalyzed.fnGetData(rowIdx);
                    let data = $.map( columns, function ( col, i ) {
                        let colData = Util.getString(col.data);

                        let _html = "";
                        if(i == 15) {
                            _html = '<button class="btn btn-sm btn-warning btnExportAnalyzedItem" data-toggle="tooltip" data-placement="top" data-original-title="Download"><i class="fa fa-download mr-5"></i> Download</button>';
                        }
                        else {
                            _html = colData;
                        }

                        return col.hidden ?
                            '<tr data-dt-row="' + col.rowIndex+'" data-dt-column="' + col.columnIndex+'">' +
                            '<td>' + col.title + ':' + '</td> ' +
                            '<td>' + (_html.length > 0 ? _html : col.data) + '</td>' +
                            '</tr>' :
                            '';
                    } ).join('');

                    return data ? $('<table/>').append( data ) : false;
                }
            }
        },
        "rowCallback": function ( row, data, index ) {},
        "drawCallback": function( settings ) {
            let api = this.api();

            setTimeout(function() {
                    App.resizeDataTable();
                    App.handleDataToolTip();

                    let pageInfo = api.table().page.info();

                    if(pageInfo.pages > 0) {
                        // Add Custom Buttons;
                        let _html = '<div class="align-center">'
                            + '<button class="btn btn-sm btn-warning btnExportAnalyzed mb-5" style=""><i class="fa fa-download"></i>&nbsp;Export to Excel</button>'
                            + '</div>';
                        $('#tableAnalyzed_wrapper .text-center').html(_html);

                        $('.btnExportAnalyzed').off().on('click', function (e) {
                            e.preventDefault();

                            let date = $('#txtDate').val();

                            if(date.trim().length > 0) {
                                Action.sendRequest(
                                    'match/x_export_analyzed',
                                    {
                                        date    : $('#txtDate').val(),
                                        country : $('#optCountry').val(),
                                        week    : $('#optWeek').val(),
                                        month   : $('#optMonth').val(),
                                        dateType: $('input[name="radioType"]:checked').val()
                                    },
                                    function (responseData) {
                                        console.log(responseData);
                                        if (responseData.file !== undefined) {
                                            Action.downloadFile(responseData.file, responseData.name, responseData.doDelete);
                                        }
                                    },
                                    null,
                                    {block_element: $('#div-final')}
                                );
                            }
                            else {
                                Dialog.commonPromptWith('', 'Please select a date');
                            }
                        });
                    }
                    else {
                        $('#tableAnalyzed_wrapper .text-center').html("");
                    }
                }, 200
            );
        },
        "initComplete": function( settings, json ) {
            let api = this.api();

            $('#tableAnalyzed_filter input')
                .off('.DT')
                .on('keyup.DT', function (e) { if (e.keyCode == App.KEYS.ENTER) { api.search(this.value).draw(); } });
        }
    });

    tableAnalyzed.on( 'responsive-display.dt', function ( e, datatable, row, showHide, update ) {
        // console.log( 'Details for row '+row.index()+' '+(showHide ? 'shown' : 'hidden') );
        if(showHide) {
            App.handleDataToolTip();
        }
    });

    tableAnalyzed.on('click', '.btnExportAnalyzedItem', function (e) {
        e.preventDefault();
        if($(this).hasClass('disabled')) return;

        let nRow = $(this).parents('tr')[0];
        let aData = tableAnalyzed.fnGetData(nRow);

        Action.downloadFile(aData['match_id'] + ".xlsx", aData['match_team'], 0);
    });


    ///////////////////////////////////////////////////
    // Date / Week Selection
    ///////////////////////////////////////////////////

    $('input[name="radioType"]').change(function(e){
        let val = $(this).val();

        if(val == 'daily') {
            $('#txtDate').closest('.form-group').removeClass('hidden');
            $('#optWeek').closest('.form-group').addClass('hidden');
            $('#optMonth').closest('.form-group').addClass('hidden');

            $('.btnMatches').removeClass('hidden');
            $('.btnTips').removeClass('hidden');
            $('.btnAnalyze').removeClass('hidden');
        }
        else if(val == 'weekly') {
            $('#txtDate').closest('.form-group').addClass('hidden');
            $('#optWeek').closest('.form-group').removeClass('hidden');
            $('#optMonth').closest('.form-group').addClass('hidden');

            $('.btnMatches').addClass('hidden');
            $('.btnTips').addClass('hidden');
            $('.btnAnalyze').addClass('hidden');
        }
        else if(val == 'monthly') {
            $('#txtDate').closest('.form-group').addClass('hidden');
            $('#optWeek').closest('.form-group').addClass('hidden');
            $('#optMonth').closest('.form-group').removeClass('hidden');

            $('.btnMatches').addClass('hidden');
            $('.btnTips').addClass('hidden');
            $('.btnAnalyze').addClass('hidden');
        }
        else {
            $('#txtDate').closest('.form-group').addClass('hidden');
            $('#optWeek').closest('.form-group').addClass('hidden');
            $('#optMonth').closest('.form-group').addClass('hidden');

            $('.btnMatches').addClass('hidden');
            $('.btnTips').addClass('hidden');
            $('.btnAnalyze').addClass('hidden');
        }

        reloadAllTables();
    });

    $('#optWeek, #optMonth').change(function(e){
        reloadAllTables();
    });
});
