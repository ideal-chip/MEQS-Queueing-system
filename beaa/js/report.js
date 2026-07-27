
$(document).ready(function () {

    $("#date_start").datepicker({
        dateFormat: "yy-mm-dd"
    });
    $("#date_end").datepicker({
        dateFormat: "yy-mm-dd"
    });

    fillCharts();
});

//==============================================================|| charts
var chart1;
var conf1;
var ctx1 = document.getElementById("chart1");
var color = Chart.helpers.color;
var finished = [false, false, false, false, false];

Chart.defaults.global.defaultFontColor = '#444';
//Chart.defaults.global.defaultFontFamily = "'Segoe UI Light','Segoe UI Semilight','Segoe UI',Helvetica,Tahoma,Geneva,Verdana,sans-seri";


var barChartData = {
    labels: ['Rate 1', 'Rate 2', 'Rate 3', 'Rate 4', 'Rate 5'],
    datasets: [{
            label: lang_feedbacks,
            backgroundColor: color(window.chartColors.red).alpha(0.5).rgbString(),
            borderColor: window.chartColors.red,
            borderWidth: 1,
            data: values
        }]
};

function fillCharts() {

    for (var i = 0; i < 5; i++) {
        var labels = createLables(values[i]);
        var config = {
            type: 'pie',
            data: {
                datasets: [{
                        data: values[i],
                        backgroundColor: [
                            window.chartColors.red,
                            window.chartColors.purple,
                            window.chartColors.yellow,
                            window.chartColors.green,
                            window.chartColors.orange
                        ],
                        label: ''
                    }],
                labels: labels
            },
            options: {
                responsive: true,
                legend: {
                    labels: {
                        usePointStyle: true
                    }
                },
                animation: {
                    onComplete: createImageCanvas
                }
            }
        };

        var ctx = document.getElementById('chart' + (i + 1)).getContext('2d');
        window.myPie = new Chart(ctx, config);
    }

}

function createLables(array) {
    var lables = [];
    for (var i = 0; i < array.length; i++) {
        lables[i] = "star-"+(i+1)+" :" + array[i];
    }

    return lables;
}

function PrintChart(id) {

    var item = document.getElementById(id);
    var clone = item.cloneNode(true);

    for (var i = 0; i < 5; i++) {
        $(clone).find("#fig" + (i + 1)).hide();
        $(clone).find("#img-fig" + (i + 1)).show();
    }

    $(clone).find("#chart-data").removeClass("row s-80 center-block");

    $(clone).find("#charts .col-1").each(function () {
        $(this).addClass("s-15").removeClass("col-1");
    });
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
var index = 0;
function createImageCanvas() {
    if (index < 5) {
        var i = index;
//    for (var i = 0; i < 5; i++) {
        if (!finished[i]) {
            finished[i] = true;
            console.log(i);
            var canvas = document.getElementById('chart' + (i + 1));
            var dataUrl = canvas.toDataURL('image/png', 1.0);

            var imgFoo = $("#img-fig" + (i + 1) + " img");
            $(imgFoo).attr("src", dataUrl);
            $(imgFoo).css("width", '100%');
            $(imgFoo).css("height", '100%');
            index++;
        }
    }
}

