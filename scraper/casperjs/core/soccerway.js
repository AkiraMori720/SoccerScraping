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
            var link = 'https://int.soccerway.com/matches/' + curDate;
            casper.open(link).then(function(){
                casper.waitForSelector('div[class^="block_date_matches"]',
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
        clickRowHeader();
    };

    var clickRowHeader = function(index, maxRow) {
        var rowsSelector = 'div[class^="block_date_matches"] > div[class^="table-container"] > table[class^="matches"] > tbody > tr';
        if(maxRow == undefined || maxRow == null || maxRow == 0) {
            var eleRows = findElementsInfo([rowsSelector]);
            if(eleRows != null) {
                maxRow = eleRows.length;
            }
            else {
                maxRow = 0;
            }
        }

        if(index == undefined || index == null || index == 0) {
            index = 0;
        }

        if(index < maxRow) {
            var bNeedExpand = false;

            while(index < maxRow) {
                var rowEleSelector = rowsSelector + ':nth-child(' + (index + 1) + ') ';
                var ele = findElementsInfo([rowEleSelector]);
                if (ele != null && ele[0].attributes['class'] != undefined) {
                    var clsDIV = ele[0].attributes['class'];

                    if (clsDIV.indexOf('group-head') !== -1 && clsDIV.indexOf('expanded') === -1) {
                        var headerTitleEle = rowEleSelector + 'th:nth-child(1)';
                        ele = findElementsInfo([headerTitleEle]);

                        if(ele != null) {
                            var tmp = ele[0].text.trim().split('-');
                            var curCountry = tmp[0].trim();
                            var curDivision= tmp[1].trim();

                            if(filterCountries.indexOf(curCountry.toLowerCase().split(" ").join('')) != -1) {
                                bNeedExpand = true;
                                break;
                            }
                        }
                    }
                }
                index ++;
            }

            if(bNeedExpand) {
                casper.thenClick(rowEleSelector + 'th:nth-child(1)').then(function () {
                    casper.wait(1000, function(){
                        clickRowHeader(index + 1, maxRow);
                    });
                });
            }
            else {
                // Filter matches
                fetchMatches();
            }
        }
        else {
            // Filter matches
            fetchMatches();
        }
    };

    var fetchMatches = function() {
        var rowsSelector = 'div[class^="block_date_matches"] > div[class^="table-container"] > table[class^="matches"] > tbody > tr';
        var eleRows = findElementsInfo([rowsSelector]);
        if(eleRows != null) {
            var curCountry = '';
            var curDivision= '';
            for(var i = 0; i < eleRows.length; i++) {
                var rowEleSelector = rowsSelector + ':nth-child(' + (i + 1) + ') ';
                var ele = findElementsInfo([rowEleSelector]);
                if (ele != null && ele[0].attributes['class'] != undefined) {
                    var clsDIV = ele[0].attributes['class'];

                    if (clsDIV.indexOf('group-head') !== -1 && clsDIV.indexOf('expanded') !== -1) {
                        var headerTitleEle = rowEleSelector + 'th:nth-child(1) ';
                        ele = findElementsInfo([headerTitleEle]);
                        if(ele != null) {
                            var tmp = ele[0].text.trim().split('-');

                            if(filterCountries.indexOf(tmp[0].toLowerCase().split(" ").join('')) != -1) {
                                curCountry = tmp[0].trim();
                                curDivision= tmp[1].trim();
                            }
                            else {
                                curCountry = '';
                                curDivision= '';
                            }
                        }
                    }
                    else if((clsDIV.indexOf('even') !== -1 || clsDIV.indexOf('odd') !== -1) && curCountry.length > 0) {
                        var linkSelector = rowEleSelector + 'td[class^="score-time"] > a';
                        var ele = findElementsInfo([linkSelector]);
                        var link = '';
                        var score = '';
                        if(ele != null && ele[0].attributes['href'] != undefined) {
                            link = "https://int.soccerway.com" + ele[0].attributes['href'];
                            var txt = ele[0].text.trim();
                            if(txt.indexOf(':') !== -1) {
                                score = txt;
                            }
                        }
                        else {
                            continue;
                        }

                        var teamSelector = rowEleSelector + 'td[class^="team team-a"]';
                        ele = findElementsInfo([teamSelector]);
                        var team_1 = '';
                        if(ele != null) {
                            team_1 = ele[0].text.trim();
                        }

                        teamSelector = rowEleSelector + 'td[class^="team team-b"]';
                        ele = findElementsInfo([teamSelector]);
                        var team_2 = '';
                        if(ele != null) {
                            team_2 = ele[0].text.trim();
                        }

                        matches.push({
                            country : curCountry,
                            division: curDivision,
                            team_1  : team_1,
                            team_2  : team_2,
                            link    : link,
                            score   : score
                        });
                    }
                }
            }
        }

        terminate();
    };

    startProcess();
};
