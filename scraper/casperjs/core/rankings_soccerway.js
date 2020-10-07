var logger = require('../util/log');

exports.executeScrape = function(casper, country, leagueList, callback) {
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
            fetchRankings(function(){
                terminate()
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
            callback(allRankings);
        }
    };

    var fetchRankings = function(callback){
        var rowsSelector = 'table[id^="page_competition_1_block_competition"] > tbody > tr';
        casper.waitForSelector(
            rowsSelector,
            function(){
                var eleRows = findElementsInfo([rowsSelector]);
                if(eleRows != null) {
                    allRankings[country][leagueList[curLeagueIndex]] = [];

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
                callback();
            },
            5000
        );
    };

    startProcess();
};
