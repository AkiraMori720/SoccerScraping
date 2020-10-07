var logger = require('../util/log');

exports.executeScrape = function(season, callback) {
    var fetchedClubs = [];

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
        if(casper == null || casper == undefined) {
            failedOpenLink();
        }
        else {
            casper.waitForSelector('table[id^="page_competition_1_block_competition_tables_"]',
                function(){
                    checkSeason(function(){
                        terminate();
                    });
                },
                function fail(){
                    var eleSubMenus = findElementsInfo(['ul[class^="left-tree"] > li[class^="expanded"] > ul > li > ul > li > a']);
                    if(eleSubMenus != null) {
                        var subLink = null;
                        for(var i = 0; i < eleSubMenus.length; i++) {
                            if(eleSubMenus[i].text.trim() == 'Regular Season') {
                                subLink = "https://int.soccerway.com" + eleSubMenus[i].attributes['href'];
                                break;
                            }
                        }

                        if(subLink != null) {
                            casper.thenOpen(subLink, function(){
                                casper.waitForSelector('table[id^="page_competition_1_block_competition_tables_"]',
                                    function(){
                                        checkSeason(function(){
                                            terminate();
                                        });
                                    },
                                    function(){
                                        failedOpenLink();
                                    },
                                    5000
                                );
                            });
                        }
                        else {
                            failedOpenLink();
                        }
                    }
                    else {
                        failedOpenLink();
                    }
                },
                5000
            );
        }
    }

    var checkSeason = function(callback) {
        var eleSeason = findElementsInfo([
            'ul[class^="left-tree"] > li[class^="expanded"] ul[class^="level-1"] > li[class^="leaf current"]',
            'ul[class^="left-tree"] > li[class^="expanded"] ul[class^="level-1"] > li[class^="expanded"]'
        ]);
        if(eleSeason != null) {
            var title = eleSeason[0].text;
            if(title.indexOf(season) >= 0) {
                fetchClubs();
                callback();
            }
            else {
                callback();
            }
        }
        else {
            callback();
        }
    };

    var fetchClubs = function() {
        var trSelector = 'table[id^="page_competition_1_block_competition_tables_"] > tbody > tr';
        var eleTeams = findElementsInfo([trSelector]);
        if(eleTeams != null) {
            for(var i = 0; i < eleTeams.length; i++) {
                var eleTd = findElementsInfo([trSelector + ':nth-child(' + (i+1) + ') > td:nth-child(3) > a']);
                if(eleTd != null) {
                    fetchedClubs.push(eleTd[0].attributes['title'].trim());
                }
            }
        }
    };

    var failedOpenLink = function() {
        if(callback) {
            callback();
        }
    };

    var terminate = function() {
        if(callback) {
            callback(fetchedClubs);
        }
    };

    startProcess();
};
