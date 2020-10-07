var logger = require('../util/log');

exports.executeScrape = function(casper, curDate, countryList, callback) {
    var currentIndex = 0;
    var matches = [];
    var filterCountries = [];

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
        currentIndex = -1;

        for(var i = 0; i < countryList.length; i++) {
            filterCountries.push(countryList[i].toLowerCase().split(/[ -]/).join(''));
        }

        if(casper == null || casper == undefined) {
            failedOpenLink();
        }
        else {
            var link = 'https://www.predictz.com/predictions/' + curDate;
            casper.open(link).then(function(){
                casper.waitForSelector('div[class^="pttable"]',
                    processPage(),
                    function fail(){
                        failedOpenLink();
                    },
                    5000
                );
            });
        }
    }

    var failedOpenLink = function() {
        if(callback) {
            callback();
        }
    };

    var terminate = function() {
        if(callback) {
            callback(matches);
        }
    };

    var processPage = function() {
        var divSelector = 'div[class^="pttable"] > div';
        var eleDIV = findElementsInfo([divSelector]);
        if(eleDIV != null) {
            var curCountry = '';
            var curDivision = '';
            for(var i = 0; i < eleDIV.length; i++) {
                var divEleSelector = divSelector + ':nth-child(' + (i + 1) + ') ';

                var ele = findElementsInfo([divEleSelector]);
                if(ele == null) {
                    continue;
                }

                if(ele[0].attributes['class'] != undefined) {
                    var clsDIV = ele[0].attributes['class'];

                    if(clsDIV.indexOf('pttrnh ptttl') !== -1) {
                        var selector = divEleSelector + 'div[class^="pttd"] h2 a';
                        var eleA = findElementsInfo([selector]);
                        if(eleA != null && eleA[0].attributes['href'] != undefined) {
                            var linkDetails = eleA[0].attributes['href'].split('/').filter(function(x){ return x.trim().length > 0; });
                            var text = eleA[0].text;
                            text = text.substr(0, text.length - 4).trim();

                            if(linkDetails.length >= 2) {
                                var tmpCountry = linkDetails[linkDetails.length - 2];
                                var tmpDivision= linkDetails[linkDetails.length - 1];

                                var newCountry = text.substr(0, tmpCountry.length);
                                var newDivision= text.substr(tmpCountry.length + 1).trim();

                                if(newCountry.length > 0 && curCountry != newCountry) {
                                    curCountry = newCountry;
                                }

                                if(newDivision.length > 0 && curDivision != newDivision) {
                                    curDivision = newDivision;
                                }
                            }
                        }
                    }
                    else if(clsDIV.indexOf('pttr ptcnt') !== -1 &&
                        curCountry.length > 0 &&
                        filterCountries.indexOf(curCountry.toLowerCase().split(" ").join('')) != -1) {

                        // Score
                        var selector = divEleSelector + 'div[class^="pttd ptprd"] > div:nth-child(1)';
                        var ele = findElementsInfo([selector]);
                        var score = {
                            type : "",
                            value: ""
                        };
                        if(ele != null) {
                            var txt = ele[0].text.trim().split(' ');
                            score.type = txt[0];
                            score.value= txt[1];
                        }

                        // Team
                        selector = divEleSelector + 'div[class^="pttd ptgame"] a';
                        ele = findElementsInfo([selector]);
                        var teams = {
                            team_1 : "",
                            team_2 : ""
                        };
                        if(ele != null) {
                            var txt = ele[0].text.trim().split(" v ");
                            teams.team_1 = txt[0];
                            teams.team_2 = txt[1];
                        }

                        matches.push({
                            country     : curCountry,
                            division    : curDivision,
                            team_1      : teams.team_1,
                            team_2      : teams.team_2,
                            result      : score.type,
                            score       : score.value
                        });
                    }
                }
            }
        }

        terminate();
    };

    startProcess();
};
