var browser = require('util/browser.js');
var soccerway = require('core/soccerway_ranks.js');

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

var country = '';
var season = '';
var leagueList = []; // Like 2020-05-23
for(var i = 0; i < casper.cli.args.length; i++) {
    var tmp = casper.cli.args[i].split("=");
    if(tmp.length > 1 && tmp[0] == 'league' && tmp[1].length > 0) {
        leagueList = tmp[1].split('-').join(' ').split(',');
    }

    if(tmp.length > 1 && tmp[0] == 'season' && tmp[1].length > 0) {
        season = tmp[1].trim();
    }

    if(tmp.length > 1 && tmp[0] == 'country' && tmp[1].length > 0) {
        country = tmp[1];
    }
}

casper.start("https://int.soccerway.com", function(){
    soccerway.executeScrape(casper, season, country, leagueList, function(data){
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