var Action = Action || {};

var Action = {
    gotoBack : function() {
        window.history.back();
    },

    gotoIndex : function() {
        window.location.href = base_url;
    },

    downloadFile : function(file, name, bDelete) {
        window.location.href = base_url + "common/download?file=" + file + "&name=" + encodeURIComponent(name) + "&delete=" + bDelete;
    },

    /**
     *
     * @param action
     * @param postData
     * @param callback
     * @param cbFailed
     * @param blockUI
     * @param bShowBlockUI
     */
    sendRequest : function(action, postData, callback, cbFailed, blockUI, bShowBlockUI) {
        this.postData(
            false,
            base_url + action,
            postData,
            function(jsonData) // Success Callback
            {
                if(callback) {
                    callback(jsonData);
                }
            },
            function(field, error) // Fail Callback
            {
                if(cbFailed) { cbFailed(error); }
                else {
                    showError(field, error);
                }
            },
            null,
            blockUI,
            bShowBlockUI
        );
    },

    /**
     *
     * @param action
     * @param postData
     * @param callback
     * @param cbFailed
     * @param blockUI
     * @param bShowBlockUI
     */
    uploadRequest : function(action, postData, callback, cbFailed, blockUI, bShowBlockUI) {
        this.postData(
            true,
            base_url + action,
            postData,
            function(jsonData) // Success Callback
            {
                if(callback) {
                    callback(jsonData);
                }
            },
            function(field, error) // Fail Callback
            {
                if(cbFailed) { cbFailed(error); }
                else {
                    showError(field, error);
                }
            },
            null,
            blockUI,
            bShowBlockUI
        );
    },


    /**
     * =====================================================
     *  Execute Ajax Post Request
     * =====================================================
     *
     * @param bUpload       - If file upload, then true
     * @param url           - request URL
     * @param data          - postData
     * @param cbSuccess     - success callback
     * @param cbFail        - fail callback
     * @param cbAlways      - always callback
     * @param blockUIInf    - block ui setting
     * @param bShowBlockUI  - Visible / Invisible block UI
     */
    postData:function (bUpload, url, data, cbSuccess, cbFail, cbAlways, blockUIInf, bShowBlockUI) {
        var blockUI = null;

        if(bShowBlockUI == null) { bShowBlockUI = true; }

        if(blockUIInf == null) {
            blockUIInf = { block_element : $("body"), block_message : _LANGUAGE_MAPS_['LANG_C_MSG_PROCESSING'] }
        }

        if(blockUIInf) {
            blockUI = blockUIInf['block_element'];
            if(blockUI != null && bShowBlockUI) {
                $("body").addClass('page-100vh');

                var blockMsg = blockUIInf['block_message'] != null ? blockUIInf['block_message'] : _LANGUAGE_MAPS_['LANG_3701'];
                blockUI.block({message: App.getBlockUIHTML(blockMsg)});
            }
        }

        setTimeout(function(){
            var ajaxReq = null;

            if(bUpload) {
                ajaxReq = $.ajax({
                    url : url,
                    type: "post",
                    contentType: false,
                    processData: false,
                    data: data
                });
            }
            else {
                ajaxReq = $.ajax({
                    url : url,
                    type: "post",
                    data: data
                });
            }


            // Callback handler that will be called on success
            ajaxReq.done(function (response, textStatus, jqXHR){
                try {
                    var result = $.parseJSON(response);

                    // If Success
                    if(result.code == RESULT_CODE_SUCCESS) {
                        if (cbSuccess != undefined) {
                            cbSuccess(result.data);
                        }
                    }
                    // If Error
                    else {
                        if(cbFail != undefined) cbFail(result.data.field ? result.data.field : '', result.msg);
                    }
                }
                catch(e) {
                    if(cbFail != undefined) cbFail('', e.toString());
                }
            });

            // Callback handler that will be called on failure
            ajaxReq.fail(function (jqXHR, textStatus, errorThrown){
                var errorMsg = "";
                try {
                    errorMsg = $.parseJSON(jqXHR.responseText).msg;
                }
                catch (e) { errorMsg = jqXHR.responseText; }
                if(cbFail != undefined) cbFail('', errorMsg);
            });

            // if the request failed or succeeded
            ajaxReq.always(function () {
                $("body").removeClass('page-100vh');
                if(blockUI != null) $(blockUI).unblock();

                if(cbAlways != undefined) cbAlways();
            });
        }, 200);
    }
};

function showError(field, msg) {
    Dialog.commonPromptWith('', msg);
}