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
            casper.waitForSelector('#navlist2',
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

    var _gCountriesLinks_ = [], _gCurCountryIndex_ = -1;
    var processPage = function() {
        var liCountrySelector = '#navlist2 > li > a';
        var eleCountries = findElementsInfo([liCountrySelector]);
        if(eleCountries != null) {
            for(var i = 0; i < eleCountries.length; i++) {
                var foundCountry = eleCountries[i].text.trim();
                if(countryList.indexOf(foundCountry) >= 0) {
                    _gCountriesLinks_.push({
                        country: foundCountry,
                        link : "https://www.soccervista.com" + eleCountries[i].attributes['href']
                    });
                }
            }

            processCountry();
        }
        else {
            terminate();
        }
    };

    var processCountry = function() {
        _gCurCountryIndex_ ++;
        if(_gCurCountryIndex_ >= _gCountriesLinks_.length) {
            terminate();
        }
        else {
            casper.thenOpen(_gCountriesLinks_[_gCurCountryIndex_].link, function(){
                var parentMenuSelector = 'div[class^="menu2"] > ul > li[class^="longer"]';
                casper.waitForSelector(
                    parentMenuSelector,
                    function(){
                        casper.mouse.move(parentMenuSelector, 30, 20);
                        casper.waitUntilVisible(
                            parentMenuSelector + '> ul',
                            function(){
                                var eleLeagues = findElementsInfo([parentMenuSelector + ' > ul > li > a']);
                                if(eleLeagues != null) {
                                    var foundCountry = _gCountriesLinks_[_gCurCountryIndex_].country;
                                    for(var i = 0; i < eleLeagues.length; i++) {
                                        if(fetchedLeagues[foundCountry] == undefined || fetchedLeagues[foundCountry] == null) {
                                            fetchedLeagues[foundCountry] = {};
                                        }

                                        var league = eleLeagues[i].text.trim();
                                        var link   = "https://www.soccervista.com" + eleLeagues[i].attributes['href'];
                                        if(fetchedLeagues[foundCountry][league] == undefined || fetchedLeagues[foundCountry][league] == null) {
                                            fetchedLeagues[foundCountry][league] = link;
                                        }
                                    }
                                }

                                processCountry();
                            },
                            function() {
                                processCountry();
                            },
                            5000
                        );
                    },
                    function(){
                        processCountry();
                    },
                    5000
                );
            });
        }
    };

    startProcess();
};
