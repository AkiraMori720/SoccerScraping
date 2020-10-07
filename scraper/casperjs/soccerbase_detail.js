var soccerbase = require('core/soccerbase_detail.js');
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
        width: 1440,
        height: 900
    }
});

function exitExecution() {
    casper.clear();
    casper.wait(100);
    casper.exit();
}

var link = '';
var season = null; // 2019/20
for(var i = 0; i < casper.cli.args.length; i++) {
    var argText = casper.cli.args[i];
    var tmp = argText.split("=");
    if(tmp.length > 1 && tmp[0] == 'season' && tmp[1].length > 0) {
        season = tmp[1];
    }

    if(tmp.length > 1 && tmp[0] == 'link' && tmp[1].length > 0) {
        link = argText.substr(tmp[0].length + 1, argText.length - 1);
    }
}

casper.start(link, function(){
    soccerbase.executeScrape(casper, season, function(data){
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