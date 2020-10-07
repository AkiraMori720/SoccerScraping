/**
 * Created by Administrator on 5/16/2017.
 */

$(document).ready(function () {
    $('#frmLogin').validator('update');

    $('#frmLogin').validator().on('submit', function (e) {
        if (e.isDefaultPrevented()) {
            // handle the invalid form...
        }
        else {
            e.preventDefault();

            Action.sendRequest(
                'user/_login',
                $('#frmLogin').serialize(),
                function success(response) { window.location.href = base_url + response.redirect_url; },
                null,
                { block_element : $(".inbox"), block_message : _LANGUAGE_MAPS_['LANG_6108'] }
            );
        }
    });

    $('#frmLogin .btn-login').click(function() {
        e.preventDefault();
        $('#frmLogin').submit();
    });

    $('#txtPassword').unbind();
    $('#txtPassword').bind('keyup', function(e) {
        if(e.keyCode == App.KEYS.ENTER) { $('#frmLogin .btn-login').click(); }
    });
});