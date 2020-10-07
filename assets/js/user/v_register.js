$(document).ready(function(){
    $('#frmRegister').validator('update');

    $('#frmRegister').validator().on('submit', function (e) {
        if (e.isDefaultPrevented()) {
            // handle the invalid form...
        }
        else {
            e.preventDefault();

            Action.sendRequest(
                'user/x_register',
                $(this).serializeArray(),
                function(jsonData){
                    Dialog.commonPromptWith('', 'Successfully created new account!', function(){
                        window.location.href = base_url + 'login';
                    });
                },
                function(msg) {
                    showError('', msg);

                    $('#txtCaptcha').val("");
                    $('.captcha-image').click();
                },
                { block_element: $('.inbox') }
            )
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
});