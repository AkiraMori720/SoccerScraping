$(document).ready(function() {
    $('.btn-go').off().on('click', function(e) {
        e.preventDefault();

        window.location.href = base_url + 'index';
    });
});