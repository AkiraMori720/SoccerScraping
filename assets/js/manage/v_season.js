$(document).ready(function(){
    $(document).ready(function(){
        $('.btnSave').off().on('click', function(e){
            e.preventDefault();

            Action.sendRequest(
                'manage/x_season_select',
                {season : $('input[type=radio]:checked').val()},
                function(resData){},
                null,
                null,
                false
            );
        });
    });
});