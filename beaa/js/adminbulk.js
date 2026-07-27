
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|| Variables
var refreshPageXML = new XMLHttpRequest();
var updateAllXML = new XMLHttpRequest();
var lastClicked = '';


var delayEle = document.getElementById("delayVal");
var delayBtn = document.getElementById("bulkBtn");
var minVal = 0;
var maxVal = 100;


//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|| set delay

function updateDelay(dir) {
    var val = parseInt(delayEle.innerHTML);
    if (dir == "up") {
        val = (val == maxVal) ? minVal : val + 1;
    } else {
        val = (val == minVal) ? maxVal : val - 1;
    }
    delayEle.innerHTML = val;
}

function setDelay() {
    var val = parseInt(delayEle.innerHTML);
//    delayBtn.disabled = true;
    $.ajax({
        type: 'get',
        dataType: 'json',
        cache: false,
        url: '../api/update.php',
        data: {id: 1, type: 'bulkdelay', value: val},
        success: function (response, textStatus, jqXHR) {
            if (response) {
                delayBtn.innerHTML = updateText;
                $(delayBtn).removeClass('btn-primary');
                $(delayBtn).addClass('btn-danger');
                updateAll();
                window.setTimeout(function () {
                    changeBtnText('bulkBtn', 'btn-primary', updateDelayText);
                }, 1100);

            } else {
            }
//            delayBtn.disabled = false;
        },
        error: function (jqXHR, textStatus, errorThrown) {
        }
    });
}

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|| refresh page

refreshPageXML.onreadystatechange = function () {
    if (refreshPageXML.readyState == 4 && refreshPageXML.status == 200) {
        if (parseInt(refreshPageXML.responseText) == 1) {
            var btn = 'btn-update' + lastClicked;
            btnElement = document.getElementById(btn);
            btnElement.innerHTML = updateText;
            $('#' + btn).removeClass('btn-warning');
            $('#' + btn).addClass('btn-danger');

            //updateAll();
            window.setTimeout(function () {
                changeBtnText(btn, 'btn-warning', recallText);
            }, 1100);
        }
    }
};
function refreshPage(id) {
    lastClicked = id;
    refreshPageXML.open('GET', '../api/update.php?type=bigdisplay&id=' + id);
    refreshPageXML.send();
}

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|| update all
updateAllXML.onreadystatechange = function () {
    if (updateAllXML.readyState == 4 && updateAllXML.status == 200) {
        if (parseInt(updateAllXML.responseText) == 1) {

            btnElement = document.getElementById('recall-all');
            btnElement.innerHTML = btnElement.innerHTML.replace(recallAllText, updateText);
            $('#recall-all').removeClass('btn-info');
            $('#recall-all').addClass('btn-danger');

            window.setTimeout(function () {
                changeBtnText('recall-all', 'btn-info', recallAllText);
            }, 1200);
        }
    }
};
function updateAll() {
    updateAllXML.open('GET', '../api/update.php?type=allbigdisplay&id=1&bdtype=2');
    updateAllXML.send();
}
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|| Functions
function changeBtnText(btn, cssClass, btnText) {
    btnElement = document.getElementById(btn);
    btnElement.classList.remove('btn-danger');
    btnElement.classList.add(cssClass);
    btnElement.innerHTML = btnElement.innerHTML.replace(updateText, btnText);
}
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|| toggle slide up/down
var $titleLess = 'see less';
var $titleMore = 'see more';
$(".moreBtn").click(function ()
{
    $button = $(this);
    $item = $(this).closest('.cats-item');
    $sec = $item.find(".cats-content");
    if ($sec.css("display") == 'none') {
        $item.find(".cats-content").slideDown('fast');
        $button.attr("title", $titleLess);
        resizeIframe($('#page'));
    } else {
        $item.find(".cats-content").slideUp('fast');
        $button.attr("title", $titleMore);
    }

});

//==============================================================  | BUlK calling start/ stop
var isBulkActive = 0;
var isBulkInctive = 0;
setInterval(function () {
    checkStatus();
}, 1000);
var checkBulkStatusXML = new XMLHttpRequest();
function checkStatus() {
    checkBulkStatusXML.open('GET', '../api/checkupdate.php?id=1&type=bulkstatus');
    checkBulkStatusXML.send();
}

checkBulkStatusXML.onreadystatechange = function () {
    if (checkBulkStatusXML.readyState == 4 && checkBulkStatusXML.status == 200) {
        val = parseInt(checkBulkStatusXML.responseText);
        //alert(val);
        if (val === 1) {
            if (!isBulkActive) {
                $('#blk-start').hide();
                $('#blk-stop').show();
                document.getElementById('bulk-active').innerHTML = lang_active;
                isBulkActive = 1;
                isBulkInctive = 0;
            }

        } else {
            if (!isBulkInctive) {
                $('#blk-start').show();
                $('#blk-stop').hide();
                document.getElementById('bulk-active').innerHTML = lang_inactive;
                isBulkActive = 0;
                isBulkInctive = 1;
            }
        }

    }
};

var bulkStatusXML = new XMLHttpRequest();
function setBulkStatus(status) {
    bulkStatusXML.open("GET", "../api/update.php?id=1&status=" + status + "&type=bulkstatus", true);
    bulkStatusXML.send();
}

bulkStatusXML.onreadystatechange = function () {
    if (bulkStatusXML.status == 200 && bulkStatusXML.readyState == 4)
    {
        response = bulkStatusXML.responseText.trim();
        if (response == 'active')
        {
//document.getElementById('bulk-active').innerHTML = 'active';
//alert('active bulk-active');
//location.reload();
        } else if (response == 'not active')
        {
//document.getElementById('bulk-active').innerHTML = 'not active';
//alert('NOT active');
//location.reload();
        }
    }

};
