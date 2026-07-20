// iDEAL-Q — admin follow-up cards report (beaa/admin/followups.php)
// Renders the "cards per category" summary chart (chart1) from the
// `values`/`labels`/`max` globals declared inline in the page.

$(document).ready(function () {
    if (typeof $.fn.datepicker === 'function') {
        $('#date_start, #date_end').datepicker({dateFormat: 'yy-mm-dd'});
    }

    ChartjsUtils.barChart('chart1', labels, [
        {label: lang_title, data: values}
    ], {scales: {yAxes: [{ticks: {beginAtZero: true, suggestedMax: max}}]}});
});

function PrintChart(elementId) {
    ChartjsUtils.printSection(elementId);
}

function exportData(elementId) {
    ChartjsUtils.exportTableToExcel(elementId, elementId);
}
