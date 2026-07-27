var flashTimes = 0;
var lastEventID = 0;

var updated = null, eventData = null;

$(document).ready(function () {

    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|| date
    var d = new Date();
    var newDate = setDate(d);

    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|| new clock [clock 1.1.0]
    var clock1 = $("#myclock2").clock({
        width: 160,
        height: 250,
        theme: 't2',
        date: newDate
    });

    updateData(displayID);
});

setInterval(function () {
    flashing();
}, 500);

setInterval(function () {
    updateData(displayID);
}, 3000);

function setDate(d) {

    d.setHours(hour);
    d.setMinutes(minutes);
    d.setSeconds(seconds);

    document.getElementById('day').innerHTML = day > 9 ? day : "0" + day;
    document.getElementById('month').innerHTML = month > 9 ? month : "0" + month;
    document.getElementById('year').innerHTML = year;

    return d;
}

function updateData(id) {
    $.ajax({
        type: 'get',
        dataType: 'json',
        cache: false,
        url: '../api/display/',
        data: {id: id},
        success: function (response, textStatus, jqXHR) {

//            console.log(response);
            if (response)
            {
                updated = response.updated;
                if (updated == 1) {
                    location.reload();
//                    console.log("true");
                } else {
                    eventData = response.event;

                    if (eventData.active == "1") {
//console.log(data.active);
                        if (lastEventID != eventData.ID) {
                            document.getElementById('num').innerHTML = eventData.ticket;
                            document.getElementById('num').style.color = 'white';
                            document.getElementById('num').style.textShadow = '0 0 5px white';

                            lastEventID = eventData.ID;
                            flashTimes = 10;
                        }
                    } else {

                        flashTimes = 0;
                        document.getElementById('num').innerHTML = lang_closed;
                        document.getElementById('num').style.color = 'red';
                        document.getElementById('num').style.textShadow = '0 0 5px red';
                    }
                }
                
                updated = null, eventData = null;
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
        }
    });
}

function flashing() {
    if (flashTimes)
    {
        if (flashTimes-- & 1)
        {
            document.getElementById('num').style.color = 'white';
            document.getElementById('num').style.textShadow = '0 0 5px white';
        } else
        {
            document.getElementById('num').style.color = 'transparent';
            document.getElementById('num').style.textShadow = '0 0 5px transparent';
        }
    }
}