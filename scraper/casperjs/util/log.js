var fs = require('fs');


exports.logToFile = function(message) {
    try {
        fs.write('log.txt', message + "\r\n", 'a');
    } catch(e) {
        console.log(e);
    }
};