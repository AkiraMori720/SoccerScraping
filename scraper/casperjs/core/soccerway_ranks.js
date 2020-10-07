var logger = require('../util/log');

exports.executeScrape = function(casper, season, country, leagueList, callback) {
    var allRankings = {};
    var curLeagueIndex = -1;

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
            casper.waitForSelector(
                'div[id="navbar-left"] > div[class^="custom-dropdown"]',
                function(){
                    selectCountry();
                },
                function(){},
                5000
            );
        }
    }

    var failedOpenLink = function() {
        if(callback) {
            callback();
        }
    };

    var terminate = function() {
        if(callback) {
            callback(allRankings);
        }
    };

    var selectCountry = function() {
        var eleSelector = '#navbar-left div[class^="custom-dropdown"]:nth-child(2) > div[class^="label-wrapper"]';
        casper.thenClick(eleSelector, function(){
            eleSelector = '#navbar-left div[class^="custom-dropdown"]:nth-child(2) > ul[class^="list"]';
            casper.waitUntilVisible(
                eleSelector,
                function(){
                    var ele = findElementsInfo([eleSelector + ' > li']);
                    var menuIndex = -1;
                    for(var i = 0; i < ele.length; i++) {
                        if(ele[i].text.indexOf(country) >= 0) {
                            menuIndex = i;
                            break;
                        }
                    }

                    if(menuIndex > -1) {
                        allRankings[country] = {};
                        casper.thenClick(eleSelector + ' > li:nth-child(' + (menuIndex + 1) + ')', function(){
                            processLeague();
                        });
                    }
                    else {
                        terminate();
                    }
                },
                function(){
                    terminate();
                },
                5000
            );
        });
    };

    var processLeague = function(){
        casper.waitForSelector(
            'ul[class^="left-tree"]',
            function() {
                curLeagueIndex ++;
                if(curLeagueIndex >= leagueList.length) {
                    terminate();
                }
                else {
                    var selector = 'ul[class^="left-tree"] > li';
                    var ele = findElementsInfo([selector]);
                    var menuIndex = -1;
                    var bNeedToClick = true;
                    for(var i = 0; i < ele.length; i++) {
                        var eleA = findElementsInfo([selector + ':nth-child(' + (i + 1) + ') > a']);
                        if(eleA != null) {
                            if(eleA[0].text.trim().toLowerCase() == leagueList[curLeagueIndex].toLowerCase()) {
                                menuIndex = i;
                                bNeedToClick = !(ele[i].attributes['class'].indexOf('expanded') >= 0);
                                break;
                            }
                        }
                    }

                    if(menuIndex > -1) {
                        if(bNeedToClick) {
                            casper.thenClick(selector + ':nth-child(' + (menuIndex + 1) + ') > a', function(){
                                fetchRankings(function(){
                                    processLeague();
                                });
                            });
                        }
                        else {
                            fetchRankings(function(){
                                processLeague();
                            });
                        }
                    }
                    else {
                        processLeague();
                    }
                }
            },
            function() {
                processLeague();
            },
            5000
        );
    };

    var fetchRankings = function(callback, bUsedSubLink){
        allRankings[country][leagueList[curLeagueIndex]] = [];

        var eleSeason = findElementsInfo([
            'ul[class^="left-tree"] > li[class^="expanded"] ul[class^="level-1"] > li[class^="leaf current"]',
            'ul[class^="left-tree"] > li[class^="expanded"] ul[class^="level-1"] > li[class^="expanded"]'
        ]);
        if(eleSeason != null) {
            var title = eleSeason[0].text;
            if(title.indexOf(season) >= 0) {
                var rowsSelector = 'table[id^="page_competition_1_block_competition_tables_"] > tbody > tr';
                casper.waitForSelector(
                    rowsSelector,
                    function(){
                        var eleRows = findElementsInfo([rowsSelector]);
                        if(eleRows != null) {
                            for(var i = 0; i < eleRows.length; i++) {
                                var ele = findElementsInfo([rowsSelector + ':nth-child(' + (i+1) + ') td:nth-child(3) > a']);
                                if(ele == null) {
                                    continue;
                                }

                                var newTeam = {
                                    team        : ele[0].attributes['title'].trim(),
                                    cur_rank    : "",
                                    prev_rank   : "",
                                    matches     : "",
                                    win         : "",
                                    draw        : "",
                                    lose        : "",
                                    goals       : "",
                                    goals_against   : "",
                                    diff_goals      : "",
                                    points          : "",
                                    last_5_matches  : ""
                                };

                                ele = findElementsInfo([rowsSelector + ':nth-child(' + (i+1) + ') td:nth-child(1)']);
                                if(ele != null) {
                                    newTeam.cur_rank = ele[0].text.trim();
                                }

                                ele = findElementsInfo([rowsSelector + ':nth-child(' + (i+1) + ') td:nth-child(2) > img']);
                                if(ele != null) {
                                    var tmp = ele[0].attributes['title'].trim().split(' ');
                                    newTeam.prev_rank = tmp[tmp.length - 1];
                                }
                                else {
                                    newTeam.prev_rank = newTeam.cur_rank;
                                }

                                ele = findElementsInfo([rowsSelector + ':nth-child(' + (i+1) + ') td:nth-child(4)']);
                                if(ele != null) {
                                    newTeam.matches = ele[0].text.trim();
                                }

                                ele = findElementsInfo([rowsSelector + ':nth-child(' + (i+1) + ') td:nth-child(5)']);
                                if(ele != null) {
                                    newTeam.win = ele[0].text.trim();
                                }

                                ele = findElementsInfo([rowsSelector + ':nth-child(' + (i+1) + ') td:nth-child(6)']);
                                if(ele != null) {
                                    newTeam.draw = ele[0].text.trim();
                                }

                                ele = findElementsInfo([rowsSelector + ':nth-child(' + (i+1) + ') td:nth-child(7)']);
                                if(ele != null) {
                                    newTeam.lose = ele[0].text.trim();
                                }

                                ele = findElementsInfo([rowsSelector + ':nth-child(' + (i+1) + ') td:nth-child(8)']);
                                if(ele != null) {
                                    newTeam.goals = ele[0].text.trim();
                                }

                                ele = findElementsInfo([rowsSelector + ':nth-child(' + (i+1) + ') td:nth-child(9)']);
                                if(ele != null) {
                                    newTeam.goals_against = ele[0].text.trim();
                                }

                                ele = findElementsInfo([rowsSelector + ':nth-child(' + (i+1) + ') td:nth-child(10)']);
                                if(ele != null) {
                                    newTeam.diff_goals = ele[0].text.trim();
                                }

                                ele = findElementsInfo([rowsSelector + ':nth-child(' + (i+1) + ') td:nth-child(11)']);
                                if(ele != null) {
                                    newTeam.points = ele[0].text.trim();
                                }

                                ele = findElementsInfo([rowsSelector + ':nth-child(' + (i+1) + ') td:nth-child(12)']);
                                if(ele != null) {
                                    newTeam.last_5_matches = ele[0].text.trim().split(/[ \n]/).join('');
                                }

                                allRankings[country][leagueList[curLeagueIndex]].push(newTeam);
                            }
                        }

                        callback();
                    },
                    function(){
                        if(!bUsedSubLink) {
                            var eleSubMenus = findElementsInfo(['ul[class^="left-tree"] > li[class^="expanded"] > ul > li > ul > li > a']);
                            if (eleSubMenus != null) {
                                var subLink = null;
                                for (var i = 0; i < eleSubMenus.length; i++) {
                                    if (eleSubMenus[i].text.trim() == 'Regular Season') {
                                        subLink = "https://int.soccerway.com" + eleSubMenus[i].attributes['href'];
                                        break;
                                    }
                                }

                                if (subLink != null) {
                                    casper.thenOpen(subLink, function () {
                                        fetchRankings(function () {
                                            callback();
                                        }, true);
                                    });
                                }
                                else {
                                    callback();
                                }
                            }
                            else {
                                callback();
                            }
                        }
                        else {
                            callback();
                        }
                    },
                    5000
                );
            }
            else {
                callback();
            }
        }
        else {
            callback();
        }
    };

    startProcess();
};
