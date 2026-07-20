// iDEAL-Q — per-subcategory wait-time sparklines on beaa/admin/followups.php
// (the "Charts" tab). Renders each entry in `chartsData` (declared inline in
// the page) as a small Chartist line chart into #chart_<subcategory_id>.

$(document).ready(function () {
    if (typeof chartsData === 'undefined' || !chartsData) return;

    chartsData.forEach(function (entry) {
        var el = document.getElementById('chart_' + entry.id);
        if (!el || !entry.data || !entry.data.length) return;

        new Chartist.Line('#chart_' + entry.id, {
            labels: entry.data.map(function (_, i) { return i + 1; }),
            series: [entry.data]
        }, {
            showPoint: true,
            showArea: true,
            fullWidth: true,
            axisX: {showLabel: false, showGrid: false},
            lineSmooth: Chartist.Interpolation.simple({divisor: 2})
        });
    });
});
