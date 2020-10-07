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
            filterCountries.push(countryList[i].split(/[ -]/).join(''));
        }

        if(casper == null || casper == undefined) {
            failedOpenLink();
        }
        else {
            var link = 'https://www.windrawwin.com/predictions/' + curDate;
            casper.open(link).then(function(){
                casper.waitForSelector('div[class^="contentfull"]',
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
        var divSelector = 'div[class^="contentfull"] > div';
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

                    if(clsDIV.indexOf('prfl w100p darkrow') !== -1) {
                        var selector = divEleSelector + '> div[class^="ptleag"]';
                        var eleDivision = findElementsInfo([selector]);
                        if(eleDivision != null) {
                            var text = eleDivision[0].text;

                            var foundCountry = null;
                            for(var c = 0; c < filterCountries.length; c++) {
                                if (text.indexOf(filterCountries[c]) !== -1) {
                                    foundCountry = filterCountries[c];
                                    break;
                                }
                            }

                            if(foundCountry != null) {
                                curCountry = foundCountry;
                                curDivision= text.replace(foundCountry, '').trim();
                            }
                            else {
                                curCountry = '';
                                curDivision = '';
                            }
                        }
                    }
                    else if((clsDIV.indexOf('wdwtablest') !== -1 || clsDIV.indexOf('widetable') !== -1) && curCountry.length > 0) {
                        if(curDate == 'today' || curDate.indexOf('future') != -1) {
                            var rowSelector = divEleSelector + ' > div';
                            var rowEles = findElementsInfo([rowSelector]);
                            if(rowEles != null) {
                                for(var r = 0; r < rowEles.length; r++) {
                                    // Score
                                    var score = {
                                        type : "",
                                        value: "",
                                        result: ""
                                    };

                                    var selector = rowSelector + ':nth-child(' + (r + 1) + ') > div[class="wtl5fcont"] > div:nth-child(2) a';
                                    var eleA = findElementsInfo([selector]);
                                    var team_1 = '', team_2 = '';
                                    if(eleA != null) {
                                        var linkDetails = eleA[0].attributes['href'].split('/').filter(function(x){ return x.trim().length > 0; });

                                        if(linkDetails.length >= 2) {
                                            var tmp = linkDetails[linkDetails.length - 2].split("-v-");
                                            team_2 = tmp[1];

                                            var text = eleA[0].text.trim();
                                            team_1 = text.substr(0, tmp[0].length);

                                            if(text.indexOf(" v ") > 0) {
                                                tmp = text.split(" v ");
                                                team_1 = tmp[0];
                                                team_2 = tmp[1];
                                            }
                                            else {
                                                text = text.substr(team_1.length + 1);
                                                tmp = text.split((' '));

                                                team_2 = text.substr(tmp[0].length + 1, text.length - tmp[tmp.length - 1].length - 2).trim();
                                                score.result = tmp[0] + ":" + tmp[tmp.length - 1];
                                            }
                                        }
                                    }
                                    else {
                                        continue;
                                    }


                                    selector = rowSelector + ':nth-child(' + (r + 1) + ') div[class="wttd wtprd"]';
                                    var ele = findElementsInfo([selector]);
                                    if(ele != null) {
                                        score.type = ele[0].text.trim();
                                    }

                                    selector = rowSelector + ':nth-child(' + (r + 1) + ') div[class="wttd wtsc"]';
                                    var ele = findElementsInfo([selector]);
                                    if(ele != null) {
                                        score.value = ele[0].text.trim();
                                    }

                                    matches.push({
                                        country : curCountry,
                                        division: curDivision,
                                        team_1  : team_1,
                                        team_2  : team_2,
                                        result  : score.type,
                                        score   : score.value,
                                        real_score : score.result
                                    });
                                }
                            }
                        }
                        else {
                            var trSelector = divEleSelector + '> table > tbody > tr';
                            var eleTRs = findElementsInfo([trSelector]);
                            if(eleTRs != null) {
                                for(var r = 0; r < eleTRs.length; r++) {
                                    // Score
                                    var score = {
                                        type : "",
                                        value: "",
                                        result: ""
                                    };

                                    var selector = trSelector + ":nth-child(" + (r + 1) + ") td:nth-child(1) a";
                                    var eleA = findElementsInfo([selector]);

                                    var team_1 = '', team_2 = '';
                                    if(eleA != null) {
                                        var linkDetails = eleA[0].attributes['href'].split('/').filter(function(x){ return x.trim().length > 0; });

                                        if(linkDetails.length >= 2) {
                                            var tmp = linkDetails[linkDetails.length - 2].split("-v-");
                                            team_2 = tmp[1];

                                            var text = eleA[0].text.trim();
                                            team_1 = text.substr(0, tmp[0].length);
                                            text = text.substr(team_1.length + 1);
                                            tmp = text.split((' '));

                                            team_2 = text.substr(tmp[0].length + 1, text.length - tmp[tmp.length - 1].length - 2).trim();
                                            score.result = tmp[0] + ":" + tmp[tmp.length - 1];
                                        }
                                    }

                                    selector = trSelector + ":nth-child(" + (r + 1) + ") td:nth-child(3)";
                                    var eleTD = findElementsInfo([selector]);
                                    if(eleTD != null) {
                                        score.type = eleTD[0].text.trim();
                                    }
                                    else {
                                        continue;
                                    }

                                    selector = trSelector + ":nth-child(" + (r + 1) + ") td:nth-child(4)";
                                    eleTD = findElementsInfo([selector]);
                                    if(eleTD != null) {
                                        score.value = eleTD[0].text.trim();
                                    }

                                    matches.push({
                                        country : curCountry,
                                        division: curDivision,
                                        team_1  : team_1,
                                        team_2  : team_2,
                                        result  : score.type,
                                        score   : score.value,
                                        real_score : score.result
                                    });
                                }
                            }
                        }
                    }
                }
            }
        }

        terminate();
    };

    startProcess();
};
