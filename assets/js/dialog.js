var Dialog = Dialog || {};

var Dialog = {
    init: function() {

    },

    /**
     * =====================================
     *  Dialog to prompt messages
     * =====================================
     *
     * @param title
     * @param message
     * @param callback_on_close
     */
    commonPromptWith : function(title, message, callback_on_close) {
        if(title != null && title.length > 0) {
            $('#dialog-prompt .modal-title').html(title);
        }

        $('#dialog-prompt .modal-body p').empty();
        $('#dialog-prompt .modal-body p').html(message);

        $("#dialog-prompt .modal-body").animate({ scrollTop: 0 }, "slow");

        $('#dialog-prompt').on('hidden.bs.modal', function () {
            if(callback_on_close != null) { setTimeout(function(){callback_on_close();}, 200); }
        });

        $('#dialog-prompt').modal();
    },

    openConfirm: function (message, callback_on_confirm) {
        $('#dialog-confirm .modal-body p').html(message);

        $('#dialog-confirm .btn-confirm').off().on('click', function() {
            if(callback_on_confirm != null) { callback_on_confirm(); }
        });

        $('#dialog-confirm').modal();
    },

	openLanguage: function(curLang, callback) {
		$('#dialog-language #txtCurURL').val(window.location.href);
		$('#optLangEN').prop('checked', false);
		$('#optLangCN').prop('checked', false);

		if(curLang == 'english') {
			$('#optLangEN').prop('checked', true);
		}
		else if(curLang == 'chinese') {
			$('#optLangCN').prop('checked', true);
		}

		$('#dialog-language .btn-primary').on('click', function () {
			var frmLang = $('#frmLang');
			frmLang.attr("method", "post");
			var serializedData = frmLang.serializeArray();

			if(serializedData[1].value != curLang) { frmLang.submit(); }
			else if(callback) {
				callback();
			}
		});

		$('#dialog-language').modal();
	}
};

Dialog.init();
