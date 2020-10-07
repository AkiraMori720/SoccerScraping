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
                terminate();
            })
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

    var fetchRankings = function(callback) {
        casper.waitForSelector('#table-type-1',
            function(){
                var trSelector = '#table-type-1 > tbody > tr';
                var eleTeams = findElementsInfo([trSelector]);
                if(eleTeams != null) {
                    for(var i = 0; i < eleTeams.length; i++) {
                        var ele = findElementsInfo([trSelector + ':nth-child(' + (i+1) + ') > td:nth-child(2) > span[class^="team_name_span"]']);
                        if(ele == null) {
                            continue;
                        }

                        var newTeam = {
                            team        : ele[0].text.trim(),
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

                        ele = findElementsInfo([trSelector + ':nth-child(' + (i+1) + ') td:nth-child(1)']);
                        if(ele != null) {
                            newTeam.cur_rank = ele[0].text.trim().split('.').join('');
                        }

                        ele = findElementsInfo([trSelector + ':nth-child(' + (i+1) + ') td:nth-child(3)']);
                        if(ele != null) {
                            newTeam.matches = ele[0].text.trim();
                        }

                        ele = findElementsInfo([trSelector + ':nth-child(' + (i+1) + ') td:nth-child(4)']);
                        if(ele != null) {
                            newTeam.win = ele[0].text.trim();
                        }

                        ele = findElementsInfo([trSelector + ':nth-child(' + (i+1) + ') td:nth-child(5)']);
                        if(ele != null) {
                            newTeam.draw = ele[0].text.trim();
                        }

                        ele = findElementsInfo([trSelector + ':nth-child(' + (i+1) + ') td:nth-child(6)']);
                        if(ele != null) {
                            newTeam.lose = ele[0].text.trim();
                        }

                        ele = findElementsInfo([trSelector + ':nth-child(' + (i+1) + ') td:nth-child(7)']);
                        if(ele != null) {
                            var tmp = ele[0].text.trim().split(":");
                            newTeam.goals = tmp[0];
                            newTeam.goals_against = tmp[1];

                            newTeam.diff_goals = newTeam.goals - newTeam.goals_against;
                        }

                        ele = findElementsInfo([trSelector + ':nth-child(' + (i+1) + ') td:nth-child(8)']);
                        if(ele != null) {
                            newTeam.points = ele[0].text.trim();
                        }

                        ele = findElementsInfo([trSelector + ':nth-child(' + (i+1) + ') td:nth-child(9) > div > a']);
                        if(ele != null) {
                            for(var k = 0; k < ele.length; k++) {
                                var result = ele[k].attributes['xparam'];
                                casper.echo(result);
                                var res = result.match(/(\d):(\d)/);
                                casper.echo(res);
                            }
                        }

                        allRankings.push(newTeam);
                    }
                }

                callback();
            },
            function fail(){
                callback();
            },
            5000
        );
    };

    startProcess();
};
