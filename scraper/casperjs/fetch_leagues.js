var browser = require('util/browser');
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
        height: 10080
    }
});

function exitExecution() {
    casper.clear();
    casper.wait(100);
    casper.exit();
}

var countryList = '';
var siteName = '';
var siteLink = '';
for(var i = 0; i < casper.cli.args.length; i++) {
    var tmp = casper.cli.args[i].split("=");

    if(tmp.length > 1 && tmp[0] == 'country' && tmp[1].length > 0) {
        countryList = tmp[1].split(',');
    }

    if(tmp.length > 1 && tmp[0] == 'site' && tmp[1].length > 0) {
        siteName = tmp[1].trim();
    }

    if(tmp.length > 1 && tmp[0] == 'link' && tmp[1].length > 0) {
        siteLink = tmp[1].trim();
    }
}

var coreEngine = require('core/leagues_' + siteName + '.js');

casper.start(siteLink, function(){
    coreEngine.executeScrape(countryList, function(data){
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