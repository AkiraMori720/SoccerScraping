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
            casper.waitForSelector('#sports-menu',
                function(){
                    var liSportSelector = '#sports-menu > li';
                    var eleSports = findElementsInfo([liSportSelector]);
                    if(eleSports != null) {
                        var foundIndex = -1;
                        for(var i = 0; i < eleSports.length; i++) {
                            var eleSport = findElementsInfo([liSportSelector + ":nth-child(" + (i + 1) + ") div[class='sport_name'] > a"]);
                            if(eleSport != null && eleSport[0].text.trim() == "Soccer") {
                                foundIndex = i;
                                break;
                            }
                        }

                        casper.thenClick(liSportSelector + ":nth-child(" + (foundIndex + 1) + ") div[class='sport_name'] > a", function(){
                            casper.waitUntilVisible(
                                liSportSelector + ":nth-child(" + (foundIndex + 1) + ") div[id^='s_'] > ul",
                                function() {
                                    processPage();
                                },
                                function() {
                                    terminate()
                                },
                                5000
                            );
                        });
                    }
                    else {
                        terminate();
                    }
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

    var _gEleCountries_ = null, _gCurCountryIndex_ = -1;
    var processPage = function() {
        var liCountrySelector = '#sports-menu > li[class="sport"] > div[id="s_1"] > ul > li';
        _gEleCountries_ = findElementsInfo([liCountrySelector]);
        if(_gEleCountries_ != null) {
            processCountry();
        }
        else {
            terminate();
        }
    };

    var processCountry = function() {
        _gCurCountryIndex_ ++;
        if(_gCurCountryIndex_ >= _gEleCountries_.length) {
            terminate();
        }
        else {
            var liCountrySelector = '#sports-menu > li[class="sport"] > div[id="s_1"] > ul > li';

            if (_gEleCountries_[_gCurCountryIndex_].attributes['class'] != undefined && _gEleCountries_[_gCurCountryIndex_].attributes['class'].indexOf("country") >= 0) {
                var liSelector = liCountrySelector + ':nth-child(' + (_gCurCountryIndex_ + 1) + ')';
                var aEleSelector = liSelector + ' > a';

                var ele = findElementsInfo([aEleSelector]);
                var foundCountry = ele[0].text.trim();
                if(ele != null && countryList.indexOf(foundCountry) >= 0) {
                    casper.thenClick(aEleSelector, function(){
                        casper.waitUntilVisible(
                            liSelector + ' > ul',
                            function() {
                                var liLeagueSelector = liSelector + ' > ul > li > a';
                                var eleLeague = findElementsInfo([liLeagueSelector]);
                                if(eleLeague != null) {
                                    for(var i = 0; i < eleLeague.length; i++) {
                                        var leagueName = eleLeague[i].text.trim();

                                        if(fetchedLeagues[foundCountry] == undefined || fetchedLeagues[foundCountry] == null) {
                                            fetchedLeagues[foundCountry] = {};
                                        }

                                        fetchedLeagues[foundCountry][leagueName] = "https://www.oddsportal.com" + eleLeague[i].attributes['href'] + "standings/";
                                    }
                                }
                                processCountry();
                            },
                            function() {
                                processCountry();
                            },
                            5000
                        );
                    });
                }
                else {
                    processCountry();
                }
            }
            else {
                processCountry();
            }
        }
    };

    startProcess();
};
