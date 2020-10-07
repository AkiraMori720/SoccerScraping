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
            casper.waitForSelector('#page_teams_1_block_teams_index_club_teams_2',
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

    var _gCountriesIndex_ = [], _gCurCountryIndex_ = -1;
    var processPage = function() {
        var liSelector = '#page_teams_1_block_teams_index_club_teams_2 > ul > li';
        var eleCountries = findElementsInfo([liSelector]);
        if(eleCountries != null) {
            for(var i = 0; i < eleCountries.length; i++) {
                if(eleCountries[i].attributes['class'] == undefined || eleCountries[i].attributes['class'] == null || eleCountries[i].attributes['class'].indexOf('expandable') == -1) {
                    continue;
                }

                var ele = findElementsInfo([liSelector + ':nth-child(' + (i+1) + ') div > a']);
                if(ele != null) {
                    var foundCountry = ele[0].text.trim();
                    if(countryList.indexOf(foundCountry) >= 0) {
                        _gCountriesIndex_.push({
                            country : foundCountry,
                            index : i
                        });
                    }
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
        if(_gCurCountryIndex_ >= _gCountriesIndex_.length) {
            terminate();
        }
        else {
            var liSelector = '#page_teams_1_block_teams_index_club_teams_2 > ul > li:nth-child(' + (_gCountriesIndex_[_gCurCountryIndex_].index+1) + ')';
            casper.thenClick(liSelector + " > div > a", function(){
                var ulSelector = liSelector + ' > ul';
                casper.waitUntilVisible(
                    ulSelector,
                    function(){
                        var eleLeagues = findElementsInfo([ulSelector + ' > li > div > a']);
                        if(eleLeagues != null) {
                            var foundCountry = _gCountriesIndex_[_gCurCountryIndex_].country;
                            for(var i = 0; i < eleLeagues.length; i++) {
                                if(fetchedLeagues[foundCountry] == undefined || fetchedLeagues[foundCountry] == null) {
                                    fetchedLeagues[foundCountry] = {};
                                }

                                var league = eleLeagues[i].text.trim();
                                var link   = "https://int.soccerway.com" + eleLeagues[i].attributes['href'];
                                if(fetchedLeagues[foundCountry][league] == undefined || fetchedLeagues[foundCountry][league] == null) {
                                    fetchedLeagues[foundCountry][league] = link;
                                }
                            }
                        }

                        processCountry();
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
