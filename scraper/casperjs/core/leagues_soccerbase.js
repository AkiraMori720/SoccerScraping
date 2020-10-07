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
            casper.waitForSelector('#teamsMenu',
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

    var _gRegionEles_ = [], _gCurRegionIndex_ = -1;
    var processPage = function() {
        var regionSelector = '#teamsMenu > div:nth-child(1) > ol > li';
        _gRegionEles_ = findElementsInfo([regionSelector]);
        if(_gRegionEles_ != null) {
            processRegion();
        }
        else {
            terminate();
        }
    };

    var processRegion = function() {
        _gCurRegionIndex_ ++;
        if(_gCurRegionIndex_ >= _gRegionEles_.length) {
            terminate();
        }
        else {
            var regionSelector = '#teamsMenu > div:nth-child(1) > ol > li';
            var aSelector = regionSelector + ':nth-child(' + (_gCurRegionIndex_+1) + ') > a';
            var ele = findElementsInfo([aSelector]);
            if(ele != null) {
                var regionName = _gRegionEles_[_gCurRegionIndex_].text.trim();
                var foundCountry = null;
                if(countryList.indexOf(regionName) >= 0) {
                    foundCountry = regionName;

                    if(fetchedLeagues[foundCountry] == undefined || fetchedLeagues[foundCountry] == null) {
                        fetchedLeagues[foundCountry] = {};
                    }
                }

                casper.mouse.move(aSelector, 30, 20);
                casper.then(function(){
                    var leaguesSelector = '#teamsMenu > div:nth-child(2) ol:nth-child(' + (_gCurRegionIndex_ + 2) + ')';
                    casper.waitUntilVisible(
                        leaguesSelector,
                        function(){
                            var eleLeagues = findElementsInfo([leaguesSelector + ' > li > a']);
                            if(eleLeagues != null) {
                                for(var k = 0; k < eleLeagues.length; k++) {
                                    var leagueName = eleLeagues[k].text.trim();

                                    var countryByLeagueName = null;
                                    if(leagueName == 'Serie A') {
                                        countryByLeagueName = 'Italian';
                                    }
                                    else if(leagueName == 'Bundesliga') {
                                        countryByLeagueName = 'German';
                                    }
                                    else if(leagueName == 'La Liga') {
                                        countryByLeagueName = 'Spanish';
                                    }
                                    else if(leagueName == 'Ligue 1') {
                                        countryByLeagueName = 'French';
                                    }

                                    if(countryList.indexOf(countryByLeagueName) !== -1) {
                                        if(fetchedLeagues[countryByLeagueName] == undefined || fetchedLeagues[countryByLeagueName] == null) {
                                            fetchedLeagues[countryByLeagueName] = {};
                                        }
                                    }
                                    else {
                                        countryByLeagueName = null;
                                    }

                                    if(foundCountry == null && countryByLeagueName == null) {
                                        for(var s = 0; s < countryList.length; s++) {
                                            if(leagueName.indexOf(countryList[s]) != -1) {
                                                if(fetchedLeagues[countryList[s]] == undefined || fetchedLeagues[countryList[s]] == null) {
                                                    fetchedLeagues[countryList[s]] = {};
                                                }

                                                fetchedLeagues[countryList[s]][leagueName] = "https://www.soccerbase.com" + eleLeagues[k].attributes['href'];
                                            }
                                        }
                                    }
                                    else {
                                        if(foundCountry != null) {
                                            fetchedLeagues[foundCountry][leagueName] = "https://www.soccerbase.com" + eleLeagues[k].attributes['href'];
                                        }
                                        else {
                                            fetchedLeagues[countryByLeagueName][leagueName] = "https://www.soccerbase.com" + eleLeagues[k].attributes['href'];
                                        }
                                    }
                                }
                            }

                            processRegion();
                        },
                        function(){
                            processRegion();
                        },
                        5000
                    );
                });
            }
            else {
                processRegion();
            }
        }
    };

    startProcess();
};
