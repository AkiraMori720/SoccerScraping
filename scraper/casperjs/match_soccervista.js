var soccervista= require('core/soccervista.js');

var browserAgents = [
    "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:77.0) Gecko/20190101 Firefox/77.0",
    "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:77.0) Gecko/20100101 Firefox/77.0",
    "Mozilla/5.0 (X11; Linux ppc64le; rv:75.0) Gecko/20100101 Firefox/75.0",
    "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:39.0) Gecko/20100101 Firefox/75.0",
    "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.10; rv:75.0) Gecko/20100101 Firefox/75.0",
    "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:70.0) Gecko/20191022 Firefox/70.0",
    "Mozilla/5.0 (Windows; U; Windows NT 9.1; en-US; rv:12.9.1.11) Gecko/20100821 Firefox/70",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36",
    "Mozilla/5.0 (X11; Ubuntu; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2919.83 Safari/537.36",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2866.71 Safari/537.36",
    "Mozilla/5.0 (X11; Ubuntu; Linux i686 on x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2820.59 Safari/537.36",
    "Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/44.0.2403.155 Safari/537.36"
];

function getRandomIntFromRange(min, max) {
    return Math.round(Math.random() * (max - min)) + min;
}

var casper = require('casper').create({
    verbose: false,
    logLevel: "info",
    pageSettings: {
        userAgent: browserAgents[getRandomIntFromRange(0, browserAgents.length - 1)],
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

casper.start("https://www.soccervista.com", function(){
    soccervista.executeScrape(casper, curDate, countryList, function(data){
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