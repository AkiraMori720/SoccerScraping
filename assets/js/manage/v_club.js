$(document).ready(function(){
    let _gAllClubs_ = null;
    //////////////////////////////////////////////
    // Data
    //////////////////////////////////////////////
    let tableData = $('#tableData').dataTable({
        "serverSide": true,
        "ajax" : function(data, callback, settings) {
            Action.sendRequest(
                'manage/x_club_list',
                $.extend(data, {season: $('#optSeason').val(), country : $('#optCountry').val(), league : $('#optLeague').val()}),
                function(jsonData) {
                    callback(jsonData);
                    if(jsonData['clubs'] != undefined) {
                        _gAllClubs_ = jsonData['clubs'];
                    }
                },
                null,
                null,
                false
            );
        },
        "dom": "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>t<'row'<'col-sm-6'i><'col-sm-6'p>>",
        "lengthMenu": [ [10, 20, 30, 50], [10, 20, 30, 50] ],
        "iDisplayLength": 20,
        "columns": [
            { "data": "index_no" },
            { "data": "oddsportal" },
            { "data": "soccervista" },
            { "data": "soccerway" },
            { "data": "predictz" },
            { "data": "windrawwin" },
            { "data": "id" },
        ],
        "columnDefs": [
            { "targets": [ 0, 6 ], "searchable": false, "orderable": false },
        ],
        "order": [ [1, "asc"] ],
        "language" : { "url": base_url + "/assets/library/datatable/lang/english.json" },
        "createdRow": function( row, data, dataIndex ) {
            let style = 'cell-style';
            let _html = '';

            $('td', row).eq(0).html('<div class="' + style + '">' + Util.getString(data['index_no']) + '</div>');

            _html = '<button class="btn btn-sm btn-warning btnEdit" data-toggle="tooltip" data-placement="top" data-original-title="Edit"><i class="fa fa-pencil"></i></button>';
            _html+= '<button class="btn btn-sm btn-danger btnDelete ml-5" data-toggle="tooltip" data-placement="top" data-original-title="Delete"><i class="fa fa-trash"></i></button>';
            $('td', row).eq(6).html(_html);
        },
        responsive: {
            details: {
                renderer: function ( api, rowIdx, columns ) {
                    let rowData = tableData.fnGetData(rowIdx);
                    let data = $.map( columns, function ( col, i ) {
                        let colData = Util.getString(col.data);

                        let _html = "";
                        if(i == 6) {
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
                'manage/x_club_del',
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

        Dialog.openClub(aData, function(){
            setTimeout(function() { App.reloadDataTable(tableData, false); }, 500);
        });
    });

    $('.btnAddNew').off().on('click', function(e){
        e.preventDefault();

        Dialog.openClub(null, function(){
            setTimeout(function() { App.reloadDataTable(tableData, false); }, 500);
        });
    });

    $("#optCountry").select2({
        placeholder: 'Select country',
        allowClear: false,
        templateResult: function (state) {
            if (!state.id || state.id.length == 0) { return state.text; }
            let $state = $("<span class='fifa-flag " + state.text.toLowerCase() + "'></span><span style='margin-left:10px'>" + state.text + "</span>");
            return $state;
        },
        templateSelection: function(selection) {
            if(selection.selected) {
                return $.parseHTML("<span class='ml-5 mt-2 fifa-flag " + selection.text.toLowerCase() + "'></span><span class='ml-5'>" + selection.text + "</span>");
            }
            else {
                return $.parseHTML('<span class="">' + selection.text + '</span>');
            }
        }
    });

    $(".select2-container").prop("style", "width:100% !important;");

    $('#optCountry').on("select2:select", function(event) {
        let season = $('#optSeason').val().trim();
        let value = $(event.currentTarget).find("option:selected").val().trim();

        let leagues = _gAllLeagues_[season][value];
        let html = "";
        $.each(leagues, function (idx, league) {
            html += '<option value="' + league + '">' + league + '</option>';
        });
        $('#optLeague').html(html);

        $('#optLeague').val(leagues[0]);

        setTimeout(function(){
            $('#optLeague').trigger('change');
        }, 100);
    });

    $('#optLeague').change(function(e){
        let val = $(this).val();
        if(val != null && val.length > 0) {
            App.reloadDataTable(tableData, true);
        }
    });

    $('#optSeason').change(function(e){
        let val = $(this).val();
        if(val.length > 0) {
            let _html = '';
            let count = 0;
            let activeCountry = '';
            $.each(_gAllLeagues_[val], function(country, leagues){
                _html += '<option value="' + country + '">' + country + '</option>';

                if(count == 0) {
                    activeCountry = country;
                }
                count ++;
            });
            $('#optCountry').html(_html);

            $('#optCountry').val(activeCountry).trigger("select2:select");
        }
    });

    let arrSites = ['oddsportal', 'soccervista', 'soccerway', 'predictz', 'windrawwin'];
    $('#optOddsportal, #optSoccervista, #optSoccerway, #optPredictz, #optWindrawwin').change(function(){
        let curSite = $(this).prop('id').split('opt').join('').toLowerCase();

        let val = $(this).val().toLowerCase();
        if(val.length > 0) {
            $.each(arrSites, function (idx, site) {
                if(site != curSite) {
                    let optList = $('#dialog-club #opt' + Util.ucwords(site) + ' > option');
                    for (let k = 0; k < optList.length; k++) {
                        let optVal = $(optList[k]).attr('value').toLowerCase();

                        if(optVal.length == 0) {
                            continue;
                        }

                        if (val == optVal || val.indexOf(optVal) != -1 || optVal.indexOf(val) != -1) {
                            $('#dialog-club #opt' + Util.ucwords(site)).val($(optList[k]).attr('value')
                            );
                            break;
                        }
                    }
                }
            });
        }
    });

    Dialog.openClub = function(data, callback) {
        $('#dialog-club #frmData').trigger('reset');

        $.each(arrSites, function(idx, site){
            let _html = '<option value="">Select</option>';
            if(_gAllClubs_ != null && _gAllClubs_[site] != undefined && _gAllClubs_[site] != null) {
                $.each(_gAllClubs_[site], function(idx, clubName){
                    _html += '<option value="' + clubName + '">' + clubName + '</option>';
                });
            }

            $('#dialog-club #opt' + Util.ucwords(site)).html(_html);
        });

        if(data != null) {
            $('#dialog-club #txtClubID').val(data.id);

            setTimeout(function(){
                $('#dialog-club #optSoccervista').val(data.soccervista).trigger('change');
                $('#dialog-club #optSoccerway').val(data.soccerway).trigger('change');
                $('#dialog-club #optSoccerbase').val(data.soccerbase).trigger('change');
                $('#dialog-club #optPredictz').val(data.predictz).trigger('change');
                $('#dialog-club #optWindrawwin').val(data.windrawwin).trigger('change');

                $('#dialog-club #optOddsportal').val(data.oddsportal).trigger('change');
                $('#dialog-club #optOddsportal').attr('readonly', true);

            }, 200);

            $('#dialog-club .modal-title').html('<i class="fa fa-group mr-5"></i>Edit Club');
        }
        else {
            $('#dialog-club #txtClubID').val('');

            setTimeout(function(){
                $('#dialog-club #optSoccervista').val('').trigger('change');
                $('#dialog-club #optSoccerway').val('').trigger('change');
                $('#dialog-club #optSoccerbase').val('').trigger('change');
                $('#dialog-club #optPredictz').val('').trigger('change');
                $('#dialog-club #optWindrawwin').val('').trigger('change');

                $('#dialog-club #optOddsportal').val('').trigger('change');
                $('#dialog-club #optOddsportal').attr('readonly', false);
            }, 200);

            $('#dialog-club .modal-title').html('<i class="fa fa-group mr-5"></i>New Club');
        }

        $('#dialog-club #frmData').validator('update');
        $('#dialog-club #frmData').validator().on('submit', function (e) {
            if (e.isDefaultPrevented()) {
                // handle the invalid form...
                console.log("prevented");
            }
            else {
                e.preventDefault();

                var formData = new FormData(this);
                formData.append('season', $('#optSeason').val());
                formData.append('country', $('#optCountry').val());

                // Ajax
                Action.uploadRequest(
                    'manage/x_club_save',
                    formData,
                    function(resultData) {
                        $('#dialog-club .error-msg').html('<i class="fa fa-info-circle mr-10"></i>Successfully saved!');
                        $('#dialog-club .error-msg').addClass('success');

                        $('#dialog-club').modal('hide');
                        if(callback) { callback(); }
                    },
                    function(msg) {
                        $('#dialog-club .error-msg').removeClass('success');
                        $('#dialog-club .error-msg').html('<i class="fa fa-info-circle mr-10"></i>' + msg);
                    },
                    { block_element : $('#dialog-club .modal-dialog') }
                );
            }
        });

        $('#dialog-club .form-group').removeClass('has-error');
        $('#dialog-club .form-group').removeClass('has-danger');
        $('#dialog-club .error-msg').html('');

        $('#dialog-club').modal();
    };
});