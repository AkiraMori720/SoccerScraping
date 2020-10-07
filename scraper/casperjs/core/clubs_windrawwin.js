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
            casper.waitForSelector('table[class^="wdwtable"]',
                function(){
                    /**
                     * No season text!!!
                     * Need to consider about that.
                     */
                    var eleTitle = findElementsInfo(['#headline3 > h1']);
                    if(eleTitle != null) {
                        var trSelector = 'table[class^="wdwtable"] > tbody > tr';
                        var eleTeams = findElementsInfo([trSelector]);
                        if (eleTeams != null) {
                            for (var i = 3; i < eleTeams.length; i++) {
                                var eleTd = findElementsInfo([trSelector + ':nth-child(' + (i + 1) + ') > td:nth-child(2)']);
                                if (eleTd != null) {
                                    fetchedClubs.push(eleTd[0].text.trim());
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
