$(document).ready(function(){
    $('#frmRegister').validator('update');

    $('#frmRegister').validator().on('submit', function (e) {
        if (e.isDefaultPrevented()) {
            // handle the invalid form...
        }
        else {
            e.preventDefault();

            Action.sendRequest(
                'user/x_update_info',
                $(this).serializeArray(),
                function(jsonData){
                    Dialog.commonPromptWith('', 'Successfully updated！', function(){
                        $('#txtCurPwd').val("");
                        $('#txtPassword').val("");
                        $('#txtRepeatPwd').val("");
                        $('#txtCaptcha').val("");
                        $('.captcha-image').click();
                    });
                },
                function(msg) {
                    showError('', msg);

                    $('#txtCaptcha').val("");
                    $('.captcha-image').click();
                },
                { block_element: $('.panel-default') }
            );
        }
    });

    $('.captcha-image').off().on('click', function(e){
        Action.sendRequest(
            'user/x_get_captcha',
            null,
            function(jsonData) {
                $('.captcha-image').html(jsonData.captcha_img);
            },
            null,
            { block_element: $('.inbox') }
        );
    });

    $('.captcha-image').click();


    $('.btn-close').off().on('click', function(e){
        e.preventDefault();

        Dialog.openConfirm("Are you sure to close your account?", function(){
            Action.sendRequest(
                'user/x_deactivate',
                $(this).serializeArray(),
                function(jsonData){
                    window.location.href = base_url + 'logout';
                },
                function(msg) {
                    showError('', msg);

                    $('#txtCaptcha').val("");
                    $('.captcha-image').click();
                },
                { block_element: $('.panel-default') }
            );
        });
    });
});