// iDEAL-Q — clerk counter workstation
// Wires the counter UI (call / recall / pending / transfer / close / logout)
// to the existing ../api/counter/?op=N endpoints. All the vars used below
// (counterID, clerkID, val_maxCount, call_delay_def, recall_times_def,
// alert_*, lang_*, path_audio_notification, ...) are declared inline in
// main.php just before this file is loaded.

var currentEvent = null;
var callTimer = null, recallTimer = null;
var pollTimer = null;

var API = '../api/counter/?op=';

function apiGet(op, params, cb) {
    var qs = 'op=' + op;
    for (var k in params) {
        if (params.hasOwnProperty(k)) {
            qs += '&' + k + '=' + encodeURIComponent(params[k]);
        }
    }
    $.get('../api/counter/?' + qs, function (data) {
        cb(data);
    }).fail(function () {
        cb(0);
    });
}

function playNotification() {
    try {
        var a = new Audio(path_audio_notification);
        a.play().catch(function () {});
    } catch (e) {}
}

function startButtonTimer($btn, $badge, seconds, onDone) {
    var remaining = seconds;
    $badge.text(remaining);
    $btn.prop('disabled', true);
    var timer = setInterval(function () {
        remaining--;
        $badge.text(remaining > 0 ? remaining : 0);
        if (remaining <= 0) {
            clearInterval(timer);
            $btn.prop('disabled', false);
            if (onDone) onDone();
        }
    }, 1000);
    return timer;
}

//====================================================================  | Call / Recall

function setCurrentEvent(eventObj) {
    currentEvent = eventObj;
    if (eventObj) {
        var ticket = (eventObj.eventChar || '') + (eventObj.eventNo || '');
        $('#eventno').text(ticket);
        $('#eventdate').text(eventObj.eventTime || '');
        $('#last-called').text(ticket);
    } else {
        $('#eventno').text(lang_opened);
        $('#eventdate').text('');
    }
}

function call() {
    if (callTimer) return;
    apiGet(1, {counter: counterID}, function (data) {
        if (data && data !== 0 && data !== '0') {
            var eventObj = (typeof data === 'string') ? JSON.parse(data) : data;
            setCurrentEvent(eventObj);
            apiGet(2, {counter: counterID, clerk: clerkID, event: eventObj.eventID, type: 1}, function () {
                playNotification();
                refreshData();
            });
        } else {
            alert(alert_noClients);
        }
        callTimer = startButtonTimer($('#call'), $('#call-timer'), call_delay_def, function () {
            callTimer = null;
        });
    });
}

function recall() {
    if (!currentEvent) {
        alert(alert_pleaseCall);
        return;
    }
    if (recallTimer) return;
    apiGet(2, {counter: counterID, clerk: clerkID, event: currentEvent.eventID, type: 2}, function (data) {
        if (data == 1) {
            playNotification();
        } else {
            alert(alert_errorInOperation);
        }
    });
    recallTimer = startButtonTimer($('#recall'), $('#recall-timer'), recall_times_def, function () {
        recallTimer = null;
    });
}

// Pick a specific waiting ticket out of order (only when isPicker == 1).
function callByEvent(eventID) {
    apiGet(15, {event: eventID, counter: counterID}, function (data) {
        if (data && data !== 0 && data !== '0' && data !== 'NO') {
            var eventObj = (typeof data === 'string') ? JSON.parse(data) : data;
            setCurrentEvent(eventObj);
            apiGet(2, {counter: counterID, clerk: clerkID, event: eventObj.eventID, type: 1}, function () {
                playNotification();
                refreshData();
            });
        } else {
            alert(alert_errorInOperation);
        }
    });
}

//====================================================================  | Pending list

function addPending() {
    if (!currentEvent) {
        alert(alert_pleaseCall);
        return;
    }
    apiGet(8, {counter: counterID, event: currentEvent.eventID, clerk: clerkID}, function (data) {
        if (data && data !== 0 && data !== '0') {
            setCurrentEvent(null);
            loadPendingList();
            refreshData();
        } else {
            alert(alert_errorInOperation);
        }
    });
}

function removePending(eventID) {
    apiGet(10, {event: eventID, counter: counterID}, function (data) {
        if (data && data !== 0 && data !== '0' && data !== 'OLD' && data !== '"NO"') {
            var eventObj = (typeof data === 'string') ? JSON.parse(data) : data;
            if (eventObj && eventObj !== 'NO') {
                setCurrentEvent(eventObj);
            }
        }
        loadPendingList();
        refreshData();
    });
}

function loadPendingList() {
    apiGet(9, {counter: counterID}, function (data) {
        var $list = $('#pending-list');
        $list.empty();
        var items = (data && data !== 0 && data !== '0') ? (typeof data === 'string' ? JSON.parse(data) : data) : [];
        $('#pend-count').text(items.length);
        items.forEach(function (item) {
            var $row = $('#pend-element-hid li').clone();
            $row.find('.p-remove').attr('onclick', "removePending(" + item.eventID + ");");
            $row.find('.p-tick').text(item.ticket);
            $list.append($row);
        });
    });
}

//====================================================================  | Transfer

function showTransferDialog() {
    if (!currentEvent) {
        alert(alert_pleaseCall);
        return;
    }
    var ticket = (currentEvent.eventChar || '') + (currentEvent.eventNo || '');
    $('#ticketNo').text(ticket);
    $('#fullScreen').show();
    $('#transferDialog').show();
}

function hideTransferDialog() {
    $('#fullScreen').hide();
    $('#transferDialog').hide();
}

function switchOption(which) {
    if (which === 'counters') {
        $('#counters').show();
        $('#categories').hide();
    } else {
        $('#counters').hide();
        $('#categories').show();
    }
}

function transfer(directCategoryId) {
    if (!currentEvent) {
        alert(alert_pleaseCall);
        return;
    }
    var params = {counter: counterID, clerk: clerkID, event: currentEvent.eventID};
    if (typeof directCategoryId !== 'undefined') {
        params.tocategory = directCategoryId;
    } else if ($('#toCounter').is(':checked')) {
        params.tocounter = $('#counters').val();
    } else {
        params.tocategory = $('#categories').val();
    }
    apiGet(3, params, function (data) {
        if (data == 1) {
            setCurrentEvent(null);
            hideTransferDialog();
            refreshData();
        } else {
            alert(alert_errorTransfer);
        }
    });
}

//====================================================================  | Category toggle

$(document).on('click', '.cat-radio', function () {
    var $btn = $(this);
    var id = $btn.attr('id');
    if (!id) return;
    var ccID = id.replace('cc', '');
    var enabled = $btn.hasClass('pressed') ? 0 : 1;
    $.get('../api/update.php?type=catstatus&id=' + ccID + '&enabled=' + enabled, function (data) {
        if (data == ccID) {
            $btn.toggleClass('pressed');
        }
    });
});

//====================================================================  | Open / close / logout

function openCounter() {
    apiGet(5, {counter: counterID}, function () {
        $('#open').prop('disabled', true);
        $('#close, #call, #recall, #pending, #transfer').prop('disabled', false);
    });
}

function closeCounter() {
    apiGet(6, {counter: counterID}, function (data) {
        if (data == 1) {
            $('#open').prop('disabled', false);
            $('#close, #call, #recall, #pending, #transfer').prop('disabled', true);
            setCurrentEvent(null);
        } else {
            alert(alert_errorClose);
        }
    });
}

function logout() {
    apiGet(12, {}, function (data) {
        if (data == 1) {
            window.location.href = './';
        } else {
            alert(alert_errorLogout);
        }
    });
}

function changeLang() {
    var lang = $('#lang').val();
    window.location.href = langPathReplace + lang;
}

//====================================================================  | Refresh loop (queue preview, counters, latest calls)

function renderEventItems(events) {
    var $tbl = $('#eventItems');
    $tbl.empty();
    events.forEach(function (ev) {
        var ticket = (ev.eventChar || '') + (ev.eventNo || '');
        var $tr = $('<tr>').css('cursor', isPicker ? 'pointer' : 'default');
        $tr.append($('<td>').text(ticket));
        $tr.append($('<td>').text(ev.eventTime || ''));
        if (ev.eventTransferred == 1) {
            $tr.append($('<td>').text('T'));
        }
        if (isPicker) {
            $tr.on('click', function () {
                callByEvent(ev.eventID);
            });
        }
        $tbl.append($tr);
    });
}

function refreshData() {
    apiGet(4, {counter: counterID, clerk: clerkID}, function (data) {
        if (!data) return;
        var arr = (typeof data === 'string') ? JSON.parse(data) : data;
        if (!arr || !arr.length) return;
        var summary = arr[arr.length - 1];
        var events = arr.slice(0, arr.length - 1);
        renderEventItems(events.slice(0, val_maxCount));
        $('#waiting').text(summary.eventQty);
        $('#c-load').text(summary.counterload);
        if (summary.lastCalled && summary.lastCalled !== '-') {
            $('#last-called').text(summary.lastCalled);
        }
    });

    apiGet(16, {counter: counterID}, function (data) {
        var $list = $('#called-list');
        var items = (data && data !== 0 && data !== '0') ? (typeof data === 'string' ? JSON.parse(data) : data) : [];
        $('#latest-size').text(items.length);
        if (!items.length) {
            $list.html("<li class='empty'><span class='pad-5 text-danger'>" + lang_empty + "</span></li>");
            return;
        }
        $list.empty();
        items.forEach(function (item) {
            $list.append($('<li>').append($('<span>').addClass('pad-5').text(item.Ticket)));
        });
    });
}

$(document).ready(function () {
    loadPendingList();
    refreshData();
    pollTimer = setInterval(refreshData, 5000);
});
