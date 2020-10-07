var logger = require('../util/log');

exports.executeScrape = function(casper, season, league, callback) {
    var currentIndex = 0;
    var allRefers = [];

    function getRandomIntFromRange(min, max) {
        return Math.round(Math.random() * (max - min)) + min;
    }

    function findElementsInfo(selectors, enableLog) {
        if(enableLog == undefined || enableLog == null) enableLog = false;

        var eleInfo = null;

        var index = 0;
        while(index < selectors.length && eleInfo == null) {
            try {
                eleInfo = casper.getElementsInfo(selectors[index]);

                // if(enableLog) {
                //     casper.echo(eleInfo[0].html);
                // }
            }
            catch (e) {
                if(enableLog) {
                    casper.echo("Not found '" + selectors[index] + "'");
                }
            }
            index ++;
        }

        return eleInfo;
    }

    function startProcess() {
        currentIndex = -1;

        if(casper == null || casper == undefined) {
            failedOpenLink();
        }
        else {
            casper.then(function(){
                casper.waitForSelector('select[id="seasonSelect"]',
                    processMainPage(),
                    function fail(){
                        failedOpenLink();
                    },
                    5000
                );
            });
        }
    }

    var failedOpenLink = function() {
        if(callback) {
            callback();
        }
    };

    var terminate = function() {
        if(callback) {
            callback(allRefers);
        }
    };

    var processMainPage = function() {
        var optSelector = 'select[id="seasonSelect"] option';
        var eleOpts = findElementsInfo([optSelector]);
        if(eleOpts != null) {
            var optVal = null;
            for (var i = 0; i < eleOpts.length; i++) {
                var optName = eleOpts[i].text.trim();
                if (optName == season) {
                    optVal = eleOpts[i].attributes['value'];
                    break;
                }
            }

            if(optVal != null) {
                var newLink = "https://www.soccerbase.com/referees/home.sd?tourn_id=" + optVal;
                casper.thenOpen(newLink, function(){
                    casper.waitForSelector('select[id="viewSelector"]',
                        function(){
                            processTable();
                        },
                        function fail(){
                            failedOpenLink();
                        },
                        5000
                    );
                });
            }
        }
        else {
            terminate();
        }
    };


    var processTable = function() {
        var trSelector = 'table[class^="table"] > tbody > tr';
        var eleTrList = findElementsInfo([trSelector]);
        if(eleTrList != null) {
            for(var i = 0; i < eleTrList.length; i++) {
                var selector = trSelector + ':nth-child(' + (i+1) + ') td[class^="first"] > a';
                var ele = findElementsInfo([selector]);
                if(ele == null) {
                    continue;
                }

                var newReferee = {
                    referee : ele[0].text.trim(),
                    link    : "https://www.soccerbase.com" + ele[0].attributes['href'],
                    country : "",
                    games   : "",
                    yellow  : "",
                    red     : ""
                };

                selector = trSelector + ':nth-child(' + (i+1) + ') td:nth-child(2)';
                ele = findElementsInfo([selector]);
                if(ele != null) {
                    newReferee.country = ele[0].text.trim();
                }

                selector = trSelector + ':nth-child(' + (i+1) + ') td:nth-child(3)';
                ele = findElementsInfo([selector]);
                if(ele != null) {
                    newReferee.games = ele[0].text.trim();
                }

                selector = trSelector + ':nth-child(' + (i+1) + ') td:nth-child(4)';
                ele = findElementsInfo([selector]);
                if(ele != null) {
                    newReferee.yellow = ele[0].text.trim();
                }

                selector = trSelector + ':nth-child(' + (i+1) + ') td:nth-child(5)';
                ele = findElementsInfo([selector]);
                if(ele != null) {
                    newReferee.red = ele[0].text.trim();
                }

                allRefers.push(newReferee);
            }
        }

        terminate();
    };

    startProcess();
};
