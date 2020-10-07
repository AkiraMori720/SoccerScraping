var logger = require('../util/log');

exports.executeScrape = function(casper, curDate, countryList, callback) {
    var currentIndex = 0;
    var matches = [];
    var filterCountries = [];

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

        for(var i = 0; i < countryList.length; i++) {
            filterCountries.push(countryList[i].toLowerCase().split(/[ -]/).join(''));
        }

        if(casper == null || casper == undefined) {
            failedOpenLink();
        }
        else {
            var link = 'https://www.soccervista.com/soccer_games.php?date=' + curDate;
            casper.open(link).then(function(){
                casper.waitForSelector('table[class^="main"]',
                    processPage(),
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
            callback(matches);
        }
    };

    var processPage = function() {
        var trSelector = 'table[class^="main"] tbody tr';
        var eleTR = findElementsInfo([trSelector]);
        if(eleTR != null) {
            var curCountry = '';
            var level = '';
            for(var i = 0; i < eleTR.length; i++) {
                var trEleSelector = trSelector + ':nth-child(' + (i + 1) + ') ';

                var ele = findElementsInfo([trEleSelector]);
                if(ele == null) {
                    continue;
                }

                if(ele[0].attributes['class'] != undefined) {
                    var clsTR = ele[0].attributes['class'];
                    if(clsTR.indexOf('headupe') >= 0) {
                        var selector = trEleSelector + 'td';
                        var eleTD = findElementsInfo([selector]);
                        if(eleTD != null) {
                            if (eleTD[1].text.trim() != curCountry) {
                                curCountry = eleTD[1].text.trim();
                            }

                            if (level != eleTD[2].text.trim()) {
                                level = eleTD[2].text.trim();
                            }
                        }
                    }
                    else if(
                        (clsTR.indexOf('twom') >= 0 || clsTR.indexOf('onem') >= 0) &&
                        curCountry.length > 0 &&
                        filterCountries.indexOf(curCountry.toLowerCase().split(" ").join('')) != -1) {
                        // Match Time
                        var selector = trEleSelector + 'td:nth-child(1)';
                        var eleTD = findElementsInfo([selector]);
                        var matchTime = '';
                        if(eleTD != null) {
                            matchTime = eleTD[0].text.trim();
                        }

                        // Teams
                        selector = trEleSelector + 'td[class^="home"]';
                        eleTD = findElementsInfo([selector]);
                        var team1 = '';
                        var active = '';
                        if(eleTD != null) {
                            team1 = eleTD[0].text.trim();
                            active = team1;
                        }

                        selector = trEleSelector + 'td:nth-child(5)';
                        eleTD = findElementsInfo([selector]);
                        var team2 = '';
                        if(eleTD != null) {
                            team2 = eleTD[0].text.trim();
                        }

                        // Result
                        selector = trEleSelector + 'td[class^="detail"]';
                        eleTD = findElementsInfo([selector]);
                        var result = '';
                        if(eleTD != null) {
                            result = eleTD[0].text.trim();
                        }

                        // Quotes
                        selector = trEleSelector + 'td:nth-child(7)';
                        eleTD = findElementsInfo([selector]);
                        var odds_1 = '';
                        if(eleTD != null) {
                            odds_1 = eleTD[0].text.trim();
                        }

                        selector = trEleSelector + 'td:nth-child(8)';
                        eleTD = findElementsInfo([selector]);
                        var odds_x = '';
                        if(eleTD != null) {
                            odds_x = eleTD[0].text.trim();
                        }

                        selector = trEleSelector + 'td:nth-child(9)';
                        eleTD = findElementsInfo([selector]);
                        var odds_2 = '';
                        if(eleTD != null) {
                            odds_2 = eleTD[0].text.trim();
                        }

                        // 1X2
                        selector = trEleSelector + 'td:nth-child(10)';
                        eleTD = findElementsInfo([selector]);
                        var inf_1x2 = '';
                        if(eleTD != null) {
                            inf_1x2 = eleTD[0].text.trim();
                        }

                        // Goals
                        selector = trEleSelector + 'td:nth-child(11)';
                        eleTD = findElementsInfo([selector]);
                        var goals = '';
                        if(eleTD != null) {
                            goals = eleTD[0].text.trim();
                        }

                        // Score
                        selector = trEleSelector + 'td:nth-child(12)';
                        eleTD = findElementsInfo([selector]);
                        var score = '';
                        if(eleTD != null) {
                            score = eleTD[0].text.trim();
                        }

                        matches.push({
                            country     : curCountry,
                            division    : level,
                            time        : matchTime,
                            team_1      : team1,
                            team_2      : team2,
                            team_active : active,
                            result      : result,
                            score       : score,
                            odds_1      : odds_1,
                            odds_x      : odds_x,
                            odds_2      : odds_2,
                            inf_1x2     : inf_1x2,
                            goals       : goals
                        });
                    }
                }
            }
        }

        terminate();
    };

    startProcess();
};
