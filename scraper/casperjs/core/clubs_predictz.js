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
            casper.waitForSelector('p[class^="medbody2"]',
                function(){
                    var eleTitle = findElementsInfo(['p[class^="medbody2"]']);
                    if(eleTitle != null) {
                        var title = eleTitle[0].text;
                        if (title.indexOf(season) >= 0) {
                            var trSelector = 'table[class^="pztable"] > tbody > tr';
                            var eleTeams = findElementsInfo([trSelector]);
                            if (eleTeams != null) {
                                for (var i = 2; i < eleTeams.length; i++) {
                                    var eleTd = findElementsInfo([trSelector + ':nth-child(' + (i + 1) + ') > td:nth-child(2) > a']);
                                    if (eleTd != null) {
                                        fetchedClubs.push(eleTd[0].text.trim());
                                    }
                                }
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
        }
    }

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
