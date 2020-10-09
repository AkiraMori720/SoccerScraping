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
            casper.waitForSelector('ul.main-filter',
                function(){
                    var seasonSelector = 'ul.main-filter > li > span';
                    var eleSeasons = findElementsInfo([seasonSelector]);
                    if(eleSeasons == null || eleSeasons.length == 0) {
                        terminate();
                    }

                    var bNeedToChangeSeason = false;
                    var i = 0;
                    for(; i < eleSeasons.length; i++) {
                        if(eleSeasons[i].text.trim().substr(0, 4) === season.substr(0, 4)) {
                            bNeedToChangeSeason = (eleSeasons[i].attributes['class'].indexOf("inactive") >= 0);
                            break;
                        }
                    }

                    if(bNeedToChangeSeason) {
                        casper.thenClick('ul[class^="main-filter"] > li:nth-child(' + (i + 1) + ') > span > strong > a');
                        casper.then(function(){
                            casper.waitForSelector('ul[class^="ifmenu bubble stages-menu"]',
                                function(){
                                    casper.thenClick('ul[class^="ifmenu bubble stages-menu"] > li:nth-child(1) > span > a');
                                    casper.then(function(){
                                        fetchClubs();
                                    });
                                },
                                function(){
                                    terminate()
                                },
                                5000
                            );
                        });
                    }
                    else if(i < eleSeasons.length){
                        fetchClubs();
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


        }
    }

    var fetchClubs = function() {
		casper.waitForSelector('#table-type-1',
            function(){
				var trSelector = '#table-type-1 > tbody > tr';
                var eleTeams = findElementsInfo([trSelector]);
                if(eleTeams != null) {
                    for(var i = 0; i < eleTeams.length; i++) {
                        var eleTd = findElementsInfo([trSelector + ':nth-child(' + (i+1) + ') > td:nth-child(2) > span[class^="team_name_span"]']);
                        if(eleTd != null) {
                            fetchedClubs.push(eleTd[0].text.trim());
                        }
                    }
                }

                terminate();
            },
            function fail(){
                failedOpenLink();
            },
            5000
        );
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
