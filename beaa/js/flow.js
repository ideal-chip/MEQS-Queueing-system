$(document).ready(function () {
    fillChartAllCategories();
    fillChartCategories();
    fillChartCounters();
});

//===============================================================================|| dates

$("#date_start").datepicker({
    dateFormat: "yy-mm-dd"
});

$("#date_end").datepicker({
    dateFormat: "yy-mm-dd"
});

//===============================================================================|| refresh page
setTimeout(function () {
    //reloadFlow();
}, 15000);

function reloadFlow() {
    $.ajax({
        type: 'get',
        dataType: 'json',
        cache: false,
        url: '../api/update.php',
        data: {id: 1, type: 'flow'},
        success: function (response, textStatus, jqXHR) {

            if (response && response == 1) {
                location.reload();
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
        }
    });
}
//===============================================================================|| Charts [charts.js]

var finished = [false, false, false];

var color = Chart.helpers.color;
Chart.defaults.global.defaultFontColor = '#444';
Chart.defaults.global.defaultFontFamily = "'Segoe UI Light','Segoe UI Semilight','Segoe UI',Helvetica,Tahoma,Geneva,Verdana,sans-seri";

var colorNames = Object.keys(window.chartColors);

function createImageCanvas() {
    if (!finished) {
        finished = true;
        var canvas = document.getElementById('chart1');
        var dataUrl = canvas.toDataURL('image/png', 1.0);

        var imgFoo = $("#img-fig1 img");
        $(imgFoo).attr("src", dataUrl);
        $(imgFoo).css("width", '100%');
        $(imgFoo).css("height", '100%');
    }

}

function fillChartAllCategories() {

    var config = {
        type: 'pie',
        data: {
            datasets: [{
                    data: allCategoriesData,
                    backgroundColor: [
                        window.chartColors.red,
                        window.chartColors.purple,
                        window.chartColors.yellow,
                        window.chartColors.green
                    ],
                    label: 'Dataset 1'
                }],
            labels: allCategoriesDataNames
        },
        options: {
            responsive: true,
            legend: {
                position: 'left'
            },
            animation: {
                onComplete: function () {
                    if (!finished[0]) {
                        finished[0] = true;
                        var canvas = document.getElementById('chart1');
                        var dataUrl = canvas.toDataURL('image/png', 1.0);

                        var imgFoo = $("#img-fig1 img");
                        $(imgFoo).attr("src", dataUrl);
                        $(imgFoo).css("width", '100%');
                        $(imgFoo).css("height", '100%');
                    }

                }
            }
        }
    };

    var ctx = document.getElementById('chart1').getContext('2d');
    window.myPie = new Chart(ctx, config);
}


function fillChartCategories() {
    var horizontalBarChartData = {
        labels: categoryNames,
        datasets: [{
                label: allCategoriesDataNames[0],
                backgroundColor: color(window.chartColors.red).alpha(0.8).rgbString(),
                borderColor: window.chartColors.red,
                borderWidth: 1,
                data: ticketsNo
            }, {
                label: allCategoriesDataNames[1],
                backgroundColor: color(window.chartColors.blue).alpha(0.8).rgbString(),
                borderColor: window.chartColors.blue,
                data: ticketsWaiting
            }, {
                label: allCategoriesDataNames[2],
                backgroundColor: color(window.chartColors.yellow).alpha(0.8).rgbString(),
                borderColor: window.chartColors.yellow,
                data: ticketsTransfered
            }, {
                label: allCategoriesDataNames[3],
                backgroundColor: color(window.chartColors.purple).alpha(0.8).rgbString(),
                borderColor: window.chartColors.purple,
                data: ticketsServed
            }]

    };


    var ctx = document.getElementById('chart2').getContext('2d');
    window.myHorizontalBar = new Chart(ctx, {
        type: 'bar',
        data: horizontalBarChartData,
        options: {
            // Elements options apply to all of the options unless overridden in a dataset
            // In this case, we are setting the border of each horizontal bar to be 2px wide
            elements: {
                rectangle: {
                    borderWidth: 1,
                }
            },
            responsive: true,
            legend: {
                position: 'top'
            },
            title: {
                display: false,
                text: lang_categories
            },
            animation: {
                onComplete: function () {
                    if (!finished[1]) {
                        finished[1] = true;
                        var canvas = document.getElementById('chart2');
                        var dataUrl = canvas.toDataURL('image/png', 1.0);

                        var imgFoo = $("#img-fig2 img");
                        $(imgFoo).attr("src", dataUrl);
                        $(imgFoo).css("width", '75%');
                        $(imgFoo).css("height", '75%');
                    }
                }
            }
        }
    });
}

function fillChartCounters() {
    var horizontalBarChartData = {
        labels: counterNumbers,
        datasets: [{
                label: lang_counterLoad,
                backgroundColor: color(window.chartColors.red).alpha(0.8).rgbString(),
                borderColor: window.chartColors.red,
                borderWidth: 1,
                data: counterLoads
            }, {
                label: lang_counterPending,
                backgroundColor: color(window.chartColors.blue).alpha(0.8).rgbString(),
                borderColor: window.chartColors.blue,
                data: counterPendings
            }]

    };


    var ctx = document.getElementById('chart3').getContext('2d');
    window.myHorizontalBar = new Chart(ctx, {
        type: 'bar',
        data: horizontalBarChartData,
        options: {
            // Elements options apply to all of the options unless overridden in a dataset
            // In this case, we are setting the border of each horizontal bar to be 2px wide
            elements: {
                rectangle: {
                    borderWidth: 1,
                }
            },
            responsive: true,
            legend: {
                position: 'top'
            },
            title: {
                display: false,
                text: lang_counterLoad
            },
            animation: {
                onComplete: function () {
                    if (!finished[2]) {
                        finished[2] = true;
                        var canvas = document.getElementById('chart3');
                        var dataUrl = canvas.toDataURL('image/png', 1.0);

                        var imgFoo = $("#img-fig3 img");
                        $(imgFoo).attr("src", dataUrl);
                        $(imgFoo).css("width", '80%');
                        $(imgFoo).css("height", '80%');
                    }

                }
            }
        }
    });
}
//===============================================================================|| printing
function printChart(id) {

    var item = document.getElementById(id);
    var clone = item.cloneNode(true);

    for (var i = 0; i < 3; i++) {
        $(clone).find("#fig" + (i + 1)).hide();
        $(clone).find("#img-fig" + (i + 1)).show();
    }

    $(clone).find("#top-row").addClass("marg-v-20");

    $(clone).addClass("no-marg");
    $(clone).removeClass("marg-v-50");


    $(clone).print({
        globalStyles: true,
        mediaPrint: false,
        iframe: true,
        noPrintSelector: ".no-print",
        prepend: "<img class='text-center' src='../files/logos/env-logo.jpg' alt=''/><h6><hr>" + title + "</h6><hr>",
        append: "<hr><p class='small'>reported by idealchip iDEAL-Q QMS - on " + todayDate + "</p>"

    });

}

function printBox(id, imgNum) {
    
    var item = document.getElementById(id);
    
    var clone = item.cloneNode(true);
    
    var dateItem = document.getElementById("feedback-form");
    var dateClone = dateItem.cloneNode(true);
    $(dateClone).prependTo(clone);

    if (imgNum > 0) {
        $(clone).find("#fig" + imgNum).hide();
        $(clone).find("#img-fig" + imgNum).show();
    }
    
    $(clone).print({
        globalStyles: true,
        mediaPrint: false,
        iframe: true,
        noPrintSelector: ".no-print",
        prepend: "<img class='text-center' src='../files/logos/env-logo.jpg' alt=''/><h6><hr>" + title + "</h6><hr>",
        append: "<hr><p class='small'>reported by idealchip iDEAL-Q QMS - on " + todayDate + "</p>"

    });

}