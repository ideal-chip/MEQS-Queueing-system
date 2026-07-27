

setInterval(function () {
    refreshData();
}, 1500);
$(document).ready(function () {

    $('[data-toggle="tooltip"]').tooltip();

    getPendingList();
    // setup transfer dialog
    switchOption('counters');
    document.getElementById('toCounter').checked = 'checked';

    $(".cat-radio").click(function () {
        updateStatus(this.id);
    });
});

//==============================================================  | other vars

var logoutXML = new XMLHttpRequest();
var callXML = new XMLHttpRequest();
var recallXML = new XMLHttpRequest();
var transferXML = new XMLHttpRequest();
var refreshXML = new XMLHttpRequest();
var closeCounterXML = new XMLHttpRequest();
var openCounterXML = new XMLHttpRequest();
var transCounters = new XMLHttpRequest();
var transCats = new XMLHttpRequest();
var calledCategory = new XMLHttpRequest();
var counterState = 1;
var lastID = 0;
var lastCalledCategory = 0;
var lastCalledNo = '';
var lastCalledDate = '';
// wether a ticket has been called or not[0,1]<bool>
var isCalled = 0;
var lastCount = 0;
var flashTimes = 0;
var flashNo;
var activeColor = 'white';
var inactiveColor = 'red';
var transparentColor = 'transparent';
var textColor = activeColor;
//var lastCalledInput = document.getElementById('lastCalled');
////==============================================================  || showBooking
//
//function showBooking() {
//    $("#booking-modal").modal('show');
//}

//==============================================================  || refresh last called tickets
var latest_size = document.getElementById("latest-size");
setInterval(function () {
    if (counterState) {
        refreshLastCalled();
    }
}, 1500);
function refreshLastCalled() {
    
    var calledList = document.getElementById("called-list");
    var item = "<li class='dropdown-menu-item' >" +
            "<span class='pad-h-10'>{{ticket}}</span>" +
            "<span class='pad-h-10'><button class='btn btn-info btn-xs' onclick='callByEvent({{id}})'>" + lang_recall + "</button></span></li>";
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
//                alert("someting")
                for (var i = 0; i < response.length; i++) {
                    var d = response[i];
                    var t = item;
                    t = t.replace("{{ticket}}", d.Ticket);
//                    t = t.replace("{{status}}", d.status);
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
                if (response) {
                    eventID = parseInt(response);
                    if (eventID > 0) {
                        flashTimes = 1;
                        resetCounter();
                        getPendingList();
                        UpdateLastCalledStatus(2);
                    }
                } else {
                    alert("error adding to pending list!");
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
            }
        });
    } else {
        alert(alert_pleaseCall);
    }
}
//var pendingXML = new XMLHttpRequest();
//pendingXML.onreadystatechange = function () {
//    pendingXML.onreadystatechange = function () {
//        if (pendingXML.readyState == 4 && pendingXML.status == 200) {
//            eventID = parseInt(pendingXML.responseText);
//            if (eventID > 0) {
//                //alert("event: " + eventID);
//                flashTimes = 1;
//                resetCounter();
//                getPendingList();
//                UpdateLastCalledStatus(2);
//            } else {
//                alert("error adding to pending list!");
//            }
//
//        }
//    };
//};
//function addPending() {
//    //alert(lastID);
//    if (lastID > 0) {
//        pendingXML.open("GET", "../api/counter/?op=8&counter=" + counterID + "&clerk=" + clerkID + "&event=" + lastID, true);
//        pendingXML.send();
//    } else {
//        alert(alert_pleaseCall);
//    }
//
//
//}

var pendingListXML = new XMLHttpRequest();
pendingListXML.onreadystatechange = function () {
    pendingListXML.onreadystatechange = function () {
        if (pendingListXML.readyState == 4 && pendingListXML.status == 200) {
            pendingList = JSON.parse(pendingListXML.responseText);
            pending_list_con = document.getElementById('pending-list');
            pend_qty = (pendingList.length > 0) ? pendingList.length : 0;
            document.getElementById('pend-count').innerHTML = pend_qty;
//                                    alert(pendingList.length);
            if (pend_qty > 0) {
//                                        alert("events: " + pendingList);


                p_list = "";
                for (var i = pendingList.length - 1; i >= 0; i--) {
                    pend_li = document.getElementById('pend-element-hid').innerHTML;
                    pend_li = pend_li.replace('--ticket--', pendingList[i].ticket);
                    pend_li = pend_li.replace("'--event--'", pendingList[i].eventID);
                    p_list += pend_li;
                    //p_list += "<li>" + pendingList[i].ticket +" "+pendingList[i].eventID+ "</li>";
                }
                pending_list_con.innerHTML = p_list;
            } else {
                pending_list_con.innerHTML = lang_empty;
                //alert('empty');
            }


        }

    };
};
function getPendingList() {
    pendingListXML.open("GET", "../api/counter/?op=9&counter=" + counterID, true);
    pendingListXML.send();
}

var removePendingXML = new XMLHttpRequest();
removePendingXML.onreadystatechange = function () {
    if (removePendingXML.readyState == 4 && removePendingXML.status == 200) {
        if (removePendingXML.responseText != 0)
        {
            var result = removePendingXML.responseText.trim();
            if (result !== "NO" && result !== "OLD")
            {
                var result = JSON.parse(removePendingXML.responseText);
                var eventNo = document.getElementById('eventno');
                var eventDate = document.getElementById('eventdate');
                lastID = result.eventID;
                lastCalledCategory = result.eventCategory;
                lastCalledNo = result.eventChar + result.eventNo;
                lastCalledDate = lang_enterTime + result.eventTime;
                //                            lastCalled.value = lastID;
                eventNo.innerHTML = lastCalledNo;
                eventDate.innerText = lastCalledDate;
                getPendingList();

                resetRecallCount();
                UpdateLastCalledStatus(0);
                if (document.getElementById('autocall').checked)
                    recall(1);

            } else if (result === "OLD") {
                getPendingList();
            } else {
                alert(alert_noClients);
                resetCounter();
            }
        } else
        {
            alert(alert_errorInOperation);
        }
//                            if (parseInt(removePendingXML.responseText) > 0) {
//                                getPendingList();
//                                call();
//                            }
    }

};
function removePending(eventID) {
    removePendingXML.open("GET", "../api/counter/?op=10&event=" + eventID + "&counter=" + counterID, true);
    removePendingXML.send();
}



setInterval(function () {
    flashing();
}, 500);
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
        document.getElementById('eventno').style.color = changeColor;
    } else {
        document.getElementById('eventno').style.color = textColor;
    }
}

function notification() {
    var audio = new Audio(path_audio_notification);
    audio.play();
}

function changeLang() {
    var x = document.getElementById("lang").value;
    //document.getElementById("demo").innerHTML = "You selected: " + x;
    //alert("x");
    location.replace(langPathReplace + x);
    //                            location.reload();
}

//==============================================================  | category enable/ disable

function updateStatus(el)
{
    if ($("#" + el).hasClass('pressed')) {
        setCatStatus(el, 0);
    } else {
        setCatStatus(el, 1);
    }

}

var catStatusXML = new XMLHttpRequest();

catStatusXML.onreadystatechange = function () {
    if (catStatusXML.readyState == 4 && catStatusXML.status == 200) {
        cc_id = parseInt(catStatusXML.responseText);
        if (cc_id > 0) {
            $("#cc" + cc_id).toggleClass('pressed');
        }
    }

};

function setCatStatus(id, enabled) {
    //alert(id);
    id = id.replace("cc", '');
    //alert(id);
    catStatusXML.open("GET", "../api/update.php?type=catstatus&id=" + id + "&enabled=" + enabled);
    catStatusXML.send();
}
//==============================================================  | Category Checkbox


function enableCategory(id) {
    var c = document.getElementById(id).checked;
    alert(id + "status: " + c);
}


//0:call, 1: recall, 2: pending, 3:transfer
function UpdateLastCalledStatus(index = 0)
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

transferXML.onreadystatechange = function () {
    if (transferXML.status == 200 && transferXML.readyState == 4)
    {
        if (parseInt(transferXML.responseText))
        {
            flashTimes = 1;
            recallCount = 0;
            hideTransferDialog();
            UpdateLastCalledStatus(3);
        } else
        {
            alert(alert_errorTransfer);
        }
    }
};

function transfer(cat) {
    if (lastID)
    {
        if (cat > 0) {
            //alert(cat);
            var toCategory = cat;
            transferXML.open("GET", "../api/counter/?op=3&counter=" + counterID + "&clerk=" + clerkID + "&event=" + lastID + "&tocategory=" + toCategory, true);
            transferXML.send();
            resetCounter();
            return;
        }
    } else
    {
        alert(alert_pleaseCall);
        return;
    }

    if (document.getElementById('toCounter').checked)
    {
        var toCounter = document.getElementById('counters').value;
//                            var toCategory = document.getElementById('categories').value;
        transferXML.open("GET", "../api/counter/?op=3&counter=" + counterID + "&clerk=" + clerkID + "&event=" + lastID + "&tocounter=" + toCounter, true);
        transferXML.send();
        resetCounter();
        return;
    }
    if (document.getElementById('toCategory').checked)
    {
        var toCategory = document.getElementById('categories').value;
        transferXML.open("GET", "../api/counter/?op=3&counter=" + counterID + "&clerk=" + clerkID + "&event=" + lastID + "&tocategory=" + toCategory, true);
        transferXML.send();
        resetCounter();
        return;
    }
    alert('Select One Please');
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
// --------------------------------|| vars
var recallCount = recall_times_def;
var lockCounterCall = 0;
var lockCounterRecall = 0;
var callType = 0;
var call_timer = document.getElementById('call-timer');
var recall_timer = document.getElementById('recall-timer');
var eventClicked = 0

var callByEventXML = new XMLHttpRequest();

// --------------------------------|| funcs - call
function call() {
    lastOperation = 1;
    callXML.open("GET", "../api/counter/?op=1&counter=" + counterID + "&clerk=" + clerkID, true);
    callXML.send();
}

callXML.onreadystatechange = function () {
    if (callXML.status == 200 && callXML.readyState == 4)
    {
        if (callXML.responseText != 0)
        {
            if (callXML.responseText.trim() != "NO")
            {
                var result = JSON.parse(callXML.responseText);
                var eventNo = document.getElementById('eventno');
                var eventDate = document.getElementById('eventdate');
                lastID = result.eventID;
                lastCalledCategory = result.eventCategory;
                lastCalledNo = result.eventChar + result.eventNo;
                lastCalledDate = lang_enterTime + result.eventTime;
                //                            lastCalled.value = lastID;
                eventNo.innerHTML = lastCalledNo;
                eventDate.innerText = lastCalledDate;

                // update last called box
                lockBtn('call');
                document.getElementById('last-called').innerHTML = lastCalledNo;
                UpdateLastCalledStatus(0);
                resetRecallCount();

                if (document.getElementById('autocall').checked) {
                    recall(1);
                }


            } else
            {
                alert(alert_noClients);
                resetCounter();
            }
        } else
        {
            alert(alert_errorInOperation);
        }
    }
};



callByEventXML.onreadystatechange = function () {
    if (callByEventXML.readyState == 4 && callByEventXML.status == 200) {
        if (callByEventXML.responseText != 0)
        {
            if (callByEventXML.responseText.trim() != "NO")
            {
                var result = JSON.parse(callByEventXML.responseText);
                var eventNo = document.getElementById('eventno');
                var eventDate = document.getElementById('eventdate');
                lastID = result.eventID;
                lastCalledCategory = result.eventCategory;
                lastCalledNo = result.eventChar + result.eventNo;
                lastCalledDate = lang_enterTime + result.eventTime;
                //                            lastCalled.value = lastID;
                eventNo.innerHTML = lastCalledNo;
                eventDate.innerText = lastCalledDate;
                // update last called box
                document.getElementById('last-called').innerHTML = lastCalledNo;
                UpdateLastCalledStatus(0);
                resetRecallCount();

                if (document.getElementById('autocall').checked)
                    recall(1);
            } else
            {
                alert(alert_errorInOperation);
                resetCounter();
                eventClicked = 0;
            }
        } else
        {
            alert(alert_errorInOperation);
            eventClicked = 0;
        }

    } else {
        eventClicked = 0;
    }
};

function callByEvent(eventID) {
    if (eventClicked !== eventID) {
        eventClicked = eventID;
        callByEventXML.open("GET", "../api/counter/?op=15&event=" + eventID + "&counter=" + counterID, true);
        callByEventXML.send();
    }
}

// type: 0: recall only[defualt], 1: call then recall
function recall(type = 0) {
    if (recallCount > 0)
    {
        lastOperation = 2;
        if (lastID)
        {
            recallXML.open("GET", "../api/counter/?op=2&counter=" + counterID + "&clerk=" + clerkID + "&event=" + lastID + "&type=" + type, true);
            recallXML.send();
            callType = type;
//            recallCount--;
        } else
        {
            alert(alert_pleaseCall);
        }
    } else
    {
        lockBtn('recall');
}
}

recallXML.onreadystatechange = function () {
    if (recallXML.status == 200 && recallXML.readyState == 4)
    {
        if (recallXML.responseText != 0)
        {
            flashTimes = 10;
            if (callType == 0) {
                recallCount--;
                UpdateLastCalledStatus(1);
            }
            lockBtn('recall');
            lockBtn('call');

        } else
        {
            alert(alert_errorInOperation);
        }
    }
};

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


//==============================================================  | refresh Data
function showPriorityReversed(num, size) {
    if (num == 0) {
        return '-';
    } else {
        return size - num + 1;
    }
}
refreshXML.onreadystatechange = function () {
    if (refreshXML.status == 200 && refreshXML.readyState == 4)
    {
        var events = JSON.parse(refreshXML.responseText);
        var eventsTable = document.getElementById('eventItems');

        var header = "<tr class='small'>" +
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
            //alert("size:" + size + " length: " + lastCount);
            // start building table content
//                                eventsRows += header.replace("--eventsCount--", events[size].eventQty);
//                                lastCalledNo = events[size].lastCalled;
//                                document.getElementById('last-called').innerHTML = lastCalledNo;

            UpdateCounterData(events[size].eventQty, events[size].lastCalled, events[size].counterload);

            for (var curCount = 0; curCount < size; curCount++)
            {
                var pickBtn = (isPicker === 0 ? "" : "<a class='badge-red-sm hover-white' href='javascript:void(0);' onclick='callByEvent(--eventID--);'><span class='glyphicon glyphicon-arrow-up'></span></a>");
                var tableRow = "<tr class='event-row'>" +
                        "<td>--priority--</td>" +
                        "<td>--ticket--" + (events[curCount].eventTransferred == "1" ? "<img src='" + path_img_transferred + "' style='vertical-align:middle;'>" : "") + "</td>" +
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
//                                eventsRows += header.replace("--eventsCount--", 0);
            eventsRows += "<tr class='event-row'>" +
                    "<td colspan='4'>" + lang_empty + "</td>" +
                    "</tr>";

        }
        eventsTable.innerHTML = eventsRows;
    }
};
function refreshData() {
    if (counterState)
    {
        refreshXML.open("GET", "../api/counter/?op=4&counter=" + counterID + "&clerk=" + clerkID, true);
        refreshXML.send();
    }
}

function UpdateCounterData(totalWaiting, lastCalled, counterLoad) {
    lastCalledNo = lastCalled;

    document.getElementById('waiting').innerHTML = totalWaiting;
    document.getElementById('last-called').innerHTML = lastCalled;
    document.getElementById('c-load').innerHTML = counterLoad;
}

//==============================================================  | open / close counter

closeCounterXML.onreadystatechange = function () {
    if (closeCounterXML.status == 200 && closeCounterXML.readyState == 4)
    {
        if (parseInt(closeCounterXML.responseText))
        {
            flashTimes = 1;
            counterState = 0;
            textColor = inactiveColor;

            document.getElementById('eventno').innerHTML = lang_closed;
            document.getElementById('eventno').style.color = textColor;
            document.getElementById('eventdate').innerHTML = "&nbsp;";
            document.getElementById('open').disabled = false;
            document.getElementById('close').disabled = true;
            document.getElementById('call').disabled = true;
            document.getElementById('recall').disabled = true;
            document.getElementById('transfer').disabled = true;
            document.getElementById('autocall').disabled = true;
            document.getElementById('pending').disabled = true;
            document.getElementById('waitEvents').innerHTML = "";
            document.getElementById('pending-list').innerHTML = "<span class='red-text'>" + lang_closed + "</span>";

            var directBtn = document.getElementById('direct-transfer');
//                                 directBtn.disabled = true;
            if (directBtn) {
                directBtn.disabled = true;
            }
//            lockBtn('call');
//            lockBtn('recall');


//            document.getElementById('fullScreen').style.display = 'block';
        } else
        {
            alert(alert_errorClose);
        }
    }
};
function closeCounter() {
    closeCounterXML.open("GET", "../api/counter/?op=6&counter=" + counterID + "&clerk=" + clerkID, true);
    closeCounterXML.send();
}

function openCounter() {

    getPendingList();

    counterState = 1;
    textColor = activeColor;

    document.getElementById('eventno').style.color = textColor;
    document.getElementById('open').disabled = true;
    document.getElementById('close').disabled = false;
//    document.getElementById('call').disabled = false;
//    document.getElementById('recall').disabled = false;
    document.getElementById('transfer').disabled = false;
    document.getElementById('autocall').disabled = true;
    document.getElementById('pending').disabled = false;
    document.getElementById('waitEvents').innerHTML = "<table border='0' id='eventItems'></table>";

    var e_eventno = document.getElementById('eventno');
    var e_eventdate = document.getElementById('eventdate');

    if (lastCalledNo && lastID) {
        e_eventno.innerHTML = lastCalledNo;
        e_eventdate.innerHTML = lastCalledDate;
    } else {
        e_eventno.innerHTML = lang_opened;
        e_eventdate.innerHTML = "&nbsp;";
    }

    var directBtn = document.getElementById('direct-transfer');
//                        alert(directBtn);
    if (directBtn) {
        document.getElementById('direct-transfer').disabled = false;
    }

//    document.getElementById('fullScreen').style.display = 'none';
}

function resetCounter() {
    var e_eventno = document.getElementById('eventno');
    var e_eventdate = document.getElementById('eventdate');
    e_eventno.innerHTML = lang_opened;
    e_eventdate.innerHTML = '';
    lastID = 0;
    lastCalledNo = '';
    //                isCalled = 0;
}

//openCounter();

//==============================================================  | set counter active

setInterval(function () {
    setCounterActive();
}, 500);
//                var isSetActive = true;

var setCounterXML = new XMLHttpRequest();
setCounterXML.onreadystatechange = function () {
    if (setCounterXML.status = 200 && setCounterXML.readyState == 4) {
        if (parseInt(setCounterXML.responseText) == 1) {

        }
    }
};
function setCounterActive() {
    if (counterState) {
        setCounterXML.open('GET', '../api/update.php?type=counter&id=' + counterID + "&clerkid=" + clerkID);
        setCounterXML.send();
    }
}

//==============================================================  | login / logout

logoutXML.onreadystatechange = function () {
    if (logoutXML.status == 200 && logoutXML.readyState == 4)
    {
        if (parseInt(logoutXML.responseText))
        {
            location.reload();
        } else
        {
            alert(alert_errorLogout);
        }
    }
};
function logout() {
    if (window.confirm(msg_logoutMessage))
    {
        logoutXML.open("get", "../api/counter/?op=12", true);
        logoutXML.send();
    }
}



