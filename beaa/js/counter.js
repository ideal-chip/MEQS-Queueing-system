$(document).ready(function () {

    $('[data-toggle="tooltip"]').tooltip();

    getPendingList();
    refreshData();
    // setup transfer dialog
    switchOption('counters');
    document.getElementById('toCounter').checked = 'checked';

    $(".cat-radio").click(function () {
        updateStatus(this.id);
    });
});
//==============================================================  | vars

var call_timer = document.getElementById('call-timer');
var recall_timer = document.getElementById('recall-timer');
var latest_size = document.getElementById("latest-size");
var eventNo_box = document.getElementById('eventno');
var eventDate_box = document.getElementById('eventdate');

// --------------------------------|| colors
var activeColor = 'white';
var inactiveColor = 'red';
var transparentColor = 'transparent';
var textColor = activeColor;

// --------------------------------|| calls vars
var recallCount = recall_times_def;
var lockCounterCall = 0;
var lockCounterRecall = 0;
var callType = 0;
var eventClicked = 0

// --------------------------------|| other vars

var counterState = 1;
var lastID = 0;
var lastCalledCategory = 0;
var lastCalledNo = '';
var lastCalledDate = '';

var lastCount = 0;
var flashTimes = 0;

//==============================================================  | Intervals

setInterval(function () {
    refreshData();
}, 1500);
setInterval(function () {
    flashing();
}, 500);
setInterval(function () {
    if (counterState) {
        refreshLastCalled();
    }
}, 1500);

setInterval(function () {
    setCounterActive();
}, 1500);

setInterval(function () {
    if (counterState) {
        if (lockCounterCall > 0) {
            lockCounterCall--;
            call_timer.innerHTML = lockCounterCall;
        } else {
            unlockBtn('call');
        }
        if (lockCounterRecall > 0) {
            lockCounterRecall--;

        } else if (recallCount > 0) {
            unlockBtn('recall');
        }
        recall_timer.innerHTML = recallCount;
    }

}, 1000);


//==============================================================  || refresh last called tickets


function refreshLastCalled() {

    var calledList = document.getElementById("called-list");
    var item = "<li class='dropdown-menu-item' >" +
            "<span class='pad-h-10'>{{ticket}}</span>" +
            "<span class='pad-h-10'><button class='btn btn-info btn-xs btn-event' onclick='callByEvent({{id}})'>" + lang_recall + "</button></span></li>";
    var empty = "<li class='empty'>" +
            "<span class='pad-5 text-danger'>" + lang_empty + "</span></li>";

    $.ajax({
        type: 'get',
        dataType: 'json',
        cache: false,
        url: '../api/counter/',
        data: {op: 16, counter: counterID},
        success: function (response, textStatus, jqXHR) {
            var content = '';
            calledList.innerHTML = '';
            if (response.length) {
                for (var i = 0; i < response.length; i++) {
                    var d = response[i];
                    var t = item;
                    t = t.replace("{{ticket}}", d.Ticket);
                    t = t.replace("{{id}}", d.ID);
                    content += t;
                }
                latest_size.innerHTML = response.length;
                calledList.innerHTML = content;
            } else {
                calledList.innerHTML = empty;
                latest_size.innerHTML = 0;
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
        }
    });
}

//==============================================================  | pending

function addPending() {
    if (lastID > 0) {
        $.ajax({
            type: 'get',
            dataType: 'json',
            cache: false,
            url: '../api/counter/',
            data: {op: 8, counter: counterID, clerk: clerkID, event: lastID},
            success: function (response, textStatus, jqXHR) {
//                console.log(response);
//                console.log("lastID: " + lastID);
                if (response) {

                    eventID = parseInt(response);
                    if (eventID > 0) {
                        flashTimes = 1;
                        resetCounter();
                        getPendingList();
                        UpdateLastCalledStatus(2);
                    }
                } else {
                    //alert("error adding to pending list!");
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
            }
        });
    } else {
        alert(alert_pleaseCall);
    }
}

function getPendingList() {
    $.ajax({
        type: 'get',
        dataType: 'json',
        cache: false,
        url: '../api/counter/',
        data: {op: 9, counter: counterID},
        success: function (response, textStatus, jqXHR) {
            var pending_list_con = document.getElementById('pending-list');
            if (response && response.length > 0) {
//                console.log(response);

                var content = "";
                for (var i = response.length - 1; i >= 0; i--) {
                    var pend_li = document.getElementById('pend-element-hid').innerHTML;
                    pend_li = pend_li.replace('--ticket--', response[i].ticket);
                    pend_li = pend_li.replace("'--event--'", response[i].eventID);
                    content += pend_li;
                }
                document.getElementById('pend-count').innerHTML = response.length;
                pending_list_con.innerHTML = content;

            } else {
                pending_list_con.innerHTML = lang_empty;
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
        }
    });
}


function removePending(eventID) {
    $.ajax({
        type: 'get',
        dataType: 'json',
        cache: false,
        url: '../api/counter/',
        data: {op: 10, event: eventID, counter: counterID},
        success: function (response, textStatus, jqXHR) {
//            console.log(response);
            if (response) {

                if (response === "NO") {
                    alert(alert_noClients);
                    resetCounter();
                } else if (response === "OLD") {
                    getPendingList();
                } else {

                    lastID = response.eventID;
                    lastCalledCategory = response.eventCategory;
                    lastCalledNo = response.Ticket;
                    lastCalledDate = lang_enterTime + response.eventTime;
                    //                            lastCalled.value = lastID;
                    eventNo_box.innerHTML = lastCalledNo;
                    eventDate_box.innerText = lastCalledDate;
                    getPendingList();

                    resetRecallCount();
                    UpdateLastCalledStatus(0);
                    if (document.getElementById('autocall').checked) {
                        recall(1);
                    }
                }

            } else {
                alert(alert_errorInOperation);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
        }
    });
}

//==============================================================  | flashing

function flashing() {
    var changeColor = '';
    if (flashTimes)
    {
        if (flashTimes-- & 1)
        {
            changeColor = textColor;

        } else
        {
            changeColor = transparentColor;
        }
        eventNo_box.style.color = changeColor;
    } else {
        eventNo_box.style.color = textColor;
    }
}

//==============================================================  | Audio notification
function notification() {
    var audio = new Audio(path_audio_notification);
    audio.play();
}

//==============================================================  | change Language
function changeLang() {
    var x = document.getElementById("lang").value;
    location.replace(langPathReplace + x);
}

//==============================================================  | category enable/ disable

function updateStatus(id) {

    var enabled = 0;
    if (!$("#" + id).hasClass('pressed')) {
        enabled = 1;
    }

    id = id.replace("cc", '');

    $.ajax({
        type: 'get',
        dataType: 'json',
        cache: false,
        url: '../api/update.php',
        data: {type: 'catstatus', id: id, enabled: enabled},
        success: function (response, textStatus, jqXHR) {
            if (response) {

                $("#cc" + response).toggleClass('pressed');
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
        }
    });
}

//==============================================================  | Last Called Status

//0:call, 1: recall, 2: pending, 3:transfer
function UpdateLastCalledStatus(index)
{

    var badges = document.getElementsByClassName('badged');
    for (var i = 0; i < badges.length; i++)
    {
        if (i === index) {
            $(badges[i]).toggleClass('badged-active', true);
        } else {
            $(badges[i]).toggleClass('badged-active', false);
        }
    }

}

//==============================================================  | transfer

function transfer(cat) {
    if (lastID)
    {
        var toCounter = 0;
        var toCategory = cat;
        if (document.getElementById('toCategory').checked) {
            toCategory = document.getElementById('categories').value;
        }
        if (document.getElementById('toCounter').checked)
        {
            var toCounter = document.getElementById('counters').value;
        }

        $.ajax({
            type: 'get',
            dataType: 'json',
            cache: false,
            url: '../api/counter/',
            data: {op: 3, counter: counterID, clerk: clerkID, event: lastID, tocounter: toCounter, tocategory: toCategory},
            success: function (response, textStatus, jqXHR) {
                if (response) {

                    flashTimes = 1;
                    recallCount = 0;
                    resetCounter();

                    hideTransferDialog();
                    UpdateLastCalledStatus(3);
                } else {
                    alert(alert_errorTransfer);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
            }
        });

    } else
    {
        alert(alert_pleaseCall);
    }

}

function centerDialog(id) {
    dialog = document.getElementById(id);
    d_width = dialog.clientWidth;
    w_width = document.body.clientWidth;
    d_height = dialog.clientHeight;
    w_height = document.body.clientHeight;
    //alert(d_width + " " + w_width);

    dialog.style.top = (w_height - d_height - 100) / 2 + 'px';
    dialog.style.left = (w_width - d_width) / 2 + 'px';
    //dialog.style.

}

function switchOption(id) {
    $('.transfer-option').hide();
    $('#' + id).show();
}

function showTransferDialog() {
    if (lastID)
    {
        document.getElementById('ticketNo').innerHTML = lastCalledNo;
        //alert(lastCalledNo);

        document.getElementById('fullScreen').style.display = 'block';
        document.getElementById('transferDialog').style.display = 'block';
        //alert(lastID + " " + lastCalledCategory);
        document.getElementById('categories').value = lastCalledCategory;
        centerDialog('transferDialog');
    } else
    {
        alert(alert_pleaseCall);
    }
}

function hideTransferDialog() {
    document.getElementById('transferDialog').style.display = 'none';
    document.getElementById('fullScreen').style.display = 'none';
}


//==============================================================  | call / recall


// --------------------------------|| funcs - call
function call() {

    lastOperation = 1;

    $.ajax({
        type: 'get',
        dataType: 'json',
        cache: false,
        url: '../api/counter/',
        data: {op: 1, counter: counterID, clerk: clerkID},
        success: function (response, textStatus, jqXHR) {

            if (response) {

                var result = response;



                lastID = result.eventID;
                lastCalledCategory = result.eventCategory;
                lastCalledNo = result.eventChar + result.eventNo;
                lastCalledDate = lang_enterTime + result.eventTime;
                //                            lastCalled.value = lastID;
                eventNo_box.innerHTML = lastCalledNo;
                eventDate_box.innerText = lastCalledDate;

                // update last called box
                lockBtn('call');
                document.getElementById('last-called').innerHTML = lastCalledNo;
                UpdateLastCalledStatus(0);
                resetRecallCount();

                if (document.getElementById('autocall').checked) {
                    recall(1);
                }

            } else {
                alert(alert_noClients);
                resetCounter();
            }
//            console.log(response);
        },
        error: function (jqXHR, textStatus, errorThrown) {
        }
    });
}

// --------------------------------|| funcs - call by event

function callByEvent(eventID) {
    if (eventClicked !== eventID) {
        eventClicked = eventID;

        $.ajax({
            type: 'get',
            dataType: 'json',
            cache: false,
            url: '../api/counter/',
            data: {op: 15, counter: counterID, event: eventID},
            success: function (response, textStatus, jqXHR) {

                if (response) {

                    if (response === "NO") {

                        alert(alert_errorInOperation);
                        resetCounter();
                        eventClicked = 0;

                    } else {

                        var result = response;

                        lastID = result.eventID;
                        lastCalledCategory = result.eventCategory;
                        lastCalledNo = result.eventChar + result.eventNo;
                        lastCalledDate = lang_enterTime + result.eventTime;
                        // lastCalled.value = lastID;
                        eventNo_box.innerHTML = lastCalledNo;
                        eventDate_box.innerText = lastCalledDate;
                        // update last called box
                        document.getElementById('last-called').innerHTML = lastCalledNo;
                        UpdateLastCalledStatus(0);
                        resetRecallCount();

                        if (document.getElementById('autocall').checked) {
                            recall(1);
                        }

                    }
                } else {
                    alert(alert_errorInOperation);
                    eventClicked = 0;
                }
//            console.log(response);
            },
            error: function (jqXHR, textStatus, errorThrown) {
            }
        });
    }
}

// --------------------------------|| funcs - recall

// type: 0: recall only[defualt], 1: call then recall

function recall(type) {
    if (!type) {
        type = 0;
    }
    if (recallCount > 0) {
        sendRecall(type);
    } else {
        lockBtn('recall');
    }
}

function sendRecall(type) {
    lastOperation = 2;
    if (lastID) {
        callType = type;

        $.ajax({
            type: 'get',
            dataType: 'json',
            cache: false,
            url: '../api/counter/',
            data: {op: 2, counter: counterID, clerk: clerkID, event: lastID, type: type},
            success: function (response, textStatus, jqXHR) {

                if (response) {
                    flashTimes = 10;
                    if (callType == 0) {
                        recallCount--;
                        UpdateLastCalledStatus(1);
                    }
                    lockBtn('recall');
                    lockBtn('call');
                } else {
                    alert(alert_errorInOperation);
                }
//            console.log(response);
            },
            error: function (jqXHR, textStatus, errorThrown) {
            }
        });

    } else {
        alert(alert_pleaseCall);
        resetCounter();
    }
}

function resetRecallCount() {
    recallCount = recall_times_def;
}
function unlockBtn(id) {
    var btn = document.getElementById(id);
    btn.disabled = false;
//                            $("#" + id).toggleClass("locked", false);
}

function lockBtn(id) {
    var btn = document.getElementById(id);
    btn.disabled = true;
//                            $("#" + id).toggleClass("locked", true);
    if (id = 'call') {
        lockCounterCall = call_delay_def;
    }
    if (id = 'recall') {
        lockCounterRecall = call_delay_def;
    }

}

//==============================================================  | refresh Data
function showPriorityReversed(num, size) {
    if (num == 0) {
        return '-';
    } else {
        return size - num + 1;
    }
}

function refreshData() {
    if (counterState)
    {
        $.ajax({
            type: 'get',
            dataType: 'json',
            cache: false,
            url: '../api/counter/',
            data: {op: 4, counter: counterID, clerk: clerkID},
            success: function (response, textStatus, jqXHR) {
                if (response) {

                    var events = response;
                    var eventsTable = document.getElementById('eventItems');

                    var header = "<tr class='small text-center-th'>" +
                            "<th class='small'>" + lang_eventPriority + "</th>" +
                            "<th class='small'>" + lang_eventNo + "</th>" +
                            "<th class='small'>" + lang_enterTime + "</th>" +
                            "<th></th>" +
                            "</tr>";

                    var maxCount = val_maxCount;
                    var eventsRows = "";
                    eventsRows += header;

                    if (events)
                    {
                        var lastCount = events.length;
                        var size = (lastCount <= maxCount ? lastCount - 1 : maxCount);

                        UpdateCounterData(events[size].eventQty, events[size].lastCalled, events[size].counterload);

                        for (var curCount = 0; curCount < size; curCount++)
                        {
                            var pickBtn = (isPicker === 0 ? "" : "<a class='badge-red-sm hover-white' href='javascript:void(0);' onclick='callByEvent(--eventID--);'><span class='glyphicon glyphicon-arrow-up'></span></a>");
                            var tableRow = "<tr class='event-row'>" +
                                    "<td>--priority--</td>" +
                                    //"<td>--ticket--" + (events[curCount].eventTransferred == "1" ? "<img src='" + path_img_transferred + "' style='vertical-align:middle;'>" : "") + "</td>" +
                                    "<td>" + (events[curCount].eventTransferred == "1" ? " <i class='fa fa-arrow-circle-left'></i>" : "") + " --ticket--</td>" +
                                    "<td>--eventTime--</td>" +
                                    "<td class='relative'>" + pickBtn + "</td>" +
                                    "</tr>";
                            tableRow = tableRow.replace('--priority--', showPriorityReversed(events[curCount].eventPriority, 10));
                            tableRow = tableRow.replace('--ticket--', events[curCount].eventChar + events[curCount].eventNo);
                            tableRow = tableRow.replace('--eventTime--', events[curCount].eventTime);
                            tableRow = tableRow.replace("--eventID--", events[curCount].eventID);

                            eventsRows += tableRow;
                        }
                        eventsTable.innerHTML = eventsRows;

                    } else
                    {
                        eventsRows += "<tr class='event-row'>" +
                                "<td colspan='4'>" + lang_empty + "</td>" +
                                "</tr>";
                    }
                    eventsTable.innerHTML = eventsRows;
                }
//            console.log(response);
            },
            error: function (jqXHR, textStatus, errorThrown) {
            }
        });
    }
}

function UpdateCounterData(totalWaiting, lastCalled, counterLoad) {
    lastCalledNo = lastCalled;

    document.getElementById('waiting').innerHTML = totalWaiting;
    document.getElementById('last-called').innerHTML = lastCalled;
    document.getElementById('c-load').innerHTML = counterLoad;
}

//==============================================================  | open / close counter

function closeCounter() {
    $.ajax({
        type: 'get',
        dataType: 'json',
        cache: false,
        url: '../api/counter/',
        data: {op: 6, counter: counterID, clerk: clerkID},
        success: function (response, textStatus, jqXHR) {

            if (response)
            {
                flashTimes = 1;
                counterState = 0;
                textColor = inactiveColor;

                eventNo_box.innerHTML = lang_closed;
                eventNo_box.style.color = textColor;
                eventDate_box.innerHTML = "&nbsp;";
                document.getElementById('open').disabled = false;
                document.getElementById('close').disabled = true;
                document.getElementById('call').disabled = true;
                document.getElementById('recall').disabled = true;
                document.getElementById('transfer').disabled = true;
                document.getElementById('autocall').disabled = true;
                document.getElementById('pending').disabled = true;
//                document.getElementById('waitEvents').innerHTML = "";
                document.getElementById('pending-list').innerHTML = "<span class='red-text'>" + lang_closed + "</span>";
                $(".shader").show();
                $(".btn-event").addClass("disabled");

                var directBtn = document.getElementById('direct-transfer');
                if (directBtn) {
                    directBtn.disabled = true;
                }

            } else
            {
                alert(alert_errorClose);
            }
//            console.log(response);
        },
        error: function (jqXHR, textStatus, errorThrown) {
        }
    });
}

function openCounter() {

    counterState = 1;
    textColor = activeColor;

    eventNo_box.style.color = textColor;
    document.getElementById('open').disabled = true;
    document.getElementById('close').disabled = false;
//    document.getElementById('call').disabled = false;
//    document.getElementById('recall').disabled = false;
    document.getElementById('transfer').disabled = false;
    document.getElementById('autocall').disabled = true;
    document.getElementById('pending').disabled = false;
//    document.getElementById('waitEvents').innerHTML = "<table border='0' id='eventItems'></table>";
    $(".btn-event").removeClass("disabled");
    $(".shader").hide();

    if (lastCalledNo && lastID) {
        eventNo_box.innerHTML = lastCalledNo;
        eventDate_box.innerHTML = lastCalledDate;
    } else {
        eventNo_box.innerHTML = lang_opened;
        eventDate_box.innerHTML = "&nbsp;";
    }

    var directBtn = document.getElementById('direct-transfer');
//                        alert(directBtn);
    if (directBtn) {
        document.getElementById('direct-transfer').disabled = false;
    }
    getPendingList();

//    document.getElementById('fullScreen').style.display = 'none';
}

function resetCounter() {

    eventNo_box.innerHTML = lang_opened;
    eventDate_box.innerHTML = '';
    lastID = 0;
    lastCalledNo = '';
}

//==============================================================  | set counter active

function setCounterActive() {
    if (counterState) {
        $.ajax({
            type: 'get',
            dataType: 'json',
            cache: false,
            url: '../api/update.php',
            data: {type: 'counter', id: counterID, clerkid: clerkID},
            success: function (response, textStatus, jqXHR) {
                if (response) {

                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
            }
        });
    }
}
//==============================================================  | login / logout

function logout() {
    if (window.confirm(msg_logoutMessage)) {
        $.ajax({
            type: 'get',
            dataType: 'json',
            cache: false,
            url: '../api/counter/',
            data: {op: 12},
            success: function (response, textStatus, jqXHR) {
                console.log(response);
                if (response) {

                    location.reload();
                } else {
                    alert(alert_errorLogout);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
            }
        });
    }
}



