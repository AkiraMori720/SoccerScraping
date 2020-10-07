var UiFeatureNotifications = function () {

    var availableAnims = [
        "bounce", "flash", "pulse", "rubberBand", "shake", "swing", "tada", "wobble",
        "bounceIn", "bounceInDown", "bounceInLeft", "bounceInRight", "bounceInUp",
        "bounceOut", "bounceOutDown", "bounceOutLeft", "bounceOutRight", "bounceOutUp",
        "fadeIn", "fadeInDown", "fadeInDownBig", "fadeInLeft", "fadeInLeftBig", "fadeInRight", "fadeInRightBig", "fadeInUp", "fadeInUpBig",
        "fadeOut", "fadeOutDown", "fadeOutDownBig", "fadeOutLeft", "fadeOutLeftBig", "fadeOutRight", "fadeOutRightBig", "fadeOutUp", "fadeOutUpBig",
        "flip", "flipInX", "flipInY", "flipOutX", "flipOutY",
        "lightSpeedIn", "lightSpeedOut",
        "rotateIn", "rotateInDownLeft", "rotateInDownRight", "rotateInUpLeft", "rotateInUpRight",
        "rotateOut", "rotateOutDownLeft", "rotateOutDownRight", "rotateOutUpLeft", "rotateOutUpRight",
        "hinge", "rollIn", "rollOut",
        "zoomIn", "zoomInDown", "zoomInLeft", "zoomInRight", "zoomInUp",
        "zoomOut", "zoomOutDown", "zoomOutLeft", "zoomOutRight", "zoomOutUp"
    ];

    return {

        // =========================================================================
        // CONSTRUCTOR APP
        // =========================================================================
        init: function () {
            UiFeatureNotifications.initNoty();
        },

        // =========================================================================
        // INIT NOTY
        // =========================================================================
        initNoty: function () {

        },

        /**
         * Layout : top, topLeft, topRight, centerLeft, center, centerRight, bottomLeft, bottomCenter, bottomRight, bottom, inline
         * Type : alert, success, error, warning, information, confirm
         *
         * @param layout
         * @param type
         * @param message
         * @returns {boolean}
         */
        showNoty: function(layout, type, message, animOpen, animClose) {

            if(availableAnims.indexOf(animOpen) == -1) {
                animOpen = 'flipInX';
            }

            if(availableAnims.indexOf(animClose) == -1) {
                animClose = 'flipOutX';
            }

            var notifyObj = noty({
                text        : message,
                type        : type,
                theme       : 'relax',
                dismissQueue: true,
                layout      : layout,
                animation   : {
                    open  : 'animated ' + animOpen,
                    close : 'animated ' + animClose
                },
                buttons     : (type != 'confirm') ? false : [
                    {addClass: 'btn btn-primary', text: 'Ok', onClick: function ($noty) {

                        // this = button element
                        // $noty = $noty element

                        $noty.close();
                        // noty({force: true, theme: 'relax', animation: {
                        //     open  : 'animated ' + animOpen,
                        //     close : 'animated ' + animClose
                        // }, text: 'You clicked "Ok" button', type: 'success', layout: layout});
                    }
                    },
                    {addClass: 'btn btn-danger', text: 'Cancel', onClick: function ($noty) {
                        $noty.close();
                        // noty({force: true, theme: 'relax', animation: {
                        //     open  : 'animated bounceIn',
                        //     close : 'animated bounceOut'
                        // }, text: 'You clicked "Cancel" button', type: 'error', layout: layout});
                    }
                    }
                ]
            });

            if(type != 'confirm') {
                setTimeout(function () {
                    notifyObj.close();
                }, 5000);
            }
        }
    };

}();

// Call main app init
UiFeatureNotifications.init();