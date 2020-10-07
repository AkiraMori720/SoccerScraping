var logger = require('../util/log');

exports.executeScrape = function(casper, selectedLink, callback) {
    var allDetails = {};

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
            processLink();
        }
    }

    var failedOpenLink = function() {
        if(callback) {
            callback();
        }
    };

    var terminate = function() {
        if(callback) {
            callback(allDetails);
        }
    };

    var processLink = function() {
        casper.open(selectedLink).then(function(){
            casper.waitForSelector('div.block_match_info',
                function() {
                    allDetails = {
                        team_a : {},
                        team_b : {},
                        referee : ""
                    };

                    processPage();
                },
                function fail(){
                    terminate();
                },
                5000
            );
        });
    };

    var processPage = function() {
        // Get Detail links of 2 teams
        var teamASelector = 'div.block_match_info > div[class^="match-info"] > div > div[class="container left"] a[class^="team"]';
        var ele = findElementsInfo([teamASelector]);
        if(ele != null) {
            allDetails.team_a = {
                link : "https://int.soccerway.com" + ele[0].attributes['href']
            }
        }

        var teamBSelector = 'div.block_match_info > div[class^="match-info"] > div > div[class="container right"] a[class^="team"]';
        ele = findElementsInfo([teamBSelector]);
        if(ele != null) {
            allDetails.team_b = {
                link : "https://int.soccerway.com" + ele[0].attributes['href']
            }
        }

        // Get Referee
        var refereeSelector = 'div.block_match_additional_info > table > tbody > tr:nth-child(1) > td:nth-child(2) > a';
        ele = findElementsInfo([refereeSelector]);
        if(ele != null) {
            var tmp = ele[0].attributes['href'].split('/');
            allDetails.referee = tmp[2].split('-').join(' ');
        }

        if(allDetails.team_a.link != undefined && allDetails.team_a.link.length > 0) {
            processTeam("team_a");
        }
        else {
            processNextTeam("team_a");
        }
    };

    var processTeam = function(team) {
        casper.thenOpen(allDetails[team].link).then(function(){
            casper.waitForSelector('div.block_team_info',
                function() {
                    var info = {
                        logo    : "",
                        site    : "",
                        founded : "",
                        address : "",
                        country : "",
                        phone   : "",
                        fax     : "",
                        email   : "",
                        history : {
                            link : "",
                            matches : []
                        },
                        venue : {
                            link : "",
                            image : "",
                            name : "",
                            city : "",
                            capacity : ""
                        }
                    };

                    var selector = 'div.block_team_info div.fully-padded div.logo img';
                    var ele = findElementsInfo([selector]);
                    if(ele != null) {
                        var imgURL = ele[0].attributes['src'];
                        info.logo = imgURL;
                    }

                    selector = 'div.block_team_info div.fully-padded > p > a';
                    ele = findElementsInfo([selector]);
                    if(ele != null) {
                        info.site = ele[0].attributes['href'].trim();
                    }

                    selector = 'div.block_team_info div.fully-padded div.clearfix dl > dd';
                    ele = findElementsInfo([selector]);
                    if(ele != null) {
                        for(var s = 0; s < ele.length; s++) {
                            var text = ele[s].text.trim().split(/[\r\n]/).join(' ');
                            if(s == 0) {
                                info.founded = text;
                            }
                            else if(s == 1) {
                                info.address = text;
                            }
                            else if(s == 2) {
                                info.country = text;
                            }
                            else if(s == 3) {
                                info.phone = text;
                            }
                            else if(s == 4) {
                                info.fax = text;
                            }
                            else if(s == 5) {
                                info.email = text;
                            }
                        }
                    }

                    // Venue
                    selector = 'div.block_team_venue > a';
                    ele = findElementsInfo([selector]);
                    if(ele != null) {
                        info.venue.link = "https://int.soccerway.com" + ele[0].attributes['href'].trim();
                    }

                    selector = 'div.block_team_venue > a > img';
                    ele = findElementsInfo([selector]);
                    if(ele != null) {
                        info.venue.image = ele[0].attributes['src'].trim();
                    }

                    selector = 'div.block_team_venue div.fully-padded div.clearfix dl > dd';
                    ele = findElementsInfo([selector]);
                    if(ele != null) {
                        for(var s = 0; s < ele.length; s++) {
                            var text = ele[s].text.trim().split(/[\r\n]/).join(' ');
                            if(s == 0) {
                                info.venue.name = text;
                            }
                            else if(s == 1) {
                                info.venue.city = text;
                            }
                            else if(s == 2) {
                                info.venue.capacity = text;
                            }
                        }
                    }

                    // History
                    // selector = 'div[id^="page_team_1_block_team_matches_summary_7"] > h2 > a';
                    selector = 'div[id^="submenu"] > ul > li:nth-child(2) > a';
                    ele = findElementsInfo([selector]);
                    if(ele != null) {
                        info.history.link = "https://int.soccerway.com" + ele[0].attributes['href'];
                    }

                    allDetails[team].info = info;

                    // fetch last matches
                    if(info.history.link.length > 0) {
                        fetchMatchHistory(team, info.history.link, function(){
                            processNextTeam(team);
                        });
                    }
                    else {
                        processNextTeam(team);
                    }
                },
                function fail(){
                    casper.echo('process team_a failed team_b');
                    processNextTeam(team);
                },
                5000
            );
        });
    };

    var processNextTeam = function(team) {
        if(team == 'team_a') {
            if(allDetails.team_b.link != undefined && allDetails.team_b.link.length > 0) {
                processTeam("team_b");
            }
            else {
                terminate();
            }
        }
        else {
            terminate();
        }
    };

    var fetchMatchHistory = function(team, link, callback) {
        casper.thenOpen(link).then(function(){
            casper.waitForSelector(
                'div[id^="page_team_1_block_team_matches"]',
                function() {
                    var typeSelector = 'div[id^="page_team_1_block_team_matches"] > div[class^="content"] > div:nth-child(2) > ul[id^="page_team_1_block_team_matches"] > li:nth-child(' + ((team == 'team_a') ? 2 : 3) + ') > a[id^="page_team_1_block_team_matches"]';
                    casper.waitForSelector(typeSelector,
                        function() {
                            casper.thenClick(typeSelector).then(function(){
                                var activeMenuSelector = 'div[id^="page_team_1_block_team_matches"] > div[class^="content"] > div:nth-child(2) > ul[id^="page_team_1_block_team_matches"] > li[class^="selected"] > a[id^="page_team_1_block_team_matches"]';
                                casper.waitForSelector(
                                    activeMenuSelector,
                                    function(){
                                        casper.waitWhileVisible(
                                            'div[id^="page_team_1_block_team_matches"] > div[class^="content"] > div[class^="overlay"]',
                                            function(){
                                                var trSelector = 'div[class^="block_team_matches"] > div[class^="table-container"] table tbody tr';
                                                var eleTRs = findElementsInfo([trSelector]);
                                                if(eleTRs != null) {
                                                    for(var m = 0; m < eleTRs.length; m++) {
                                                        var history = {
                                                            date    : "",
                                                            division: "",
                                                            team_a  : "",
                                                            team_b  : "",
                                                            result  : ""
                                                        };

                                                        var selector = trSelector + ':nth-child(' + (m+1) + ') td[class^="full-date"]';
                                                        var ele = findElementsInfo([selector]);
                                                        if(ele != null) {
                                                            history.date = ele[0].text.trim();
                                                        }

                                                        selector = trSelector + ':nth-child(' + (m+1) + ') td[class^="competition"] > a';
                                                        ele = findElementsInfo([selector]);
                                                        if(ele != null) {
                                                            history.division = ele[0].attributes['title'];
                                                        }

                                                        selector = trSelector + ':nth-child(' + (m+1) + ') td[class^="team team-a"] > a';
                                                        ele = findElementsInfo([selector]);
                                                        if(ele != null) {
                                                            history.team_a = ele[0].attributes['title'];
                                                        }
                                                        else {
                                                            continue;
                                                        }

                                                        selector = trSelector + ':nth-child(' + (m+1) + ') td[class^="team team-b"] > a';
                                                        ele = findElementsInfo([selector]);
                                                        if(ele != null) {
                                                            history.team_b = ele[0].attributes['title'];
                                                        }

                                                        selector = trSelector + ':nth-child(' + (m+1) + ') td[class^="score"] > a';
                                                        ele = findElementsInfo([selector]);
                                                        if(ele != null) {
                                                            history.result = ele[0].text.trim();
                                                        }

                                                        allDetails[team].info.history.matches.push(history);
                                                    }
                                                }

                                                callback();
                                            },
                                            function(){
                                                callback();
                                            },
                                            5000
                                        );
                                    },
                                    function(){
                                        callback();
                                    },
                                    5000
                                );
                            });
                        },
                        function fail(){
                            callback();
                        },
                        5000
                    );
                },
                function() {
                    callback();
                },
                2000
            );

        });
    };

    startProcess();
};
