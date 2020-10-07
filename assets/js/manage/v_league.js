$(document).ready(function(){
    //////////////////////////////////////////////
    // Data
    //////////////////////////////////////////////
    let tableData = $('#tableData').dataTable({
        "serverSide": true,
        "ajax" : function(data, callback, settings) {
            Action.sendRequest(
                'manage/x_league_list',
                $.extend(data, {season: $('#optSeason').val()}),
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
            { "data": "country" },
            { "data": "division" },
            { "data": "max_matches" },
            // { "data": "oddsportal" },
            { "data": "soccervista" },
            { "data": "soccerway" },
            { "data": "predictz" },
            { "data": "windrawwin" },
            { "data": "soccerbase" },
            { "data": "id" },
        ],
        "columnDefs": [
            { "targets": [ 0, 9 ], "searchable": false, "orderable": false },
        ],
        "order": [ [1, "asc"] ],
        "language" : { "url": base_url + "/assets/library/datatable/lang/english.json" },
        "createdRow": function( row, data, dataIndex ) {
            let style = 'cell-style';
            let _html = '';

            $('td', row).eq(0).html('<div class="' + style + '">' + Util.getString(data['index_no']) + '</div>');

            _html = '<span class="mr-5 fifa-flag ' + data['country'].toLowerCase() + '"></span>';
            _html+= '<span class="">' + Util.getString(data['country']) + '</span>';
            $('td', row).eq(1).html('<div class="cell-text">' + _html + '</div>');

            _html = '<button class="btn btn-sm btn-warning btnEdit" data-toggle="tooltip" data-placement="top" data-original-title="Edit"><i class="fa fa-pencil"></i></button>';
            _html+= '<button class="btn btn-sm btn-danger btnDelete ml-5" data-toggle="tooltip" data-placement="top" data-original-title="Delete"><i class="fa fa-trash"></i></button>';
            $('td', row).eq(9).html(_html);
        },
        responsive: {
            details: {
                renderer: function ( api, rowIdx, columns ) {
                    let rowData = tableData.fnGetData(rowIdx);
                    let data = $.map( columns, function ( col, i ) {
                        let colData = Util.getString(col.data);

                        let _html = "";
                        if(i == 9) {
                            _html = '<button class="btn btn-sm btn-warning btnEdit" data-toggle="tooltip" data-placement="top" data-original-title="Edit"><i class="fa fa-pencil"></i> Edit</button>';
                            _html+= '<button class="btn btn-sm btn-danger btnDelete ml-10" data-toggle="tooltip" data-placement="top" data-original-title="Delete"><i class="fa fa-trash"></i> Delete</button>';
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

            $('#tableData_filter input')
                .off('.DT')
                .on('keyup.DT', function (e) { if (e.keyCode == App.KEYS.ENTER) { api.search(this.value).draw(); } });
        }
    });

    tableData.on( 'responsive-display.dt', function ( e, datatable, row, showHide, update ) {
        // console.log( 'Details for row '+row.index()+' '+(showHide ? 'shown' : 'hidden') );
        if(showHide) {
            App.handleDataToolTip();
        }
    });

    tableData.on('click', '.btnDelete', function (e) {
        e.preventDefault();
        if($(this).hasClass('disabled')) return;

        let nRow = $(this).parents('tr')[0];
        let aData = tableData.fnGetData(nRow);

        Dialog.openConfirm("Would you like to delete now?", function(){
            Action.sendRequest(
                'manage/x_league_del',
                { id : aData['id'] },
                function(jsonData) {
                    setTimeout(function() { App.reloadDataTable(tableData, false); }, 100);
                },
                null,
                null,
                false
            );
        });
    });

    tableData.on('click', '.btnEdit', function (e) {
        e.preventDefault();
        if($(this).hasClass('disabled')) return;

        let nRow = $(this).parents('tr')[0];
        let aData = tableData.fnGetData(nRow);

        Dialog.openLeague(aData, function(){
            setTimeout(function() { App.reloadDataTable(tableData, false); }, 500);
        });
    });

    $('.btnAddNew').off().on('click', function(e){
        e.preventDefault();

        Dialog.openLeague(null, function(){
            setTimeout(function() { App.reloadDataTable(tableData, false); }, 500);
        });
    });

    $('.btnAddPrev').off().on('click', function(e){
        e.preventDefault();

        Action.sendRequest(
            'manage/x_league_import_prev',
            {season : $('#optSeason').val()},
            function(){
                App.reloadDataTable(tableData, true);
            },
            null,
            null,
            false
        );
    });

    $('#optSeason').change(function(e){
        let val = $(this).val();
        if(val.length > 0) {
            App.reloadDataTable(tableData, true);
        }
    });

    let _html = '';
    $.each(_gCountryList_, function(name, countryItem){
        _html += '<option value="' + name + '">' + name + '</option>';
    });
    $('#optCountry').html(_html);

    $("#optCountry").select2({
        placeholder: 'Select country',
        allowClear: false,
        templateResult: function (state) {
            if (!state.id || state.id.length == 0) { return state.text; }
            let $state = $("<span class='fifa-flag " + _gCountryList_[state.id]['icon'] + "'></span><span style='margin-left:10px'>" + state.text + "</span>");
            return $state;
        },
        templateSelection: function(selection) {
            if(selection.selected) {
                return $.parseHTML("<span class='ml-5 mt-2 fifa-flag " + _gCountryList_[selection.text]['icon'] + "'></span><span class='ml-5'>" + selection.text + "</span>");
            }
            else {
                return $.parseHTML('<span class="">' + selection.text + '</span>');
            }
        }
    });

    function readonly_select(objs, action) {
        if (action===true)
            objs.prepend('<div class="disabled-select"></div>');
        else
            $(".disabled-select", objs).remove();
    }

    $(".select2-container").prop("style", "width:100% !important;");

    $('#dialog-league #optCountry').on("select2:select", function(event) {
        let value = $(event.currentTarget).find("option:selected").val();

        let sites = ['oddsportal', 'soccervista', 'soccerbase', 'soccerway', 'windrawwin', 'predictz'];
        $.each(sites, function(idx, site){
            if(_gAllLeagues_[site] != undefined && _gAllLeagues_[site] != null) {
                let html = "<option value=''>Select</option>";
                if(_gAllLeagues_[site][value] != undefined && _gAllLeagues_[site][value] != null) {
                    $.each(_gAllLeagues_[site][value], function(idx, leagueInf){
                        html += '<option value="' + leagueInf.league + '">' + leagueInf.league + '</option>';
                    });
                }
                $('#dialog-league #opt' + Util.ucwords(site)).html(html);
            }
        });
    });

    $('#optOddsportal, #optSoccervista, #optSoccerway, #optPredictz, #optWindrawwin').change(function(){
        let sites = ['oddsportal', 'soccervista', 'soccerway', 'predictz', 'windrawwin'];
        let curSite = $(this).prop('id').split('opt').join('').toLowerCase();

        let val = $(this).val().toLowerCase();

        if(val.length > 0) {
            $.each(sites, function (idx, site) {
                if(site != curSite) {
                    let optList = $('#dialog-league #opt' + Util.ucwords(site) + ' > option');
                    for (let k = 0; k < optList.length; k++) {
                        let optVal = $(optList[k]).attr('value').toLowerCase();

                        if (optVal.length == 0) {
                            continue;
                        }

                        if (val == optVal || val.indexOf(optVal) != -1 || optVal.indexOf(val) != -1) {
                            $('#dialog-league #opt' + Util.ucwords(site)).val($(optList[k]).attr('value'));
                            break;
                        }
                    }
                }
            });
        }
    });

    Dialog.openLeague = function(data, callback) {
        $('#dialog-league #frmData').trigger('reset');

        readonly_select($(".select2"), false);
        if(data != null) {
            $('#dialog-league #txtLeagueID').val(data.id);
            $('#dialog-league #txtMatches').val(data.max_matches);
            $('#dialog-league #optCountry').select2("trigger", "select", { data: { id: data.country } });

            // $('#dialog-league #optLeague').val(data.division);
            setTimeout(function(){
                $('#dialog-league #optSoccervista').val(data.soccervista).trigger('change');
                $('#dialog-league #optSoccerway').val(data.soccerway).trigger('change');
                $('#dialog-league #optSoccerbase').val(data.soccerbase).trigger('change');
                $('#dialog-league #optPredictz').val(data.predictz).trigger('change');
                $('#dialog-league #optWindrawwin').val(data.windrawwin).trigger('change');

                $('#dialog-league #optOddsportal').val(data.oddsportal).trigger('change');
                $('#dialog-league #optOddsportal').attr('readonly', true);

                readonly_select($(".select2"), true);
            }, 200);

            $('#dialog-league .modal-title').html('<i class="fa fa-trophy mr-5"></i>Edit League');
        }
        else {
            $('#dialog-league #txtLeagueID').val('');
            $('#dialog-league #txtMatches').val('34');
            $('#dialog-league #optCountry').select2("trigger", "select", { data: { id: "England" } });

            setTimeout(function(){
                $('#dialog-league #optSoccervista').val('').trigger('change');
                $('#dialog-league #optSoccerway').val('').trigger('change');
                $('#dialog-league #optSoccerbase').val('').trigger('change');
                $('#dialog-league #optPredictz').val('').trigger('change');
                $('#dialog-league #optWindrawwin').val('').trigger('change');

                $('#dialog-league #optOddsportal').val('').trigger('change');
                $('#dialog-league #optOddsportal').attr('readonly', false);
            }, 200);

            $('#dialog-league .modal-title').html('<i class="fa fa-trophy mr-5"></i>New League');
        }

        $('#dialog-league #frmData').validator('update');
        $('#dialog-league #frmData').validator().on('submit', function (e) {
            if (e.isDefaultPrevented()) {
                // handle the invalid form...
                console.log("prevented");
            }
            else {
                e.preventDefault();

                var formData = new FormData(this);
                formData.append('season', $('#optSeason').val());
                // Ajax
                Action.uploadRequest(
                    'manage/x_league_save',
                    formData,
                    function(resultData) {
                        $('#dialog-league .error-msg').html('<i class="fa fa-info-circle mr-10"></i>Successfully saved!');
                        $('#dialog-league .error-msg').addClass('success');

                        $('#dialog-league').modal('hide');
                        if(callback) { callback(); }
                    },
                    function(msg) {
                        $('#dialog-league .error-msg').removeClass('success');
                        $('#dialog-league .error-msg').html('<i class="fa fa-info-circle mr-10"></i>' + msg);
                    },
                    { block_element : $('#dialog-league .modal-dialog') }
                );
            }
        });

        $('#dialog-league .form-group').removeClass('has-error');
        $('#dialog-league .form-group').removeClass('has-danger');
        $('#dialog-league .error-msg').html('');

        $('#dialog-league').modal();
    };
});