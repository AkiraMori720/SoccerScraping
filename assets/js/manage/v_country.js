$(document).ready(function(){
    //////////////////////////////////////////////
    // Data
    //////////////////////////////////////////////
    let tableData = $('#tableData').dataTable({
        "serverSide": true,
        "ajax" : function(data, callback, settings) {
            Action.sendRequest(
                'manage/x_country_list',
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
            { "data": "iso2_code" },
            { "data": "oddsportal" },
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

            _html = '<span class="mr-5 fifa-flag ' + data['oddsportal'].toLowerCase() + '"></span>';
            _html+= '<span class="">' + Util.getString(data['oddsportal']) + '</span>';
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
                'manage/x_country_del',
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

        Dialog.openCountry(aData, function(){
            setTimeout(function() { App.reloadDataTable(tableData, false); }, 500);
        });
    });

    $('.btnAddNew').off().on('click', function(e){
        e.preventDefault();

        Dialog.openCountry(null, function(){
            setTimeout(function() { App.reloadDataTable(tableData, false); }, 500);
        });
    });

    $('.btnAddPrev').off().on('click', function(e){
        e.preventDefault();

        Action.sendRequest(
            'manage/x_country_import_prev',
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

    Dialog.openCountry = function(data, callback) {
        $('#dialog-country #frmData').trigger('reset');

        if(data != null) {
            $('#dialog-country #txtCountryID').val(data.id);
            $('#dialog-country #txtCountry').val(data.country);
            $('#dialog-country #txtCountry').attr('readonly', true);

            $('#dialog-country #txtIso2').val(data.iso2_code);
            $('#dialog-country #txtOddsportal').val(data.oddsportal);
            $('#dialog-country #txtSoccerVista').val(data.soccervista);
            $('#dialog-country #txtSoccerWay').val(data.soccerway);
            $('#dialog-country #txtSoccerBase').val(data.soccerbase);
            $('#dialog-country #txtPredictz').val(data.predictz);
            $('#dialog-country #txtWindrawwin').val(data.windrawwin);

            $('#dialog-country .modal-title').html('<i class="fa fa-flag mr-5"></i>Edit Country');
        }
        else {
            $('#dialog-country #txtCountry').attr('readonly', false);
            $('#dialog-country #txtCountryID').val('');
            $('#dialog-country #txtIso2').val('');
            $('#dialog-country #txtOddsportal').val('');
            $('#dialog-country #txtSoccerVista').val('');
            $('#dialog-country #txtSoccerWay').val('');
            $('#dialog-country #txtSoccerBase').val('');
            $('#dialog-country #txtPredictz').val('');
            $('#dialog-country #txtWindrawwin').val('');

            $('#dialog-country .modal-title').html('<i class="fa fa-flag mr-5"></i>New Country');
        }

        $('#dialog-country #frmData').validator('update');
        $('#dialog-country #frmData').validator().on('submit', function (e) {
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
                    'manage/x_country_save',
                    formData,
                    function(resultData) {
                        $('#dialog-country .error-msg').html('<i class="fa fa-info-circle mr-10"></i>Successfully saved!');
                        $('#dialog-country .error-msg').addClass('success');

                        $('#dialog-country').modal('hide');
                        if(callback) { callback(); }
                    },
                    function(msg) {
                        $('#dialog-country .error-msg').removeClass('success');
                        $('#dialog-country .error-msg').html('<i class="fa fa-info-circle mr-10"></i>' + msg);
                    },
                    { block_element : $('#dialog-country .modal-dialog') }
                );
            }
        });

        $('#dialog-country .form-group').removeClass('has-error');
        $('#dialog-country .form-group').removeClass('has-danger');
        $('#dialog-country .error-msg').html('');

        $('#dialog-country').modal();
    };
});