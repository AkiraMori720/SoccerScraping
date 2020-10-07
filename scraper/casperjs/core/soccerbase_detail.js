var logger = require('../util/log');

exports.executeScrape = function(casper, season, callback) {
    var currentIndex = 0;
    var allMatches = [];

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
                    selectSeason(),
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
            callback(allMatches);
        }
    };

    var selectSeason = function() {
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
                var newLink = casper.getCurrentUrl() + "&season_id=" + optVal;
                casper.thenOpen(newLink, function(){
                    processLink();
                });
            }
        }
        else {
            terminate();
        }
    };

    var processLink = function() {
        casper.waitForSelector('#tpg',
            function(){
                var trSelector = '#tpg > tbody > tr';
                var eleTRs = findElementsInfo([trSelector]);
                if(eleTRs != null) {
                    for(var i = 0; i < eleTRs.length; i++) {
                        var selector = trSelector + ':nth-child(' + (i + 1) + ') td[class^="first"] > a';
                        var ele = findElementsInfo([selector]);
                        if(ele == null) {
                            continue;
                        }

                        var matchItem = {
                            league  : "",
                            comp_id : "",
                            date    : "",
                            team_a  : "",
                            team_b  : "",
                            result  : "",
                            yellow  : "",
                            red     : ""
                        };

                        matchItem.league = ele[0].text;
                        var link = ele[0].attributes['href'].trim();
                        var tmp = link.split('=');
                        matchItem.comp_id = tmp[tmp.length - 1];

                        selector = trSelector + ':nth-child(' + (i + 1) + ') td[class^="dateTime"]';
                        ele = findElementsInfo([selector]);
                        if(ele != null) {
                            matchItem.date = ele[0].text.trim();
                        }

                        selector = trSelector + ':nth-child(' + (i + 1) + ') td[class^="team homeTeam"]';
                        ele = findElementsInfo([selector]);
                        if(ele != null) {
                            matchItem.team_a = ele[0].text.trim();
                        }

                        selector = trSelector + ':nth-child(' + (i + 1) + ') td[class^="team awayTeam"]';
                        ele = findElementsInfo([selector]);
                        if(ele != null) {
                            matchItem.team_b = ele[0].text.trim();
                        }

                        selector = trSelector + ':nth-child(' + (i + 1) + ') td[class^="score"]';
                        ele = findElementsInfo([selector]);
                        if(ele != null) {
                            matchItem.result = ele[0].text.trim();
                        }

                        selector = trSelector + ':nth-child(' + (i + 1) + ') td:nth-child(8)';
                        ele = findElementsInfo([selector]);
                        if(ele != null) {
                            matchItem.yellow = ele[0].text.trim();
                        }

                        selector = trSelector + ':nth-child(' + (i + 1) + ') td:nth-child(9)';
                        ele = findElementsInfo([selector]);
                        if(ele != null) {
                            matchItem.red = ele[0].text.trim();
                        }

                        allMatches.push(matchItem);
                    }
                }

                terminate();
            },
            function(){
                terminate();
            },
            5000
        );
    };

    startProcess();
};
