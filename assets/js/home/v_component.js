$(document).ready(function(){
    $('.btn-success').off().on('click', function(e){
        new Noty({
		    theme : 'relax',
		    type: 'success',
		    layout: 'bottomRight',
		    timeout: 3000,
		    text: "Your payment processed successfully!",
		    progressBar: true,
		    animation: {
		        open: 'animated bounceInRight',
		        close: 'animated bounceOutRight'
		    }
		}).show();
    });

    $('.btn-warning').off().on('click', function(e){
		new Noty({
			theme : 'relax',
			type: 'error',
			layout: 'bottomRight',
			timeout: 3000,
			text: "Failed to process your payment!",
			progressBar: true,
			animation: {
				open: 'animated bounceInRight',
				close: 'animated bounceOutRight'
			}
		}).show();
    });

    /////////////////////////////////////////////////////////
    // DataTable
    /////////////////////////////////////////////////////////
    let table = $('#tableList').dataTable({
        "serverSide": false,
        // "dom": "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>t<'row'<'col-sm-6'i><'col-sm-6'p>>",
        "dom": "<'row'<'col-sm-4'><'col-sm-4 text-center'B><'col-sm-4'>>t<'row mt-20'<'col-sm-4'i><'col-sm-8'<'page-num'><'page-size'l>p>>",
        "lengthMenu": [ [10, 20, 30, 50], [10, 20, 30, 50] ],
        "iDisplayLength": 20,
        "order": [ [1, "asc"] ],
        // "language" : { "url": base_url + "/assets/library/datatable/lang/" + UI_LANGUAGE + ".json" },
        "language" : {
            "emptyTable":     "No data",
            "loadingRecords": "Loading...",
            paginate : {
                previous : "<i class='fa fa-angle-left'></i>",
                next : "<i class='fa fa-angle-right'></i>"
            },
            lengthMenu: '<select class="form-control">'+
                '<option value="10">10 entries</option>'+
                '<option value="20">20 entries</option>'+
                '<option value="30">30 entries</option>'+
                '<option value="40">40 entries</option>'+
                '<option value="50">50 entries</option>'+
                '</select>'
        },
        responsive: {
            details: {
                renderer: function ( api, rowIdx, columns ) {
                    let rowData = table.fnGetData(rowIdx);
                    let data = $.map( columns, function ( col, i ) {
                        let colData = Util.getString(col.data);

                        let _html = "";
                        {
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

            let pageInfo = api.table().page.info();

            $('.page-num').html('<input type="number" class="form-control txtPageNum" step="1" min="1" max="' + pageInfo.pages + '" value="' + (api.page() + 1) + '" /> Page');

            $('.page-num .txtPageNum').keyup(function(e){
                if(e.keyCode == App.KEYS.ENTER) {
                    let pageNum = $(this).val();
                    api.table().page(parseInt(pageNum) - 1).draw('page');
                };
            });

            setTimeout(function() {
                    App.resizeDataTable();
                    App.handleDataToolTip();
                }, 200
            );
        },
        "initComplete": function( settings, json ) {
            let api = this.api();

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
});
