// iDEAL-Q — shared Chart.js / export / print helpers used by flow.js and report.js.
// Chart.js 2.x API (matches the vendored ../js/Chartjs.min.js build).

var ChartjsUtils = (function () {
    var palette = ['#3498db', '#2ecc71', '#f1c40f', '#e74c3c', '#9b59b6', '#1abc9c'];

    function barChart(canvasId, labels, datasets, opts) {
        var ctx = document.getElementById(canvasId);
        if (!ctx) return null;
        var chartDatasets = datasets.map(function (ds, i) {
            return {
                label: ds.label,
                data: ds.data,
                backgroundColor: ds.color || palette[i % palette.length]
            };
        });
        return new Chart(ctx, {
            type: 'bar',
            data: {labels: labels, datasets: chartDatasets},
            options: Object.assign({
                responsive: true,
                maintainAspectRatio: false,
                scales: {yAxes: [{ticks: {beginAtZero: true}}]}
            }, opts || {})
        });
    }

    function exportTableToExcel(elementId, filename) {
        var el = document.getElementById(elementId);
        if (!el) return;
        var html = el.outerHTML || el.innerHTML;
        var template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" ' +
            'xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">' +
            '<head><meta charset="utf-8"></head><body>' + html + '</body></html>';
        var blob = new Blob([template], {type: 'application/vnd.ms-excel'});
        saveAs(blob, (filename || 'export') + '.xls');
    }

    function printSection(elementId) {
        $('#' + elementId).print({
            globalStyles: true,
            mediaPrint: false,
            stylesheet: '../css/paper.bootstrap.min.css,../css/common.css,../css/admin.css'
        });
    }

    return {barChart: barChart, exportTableToExcel: exportTableToExcel, printSection: printSection};
})();
