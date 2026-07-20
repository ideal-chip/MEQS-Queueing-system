// iDEAL-Q — feedback report page (beaa/admin/feedbacks.php)
// Uses `values` (5 x 5 rating-distribution arrays) and `max` declared inline
// in the page, plus ChartjsUtils from Chartjs_utils.js.

$(document).ready(function () {
    if (typeof $.fn.datepicker === 'function') {
        $('#date_start, #date_end').datepicker({dateFormat: 'yy-mm-dd'});
    }

    var ratingLabels = ['1', '2', '3', '4', '5'];
    for (var i = 1; i <= 5; i++) {
        ChartjsUtils.barChart('chart' + i, ratingLabels, [
            {label: lang_feedbacks, data: values[i - 1]}
        ], {scales: {yAxes: [{ticks: {beginAtZero: true, suggestedMax: max}}]}});
    }
});

function PrintChart(elementId) {
    ChartjsUtils.printSection(elementId);
}
