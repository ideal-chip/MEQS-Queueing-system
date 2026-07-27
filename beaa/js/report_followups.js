
$(document).ready(function () {

    fillChart();
});

//==============================================================|| charts
var chart1;
var conf1;
var ctx1 = document.getElementById("chart1");
var color = Chart.helpers.color;
var finished = false;

Chart.defaults.global.defaultFontColor = '#444';
Chart.defaults.global.defaultFontFamily = "'Segoe UI Light','Segoe UI Semilight','Segoe UI',Helvetica,Tahoma,Geneva,Verdana,sans-seri";

$("#date_start").datepicker({
    dateFormat: "yy-mm-dd"
});
$("#date_end").datepicker({
    dateFormat: "yy-mm-dd"
});

var barChartData = {
    labels: labels,
    datasets: [{
            label: lang_mainService,
            backgroundColor: color(window.chartColors.red).alpha(0.5).rgbString(),
            borderColor: window.chartColors.red,
            borderWidth: 1,
            data: values
        }]

};

function fillChart() {
    window.myBar = new Chart(ctx1, {
        type: 'bar',
        data: barChartData,
        options: {
            responsive: true,
            legend: {
                position: 'top',
                fontSize: 16,
            },
            title: {
                display: true,
                text: lang_title,
                fontSize: 16
            },
            scales: {
                yAxes: [{
                        ticks: {
                            min: 0,
                            max: max
                        }
                    }]
            },
            layout: {
                padding: {
                    left: 50,
                    right: 0,
                    top: 0,
                    bottom: 0
                }
            },
            animation: {

                onComplete: createImageCanvas
            }
        }
    });
}

function createImageCanvas() {
    if (!finished) {
        finished = true;
        var canvas = document.getElementById('chart1');
        dataUrl = canvas.toDataURL('image/png', 1.0);
        imageFoo = document.getElementById("img");
        imageFoo.src = dataUrl;

// Style your image here
        imageFoo.style.width = '100%';
        imageFoo.style.height = '100%';

// After you are done styling it, append it to the BODY element
//        document.body.appendChild(imageFoo);
    }

}
function PrintChart(id) {

    var item = document.getElementById(id);
    var clone = item.cloneNode(true);
    $(clone).find("#fig1").remove();
    $(clone).find("#img-fig").show();
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

