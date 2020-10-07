var Util = Util || {};

Util = {

    /**
     *
     * @param imgEle
     * @param fileData
     */
    showImageFromFile: function (imgEle, fileData)
    {
        if (!window.File || !window.FileReader || !window.FileList || !window.Blob) {
            alert('The File APIs are not fully supported in this browser.');
            return;
        }

        if (fileData == null) {
            alert("Please select a file to import");
        }
        else {
            var file = fileData;
            var fr = new FileReader();
            fr.onload = function() {
                var imgData = fr.result;

                $(imgEle).prop('src', imgData);
            };
            fr.readAsDataURL(file);
        }
    },

    /**
     *
     * @param filePath
     * @returns {boolean}
     */
    isJPEG: function(filePath)
    {
        var type = Util.getExtension(filePath);

        if(type === "jpg" || type === "jpeg"){
            return true;
        } else {
            return false;
        }
    },

	isPNG: function(filePath)
	{
		var type = Util.getExtension(filePath);

		if(type === "png"){
			return true;
		} else {
			return false;
		}
	},

    isExcel: function(filePath)
    {
        var type = Util.getExtension(filePath);

        if(type === "xls" || type === "xlsx"){
            return true;
        } else {
            return false;
        }
    },

    isCSV: function(filePath)
    {
        var type = Util.getExtension(filePath);

        if(type === "csv"){
            return true;
        } else {
            return false;
        }
    },

	getFileName: function(filePath) {
		return filePath.split('.').shift();
	},

	getExtension: function(filePath) {
		return filePath.split('.').pop().toLowerCase();
	},

    getString : function (value) {
		if(value == null || value == undefined) {
			return "";
		}

		return "" + value;
	},

    ucwords : function  (str) {
        return (str + '').replace(/^([a-z])|\s+([a-z])/g, function ($1) {
            return $1.toUpperCase();
        });
    }
};
