// iDEAL-Q — admin big-display bulk-calling settings page
// (delay stepper, start/stop bulk calling, recall-all, add/edit/delete already
// work via plain links + confirm() inline in list.php)

var bulkIsActive = false;

function updateDelay(direction) {
    var $val = $('#delayVal');
    var value = parseInt($val.text(), 10) || 0;
    value = direction === 'up' ? value + 1 : Math.max(0, value - 1);
    $val.text(value);
}

function setDelay() {
    var value = parseInt($('#delayVal').text(), 10) || 0;
    $.get('../api/update.php?type=bulkdelay&value=' + value, function (data) {
        if (data !== 0 && data !== '0') {
            var $btn = $('#bulkBtn');
            var original = $btn.html();
            $btn.html(updateText);
            setTimeout(function () { $btn.html(original); }, 1500);
        }
    });
}

function setBulkStatus(status) {
    $.get('../api/update.php?type=bulkstatus&status=' + status, function () {
        pollBulkStatus();
    });
}

function updateAll() {
    $.get('../api/update.php?type=allbigdisplay', function () {
        var $link = $('#recall-all .txt');
        var original = $link.text();
        $link.text(updateText);
        setTimeout(function () { $link.text(recallAllText); }, 1500);
    });
}

function pollBulkStatus() {
    $.get('../api/checkupdate.php?id=1&type=bulkstatus', function (data) {
        var active = parseInt(data, 10) === 1;
        if (active !== bulkIsActive) {
            bulkIsActive = active;
            if (active) {
                $('#blk-start').hide();
                $('#blk-stop').show();
                $('#bulk-active').text(lang_active);
                $('#recall-all').removeClass('hidden');
            } else {
                $('#blk-start').show();
                $('#blk-stop').hide();
                $('#bulk-active').text(lang_inactive);
                $('#recall-all').addClass('hidden');
            }
        }
    });
}

$(document).ready(function () {
    pollBulkStatus();
    setInterval(pollBulkStatus, 2000);
});
