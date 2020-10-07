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
            var link = 'https://www.oddsportal.com/matches/soccer/' + curDate;
            casper.open(link).then(function(){
                casper.waitForSelector('#table-matches table[class^=" table-main"]',
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
        var trSelector = '#table-matches table[class^=" table-main"] tbody tr';
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

                // if(ele[0].attributes['class'] != undefined) {
                //
                // }

                // var clsTR = ele[0].attributes['class'];
                // if(clsTR.indexOf('dark') >= 0) {
                if(ele[0].attributes['xtid'] != undefined) {
                    var selector = trEleSelector + 'th[class^="first2"] a';
                    var eleA = findElementsInfo([selector]);
                    if(eleA != null) {
                        for(var k = 0; k < eleA.length; k++) {
                            if (eleA[k].attributes['class'] != undefined && eleA[k].attributes['class'] == 'bfl') {
                                if (curCountry != eleA[k].text.trim()) {
                                    curCountry = eleA[k].text.trim();
                                }
                            }
                            else {
                                if (level != eleA[k].text.trim()) {
                                    level = eleA[k].text.trim();
                                }
                            }
                        }
                    }
                }
                else if(
                    // (clsTR.indexOf('activate') >= 0 || clsTR.indexOf('odd') >= 0) &&
                    ele[0].attributes['xeid'] != undefined &&
                    curCountry.length > 0 &&
                    filterCountries.indexOf(curCountry.toLowerCase().split(" ").join('')) != -1 ) {
                    // Match Time
                    var selector = trEleSelector + 'td[class^="table-time"]';
                    var eleTD = findElementsInfo([selector]);
                    var matchTime = '';
                    if(eleTD != null) {
                        matchTime = eleTD[0].text.trim();
                    }

                    // Teams
                    selector = trEleSelector + 'td[class^="name"] a[href^="/soccer"]';
                    var eleA = findElementsInfo([selector]);
                    var teamData = {
                        team1 : '',
                        team2 : '',
                        active: ''
                    };
                    if(eleA != null) {
                        var str = eleA[0].text.trim().split(/[,-]/);

                        if(str.length < 2 && eleA.length > 1) {
                            str = eleA[1].text.trim().split(/[,-]/);
                        }

                        teamData.team1 = str[0].trim();
                        teamData.team2 = str[1].trim();

                        // selector = trEleSelector + 'td[class^="name"] a span[class="bold"]';
                        // var eleBold = findElementsInfo([selector]);
                        // if(eleBold != null) {
                        //     teamData.active = eleBold[0].text.trim();
                        // }
                        teamData.active = teamData.team1;
                    }
                    else {
                        continue;
                    }

                    // Score
                    selector = trEleSelector + 'td[class^="center bold table-odds"]';
                    eleTD = findElementsInfo([selector]);
                    var score = '';
                    if(eleTD != null) {
                        score = eleTD[0].text.trim();
                    }

                    // Quotes
                    selector = trEleSelector + 'td[class^="odds-nowrp"]';
                    eleTD = findElementsInfo([selector]);
                    var quotes = {
                        odds_1 : '',
                        odds_x : '',
                        odds_2 : '',
                    };
                    if(eleTD != null) {
                        quotes.odds_1 = eleTD[0].text.trim();
                        quotes.odds_x = eleTD[1].text.trim();
                        quotes.odds_2 = eleTD[2].text.trim();
                    }

                    // Bookmarks
                    selector = trEleSelector + 'td[class^="center info-value"]';
                    eleTD = findElementsInfo([selector]);
                    var bookmarks = '';
                    if(eleTD != null) {
                        bookmarks = eleTD[0].text.trim();
                    }

                    matches.push({
                        country     : curCountry,
                        division    : level,
                        time        : matchTime,
                        team_1      : teamData.team1,
                        team_2      : teamData.team2,
                        team_active : teamData.active,
                        score       : score,
                        odds_1      : quotes.odds_1,
                        odds_x      : quotes.odds_x,
                        odds_2      : quotes.odds_2,
                        bookmark    : bookmarks
                    });
                }
            }
        }

        terminate();
    };

    startProcess();
};
