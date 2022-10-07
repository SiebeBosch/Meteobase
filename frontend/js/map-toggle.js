$(document).ready(function () {
    $('#toetsingsdata-year-select').click(function (event) {
        $('#toetsingsdata-map-winter').css('display', 'none');
        $('#toetsingsdata-map-year').css('display', 'inline');
    });

    $('#toetsingsdata-winter-select').click(function (event) {
        $('#toetsingsdata-map-year').css('display', 'none');
        $('#toetsingsdata-map-winter').css('display', 'inline');
    });
});