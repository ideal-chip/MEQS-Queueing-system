// iDEAL-Q — admin flow/statistics dashboard (beaa/admin/flow.php)
// Uses the data arrays (ticketsNo, categoryNames, counterLoads, ...) declared
// inline in the page, plus ChartjsUtils from Chartjs_utils.js.

$(document).ready(function () {
    if (typeof $.fn.datepicker === 'function') {
        $('#date_start, #date_end').datepicker({dateFormat: 'yy-mm-dd'});
    }

    ChartjsUtils.barChart('chart1', allCategoriesDataNames, [
        {label: title, data: allCategoriesData}
    ]);

    ChartjsUtils.barChart('chart2', categoryNames, [
        {label: lang_categories, data: ticketsNo, color: '#3498db'},
        {label: lang_counterPending, data: ticketsWaiting, color: '#f1c40f'}
    ]);

    ChartjsUtils.barChart('chart3', counterNumbers, [
        {label: lang_counterLoad, data: counterLoads, color: '#2ecc71'},
        {label: lang_counterPending, data: counterPendings, color: '#e74c3c'}
    ]);
});

function printChart(elementId) {
    ChartjsUtils.printSection(elementId);
}

function printBox(elementId) {
    ChartjsUtils.printSection(elementId);
}

function exportData(elementId) {
    ChartjsUtils.exportTableToExcel(elementId, elementId);
}
