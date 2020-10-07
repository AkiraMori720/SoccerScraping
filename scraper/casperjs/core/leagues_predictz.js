var logger = require('../util/log');

exports.executeScrape = function(countryList, callback) {
    var fetchedLeagues = {};

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
            casper.waitForSelector('#selectnav',
                function(){
                    processPage()
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
            callback(fetchedLeagues);
        }
    };

    var processPage = function() {
        var optionsSelector = '#selectnav > optgroup > option';
        var eleLeagues = findElementsInfo([optionsSelector]);
        if(eleLeagues != null) {
            for(var i = 0; i < eleLeagues.length; i++) {
                var leagueName = eleLeagues[i].text.trim();

                var foundCountry = null;
                for(var k = 0; k < countryList.length; k++) {
                    if(leagueName.indexOf(countryList[k]) >= 0) {
                        foundCountry = countryList[k];
                        break;
                    }
                }

                if(foundCountry != null) {
                    if(fetchedLeagues[foundCountry] == undefined || fetchedLeagues[foundCountry] == null) {
                        fetchedLeagues[foundCountry] = {};
                    }

                    var league = leagueName.substr(foundCountry.length + 1);
                    var link   = eleLeagues[i].attributes['value'];
                    if(fetchedLeagues[foundCountry][league] == undefined || fetchedLeagues[foundCountry][league] == null) {
                        fetchedLeagues[foundCountry][league] = link;
                    }
                }
            }

            terminate();
        }
        else {
            terminate();
        }
    };

    startProcess();
};
