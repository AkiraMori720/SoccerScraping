var browser = require('util/browser');
var predictz = require('core/predictz.js');

function getRandomIntFromRange(min, max) {
    return Math.round(Math.random() * (max - min)) + min;
}

var casper = require('casper').create({
    verbose: false,
    logLevel: "info",
    pageSettings: {
        userAgent: browser.agents[getRandomIntFromRange(0, browser.agents.length - 1)],
        loadImages:  false
    },
    viewportSize: {
        width: 1920,
        height: 1080
    }
});

function exitExecution() {
    casper.clear();
    casper.wait(100);
    casper.exit();
}

var countryList = '';
var curDate = null; // Like 2020-05-23
for(var i = 0; i < casper.cli.args.length; i++) {
    var tmp = casper.cli.args[i].split("=");
    if(tmp.length > 1 && tmp[0] == 'date' && tmp[1].length > 0) {
        curDate = tmp[1];
    }

    if(tmp.length > 1 && tmp[0] == 'country' && tmp[1].length > 0) {
        countryList = tmp[1].split(',');
    }
}

casper.start("https://www.predictz.com/predictions/", function(){
    predictz.executeScrape(casper, curDate.split('-').join(''), countryList, function(data){
        if(data != undefined && data != null) {
            casper.echo(JSON.stringify(data));
        }

        exitExecution();
    });
});

casper.run();

// casper.on('run.complete', function() {
//     this.exit();
// });