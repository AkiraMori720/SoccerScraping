var spinnerOptions = {
    lines   : 15,       // The number of lines to draw
    length  : 12,       // The length of each line
    width   : 3,        // The line thickness
    radius  : 10,       // The radius of the inner circle
    color   : '#FFF',   // #rbg or #rrggbb
    speed   : 1,        // Rounds per second
    trail   : 60,       // Afterglow percentage
    shadow  : false,    // Whether to render a shadow
    opacity : 0.25
};

var spinnerSmallOpts = {
    lines   : 11,       // The number of lines to draw
    length  : 4,        // The length of each line
    width   : 2,        // The line thickness
    radius  : 4,        // The radius of the inner circle
    color   : '#333',   // #rbg or #rrggbb
    speed   : 1,        // Rounds per second
    trail   : 60,       // Afterglow percentage
    shadow  : false,    // Whether to render a shadow
    opacity : 0.25
};

(function ($) {
    $.fn.loadNicely = function (options) {

        var defaults = {
            preLoad: function (img) { },
            onLoad: function (img) { $(img).fadeIn(200); }
        };

        var options = $.extend(defaults, options);

        return this.each(function () {
            if (!this.complete) {
                options.preLoad(this);
                $(this).load(function () { options.onLoad(this); }).attr("src", this.src);
            }
            else {
                options.onLoad(this);
            }
        });
    };
})(jQuery);