
$(document).ready(function () {

    setTimeout(function () {
        fillCharts();
    }, 100);
});

var options = {
//    chartPadding: 10,
    lineSmooth: Chartist.Interpolation.none({
        fillHoles: false
    }),
    series: {
        'series-2': {
            showPoint: false
        }
    },
    axisX: {
//            type: Chartist.AutoScaleAxis,
        divisor: 1
    }
};

//==============================================================|| charts

function getDataXY(ch1, waitTime) {

    var labelArr = [];
    var dataArr = [];
    var linArr = [];

//    console.log(ch1);
    var obj = {x: null, y: null};
    dataArr.push(obj);

    for (var i = 0; i < ch1.length; i++) {

        var item = ch1[i];

        var obj = {x: (i + 1), y: parseInt(item)};
        dataArr.push(obj);

        labelArr.push((i + 1) + "");

    }
    for (var i = 0; i < ch1.length + 15; i++) {

        linArr.push(waitTime);
    }

    var data = {
        labels: labelArr,
        series: [
            {
                name: 'series-1',
                data: dataArr
            },
            {
                name: 'series-2',
                data: linArr
            }
        ]
    };

    return data;
}

function fillCharts() {
    var domCharts = [];
    for (var i = 0; i < chartsData.length; i++) {

        if (chartsData[i].data.length > 0) {
            var data = getDataXY(chartsData[i].data, chartsData[i].wait);
            domCharts[i] = new Chartist.Line('#chart_' + chartsData[i].id, data, options);
        } else {
            $("#chart_" + chartsData[i].id).html("NO DATA");
        }

    }

}



