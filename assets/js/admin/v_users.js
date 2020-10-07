$(document).ready(function(){
    /////////////////////////////////////////////////////////
    // DataTable
    /////////////////////////////////////////////////////////
    var table = $('#tableList').dataTable({
        "serverSide": true,
        "ajax" : function(data, callback, settings) {
            Action.sendRequest(
                'admin/x_users',
                data,
                function(jsonData) {
                    callback(jsonData);
                    if(jsonData['others'] != undefined) {
                        var others = jsonData['others'];
                    }
                },
                null,
                { block_element: $(".dataTables_wrapper"), block_message: _LANGUAGE_MAPS_['LANG_3700'] }
            );
        },
        "dom": "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>t<'row'<'col-sm-6'i><'col-sm-6'p>>",
        "lengthMenu": [ [10, 20, 25, 50], [10, 20, 25, 50] ],
        "iDisplayLength": 25,
        "columns": [
            { "data": "index_no" },
            { "data": "uid" },
            { "data": "user_name" },
            { "data": "user_type" },
            { "data": "real_name" },
            { "data": "email" },
            { "data": "status" },
            { "data": "created_at" },
            { "data": "uid" }
        ],
        "columnDefs": [
            { "targets": [ 0 ], "searchable": false, "orderable": false },
            { "targets": [ 1 ], "searchable": true, "orderable": true },
            { "targets": [ 2 ], "searchable": true, "orderable": true },
            { "targets": [ 3 ], "searchable": true, "orderable": true },
            { "targets": [ 4 ], "searchable": true, "orderable": true },
            { "targets": [ 5 ], "searchable": true, "orderable": true },
            { "targets": [ 6 ], "searchable": true, "orderable": true },
            { "targets": [ 7 ], "searchable": true, "orderable": true },
            { "targets": [ 8 ], "searchable": false, "orderable": false },
        ],
        "order": [ [1, "asc"] ],
        "language" : { "url": base_url + "/assets/plugins/datatable/lang/english.json" },
        "createdRow": function( row, data, dataIndex ) {
            var style = 'cell-style';
            var _html = '';

            $('td', row).eq(0).html('<div class="' + style + '">' + Util.getString(data['index_no']) + '</div>');
            $('td', row).eq(1).html('<div class="' + style + '">' + Util.getString(data['uid']) + '</div>');
            $('td', row).eq(2).html('<div class="' + style + '">' + Util.getString(data['user_name']) + '</div>');

            if(Util.getString(data['user_type']) == '1') {
                _html = '<span class="color-red">Administrator</span>'
            }
            else {
                _html = '<span class="color-gray">Common User</span>'
            }
            $('td', row).eq(3).html('<div class="' + style + '">' + _html + '</div>');

            $('td', row).eq(4).html('<div class="' + style + '">' + Util.getString(data['real_name']) + '</div>');
            $('td', row).eq(5).html('<div class="' + style + '">' + Util.getString(data['email']) + '</div>');

            if(Util.getString(data['status']) == '1') {
                _html = '<i class="fa fa-toggle-on activated" data-toggle="tooltip" data-placement="top" data-original-title="Activate"></i>';
            }
            else {
                _html = '<i class="fa fa-toggle-off inactivate" data-toggle="tooltip" data-placement="top" data-original-title="Deactivate"></i>';
            }
            $('td', row).eq(6).html('<div class="actions-wrapper">' + _html + '</div>');

            $('td', row).eq(7).html('<div class="' + style + '">' + Util.getString(data['created_at']) + '</div>');
            $('td', row).eq(8).html('<button class="btn btn-sm btn-primary btnPassword" data-toggle="tooltip" data-placement="top" data-original-title="Change Password"><i class="fa fa-key"></i></button>');
        },
        responsive: {
            details: {
                renderer: function ( api, rowIdx, columns ) {
                    var rowData = table.fnGetData(rowIdx);
                    var data = $.map( columns, function ( col, i ) {
                        var colData = Util.getString(col.data);

                        var _html = "";
                        if(i == 3) {
                            if(colData == '1') {
                                _html = '<span class="color-red">Administrator</span>'
                            }
                            else {
                                _html = '<span class="color-gray">Common User</span>'
                            }
                        }
                        else if(i == 6) {
                            if(colData == '1') {
                                _html = '<i class="fa fa-toggle-on activated" data-toggle="tooltip" data-placement="top" data-original-title="未激用号"></i>';
                            }
                            else {
                                _html = '<i class="fa fa-toggle-off inactivate" data-toggle="tooltip" data-placement="top" data-original-title="激活用号"></i>';
                            }

                            _html = '<div class="actions-wrapper">' + _html + '</div>';
                        }
                        else if(i == 8) {
                            _html = '<button class="btn btn-sm btn-primary btnPassword" data-toggle="tooltip" data-placement="top" data-original-title="Change Password"><i class="fa fa-key"></i></button>';
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
            var api = this.api();

            setTimeout(function() {
                    App.resizeDataTable();
                    App.handleDataToolTip();
                }, 200
            );
        },
        "initComplete": function( settings, json ) {
            var api = this.api();

            $('#tableList_filter input')
                .off('.DT')
                .on('keyup.DT', function (e) { if (e.keyCode == App.KEYS.ENTER) { api.search(this.value).draw(); } });
        }
    });

    table.on( 'responsive-display.dt', function ( e, datatable, row, showHide, update ) {
        // console.log( 'Details for row '+row.index()+' '+(showHide ? 'shown' : 'hidden') );
        if(showHide) {
            App.handleDataToolTip();
        }
    });


    table.on('click', '.activated', function (e) {
        e.preventDefault();
        if($(this).hasClass('disabled')) return;

        var nRow = $(this).parents('tr')[0];
        var aData = table.fnGetData(nRow);

        Action.sendRequest(
            'admin/x_deactivate',
            { user_id : aData['uid'] },
            function(jsonData) {
                setTimeout(function() { App.reloadDataTable(table, false); }, 100);
            },
            null,
            { block_element: $('.dataTables_wrapper') }
        );
    });


    table.on('click', '.inactivate', function (e) {
        e.preventDefault();
        if($(this).hasClass('disabled')) return;

        var nRow = $(this).parents('tr')[0];
        var aData = table.fnGetData(nRow);

        Action.sendRequest(
            'admin/x_activate',
            { user_id : aData['uid'] },
            function(jsonData) {
                setTimeout(function() { App.reloadDataTable(table, false); }, 100);
            },
            null,
            { block_element: $('.dataTables_wrapper') }
        );
    });


    table.on('click', '.btnPassword', function (e) {
        e.preventDefault();

        var nRow = $(this).parents('tr')[0];
        var aData = table.fnGetData(nRow);

        Dialog.openUpdatePwd(function(newPassword){
            Action.sendRequest(
                'admin/x_upt_pwd',
                { uid : aData['uid'], password:newPassword  },
                function(jsonData) {

                },
                null,
                null,
                false
            );
        });
    });

    Dialog.openUpdatePwd = function(callback_on_confirm) {
        $('.frmPassword').trigger('reset');

        $('#dialog-upt-pwd .frmPassword').validator('update');
        $('#dialog-upt-pwd .frmPassword').validator().on('submit', function (e) {
            if (e.isDefaultPrevented()) {
                // handle the invalid form...
            }
            else {
                e.preventDefault();

                callback_on_confirm($('#dialog-upt-pwd #txtPassword').val());
                $('#dialog-upt-pwd').modal('hide');
            }
        });

        $('.image-box .image-action .fa-trash').off().on('click', function(e){
            $(this).closest('.image-data-container').remove();
        });

        $('#dialog-upt-pwd').modal();
    };
});