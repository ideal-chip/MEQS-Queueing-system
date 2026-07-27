$(document).ready(function () {

    $(".btn").mouseup(function () {
        $(this).blur();
    });

});
//==============================================================================|| lang

function updateLang(lang) {
    location.replace("?language=" + lang);
}
//==============================================================================|| time
// get time formatted
function updateTime() {
    $("#time").text(getTime());
}

function getTime() {

    var date = new Date();
    return tow_digit(date.getHours()) + ":" + tow_digit(date.getMinutes()) + ":" + tow_digit(date.getSeconds());
}

function getDate() {

    var date = new Date();
    return tow_digit(date.getDate()) + "/" + tow_digit(date.getMonth() + 1) + "/" + tow_digit(date.getFullYear());
}

function getTimeShort() {

    var date = new Date();
    return tow_digit(date.getMinutes()) + ":" + tow_digit(date.getSeconds());
}

function tow_digit(num) {
    return (num > 9) ? num : "0" + num;
}

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|| Export Excel
function exportData(report_id) {
    var item = document.getElementById(report_id);
    var clone = item.cloneNode(true);

    $(clone).find(".tb-remove").remove();
    $(clone).find("a").contents().unwrap();
    $(clone).find("img").remove();
    var hdStyle = "color: #fff;background-color: #2196f3;";
//    var rowStyle = "border-bottom: 1px solid #333;";
    $(clone).find("thead tr").attr("style", hdStyle);

    $(clone).find("tr").each(function () {
        if ($(this).text().trim() == "") {
            $(this).remove();
        }
    });
    var blob = new Blob([clone.innerHTML], {
        type: "text/plain;charset=utf-8;"
    });
    saveAs(blob, "Report-" + report_id + ".xls");
}
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|| Printing
function printElement(id, hdr, ftr) {
    var item = document.getElementById(id);
    var clone = item.cloneNode(true);

    hdr = (!hdr || hdr == "") ? "" : "<h5>" + hdr + "</h5>";
    ftr = (!ftr || ftr == "") ? "" : ftr;

//console.log(hdr);

    $(clone).print({
        //Use Global styles
        globalStyles: true,
        //Add link with attrbute media=print
        mediaPrint: false,
        //Custom stylesheet
//            stylesheet : "../css/common.css",
        //Print in a hidden iframe
        iframe: true,
        //Don't print this
        noPrintSelector: ".no-print",
        //Add this at top
        //prepend: hdr,
        //Add this on bottom
        //append: ftr,
        //Log to console when printing is done via a deffered callback
//        deferred: $.Deferred().done(function () {
//            console.log('Printing done', arguments);
//        })
    });
}

function printElement2(id) {
    var item = document.getElementById(id);
    var clone = item.cloneNode(true);
    $(clone).find("tr").each(function () {
        if ($(this).text().trim() == "" || $(this).hasClass("hidden")) {
            $(this).remove();
        }
    });

    $(clone).find(".no-print").each(function () {
        $(this).remove();
    });
    $(clone).printThis({
        pageTitle: "Report live minutes",
        header: "<h1>Report: live minutes</h1>", // prefix to html
        footer: "<p class='text-center small'> ® iDEALChip Electronics, Inc. © 1997 - <?php echo date('Y') ?> - iDEAL-shifts: <?php echo date('Y') ?></p>"
    });
}

//==============================================================================|| tables

function renderHead(array, size) {
    var hds = '<tr>';

    for (var i = 0; i < size; i++) {
        hds += "<th>" + array[i] + "</th>";
    }

    return hds + "</tr>";
}
function renderRow(array, size) {
    var tds = '<tr>';

    for (var i = 0; i < size; i++) {
        tds += "<td>" + array[i] + "</td>";
    }

    return tds + "</tr>";
}

//==============================================================================|| Other Functions
//function resizeElement(id) {
//    var iframe = document.getElementById(id);
//    iframe.style.height = 'auto';
//    iframe.style.height = (parseInt(iframe.contentWindow.document.body.scrollHeight) + 50) + 'px';
//}
function arrFromJsonObj(obj) {
    var arr = [];
    for (var x in obj)
        if (obj.hasOwnProperty(x)) {
            arr.push(obj[x]);
        }
    return arr;
}