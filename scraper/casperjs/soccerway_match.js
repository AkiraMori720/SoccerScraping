var soccerway_detail = require('core/soccerway_detail.js');
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
        height: 1080
    }
});

function exitExecution() {
    casper.clear();
    casper.wait(100);
    casper.exit();
}

var curLink = "";
for(var i = 0; i < casper.cli.args.length; i++) {
    var tmp = casper.cli.args[i].split("=");
    if(tmp.length > 1 && tmp[0] == 'link' && tmp[1].length > 0) {
        curLink = tmp[1].trim();
    }
}

casper.start("https://int.soccerway.com", function(){
    if(curLink.length > 0) {
        soccerway_detail.executeScrape(casper, curLink, function (data) {
            if (data != undefined && data != null) {
                casper.echo(JSON.stringify(data));
            }

            exitExecution();
        });
    }
    else {
        exitExecution();
    }
});

casper.run();

// casper.on('run.complete', function() {
//     this.exit();
// });