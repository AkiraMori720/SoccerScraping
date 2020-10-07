var App = App || {};
////////////////////////////////////////////////////////////////////////////////

App = {
    KEYS : {
        BACKSPACE: 8,
        TAB: 9,
        ENTER: 13,
        SHIFT: 16,
        CTRL: 17,
        ALT: 18,
        ESC: 27,
        SPACE: 32,
        PAGE_UP: 33,
        PAGE_DOWN: 34,
        END: 35,
        HOME: 36,
        LEFT: 37,
        UP: 38,
        RIGHT: 39,
        DOWN: 40,
        DELETE: 46
    },

    lastLoginTime: new Date().getTime(),
    bLoggedIn : false,

    init: function () {
        (function($) {
            $.fn.hasScrollBar = function() {
                return this.get(0) ? this.get(0).scrollHeight > this.innerHeight() : false;
            }
        })(jQuery);

        (function() {
            'use strict';
            window.addEventListener('load', function() {
                // Fetch all the forms we want to apply custom Bootstrap validation styles to
                var forms = document.getElementsByClassName('needs-validation');
                // Loop over them and prevent submission
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();

        App.handleBaseURL();
        App.handleBackToTop();
        App.handleDataToolTip();
        App.handleWindowResize();

        App.handleLanguage();

        window.onfocus = function () {
		    let curTime = new Date().getTime();

		    if(curTime - App.lastLoginTime >= 3600 * 1000 || !App.bLoggedIn) {
                Action.sendRequest(
                    'user/x_check_login',
                    {},
                    function(responseData) {
                        App.bLoggedIn = responseData.login;
                        if(responseData.login) {
                            App.lastLoginTime = curTime;
                        }

                        let ignoreLinks = [
                            myTrim(base_url),
                            base_url + 'user/login',
                            base_url + 'user/register',
                            base_url + 'user/forgot'
                        ];

                        let curLink = myTrim(window.location.href);
                        if(!responseData.login && $.inArray(curLink, ignoreLinks) < 0 ) {
                            console.log('redirecting to login...');
                            window.location.href = base_url + 'user/login';
                        }
                        else if(responseData.login && $.inArray(curLink, ignoreLinks) >= 0) {
                            window.location.href = base_url + 'home/index';
                        }

                        function myTrim(x) {
                            return x.replace(/^[\s\/#]+|[\s\/#]+$/gm,'');
                        }

                    },
                    null,
                    null,
                    false
                );
            }
		};
	},

	handleLanguage: function() {
		$('.current-language').off().on('click', function(e){
			Dialog.openLanguage(UI_LANGUAGE);
		});
	},

    // ==================== SET UP BASE URL =======================
    handleBaseURL: function () {
        var getUrl = window.location,
            baseUrl = getUrl .protocol + "//" + getUrl.host + "/" + getUrl.pathname.split('/')[1];
        return baseUrl;
    },

    // ==================== BACK TOP =======================
    handleBackToTop: function () {
        $(window).scroll(function () {
            if ($(this).scrollTop() > 80) {
                $('#back-top').addClass('show');
            } else {
                $('#back-top').removeClass('show');
            }
        });
        // scroll body to 0px on click
        $('#back-top').off().on('click', function () {
            $('html, body').animate({ scrollTop: 0 }, 500);
        });
    },

    handleDataToolTip: function() {
        $('[data-toggle="tooltip"]').tooltip();
    },

    handleWindowResize: function() {
        $(window).resize(function(){
            App.handleDataToolTip();
        });
    },

    getBlockUIHTML : function (msg) {
        return '<img src="' + base_url + 'assets/img/loading-spinner-blue.gif"/><span>' + msg + '</span>';
    },

    resizeDataTable: function() {
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust().responsive.recalc();
    },

    reloadDataTable: function(oTable, bReloadAll) {
        if(oTable !== undefined) {
            if(bReloadAll == undefined) { bReloadAll = true; }
            oTable.api().ajax.reload(null, bReloadAll);
        }
    },

    openPDF: function(fileLink) {
        // $('#pdf_open').prop('href', base_url + fileLink);
        // $('#pdf_open').removeClass('hidden');
        // setTimeout(function(){$('#pdf_open').click();}, 500);
        // setTimeout(function(){ $('#pdf_open').addClass('hidden') }, 1000);
    }
};

// Call main app init
App.init();
