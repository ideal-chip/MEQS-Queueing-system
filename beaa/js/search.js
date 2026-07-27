
$(document).ready(function () {

    updateSubFone();
    $('#booking-modal').on('shown.bs.modal', function () {
        $('#client-name').focus();
    });

});

function updateSubFone() {
    $.ajax({
        type: 'get',
        dataType: 'json',
        cache: false,
        url: '../api/counter/followupData.php',
        data: {method: 'getextlist'},
        success: function (response, textStatus, jqXHR) {
            var content = '';

            if (response) {
                for (var i = 0; i < response.length; i++) {
                    var item = "<option value='" + response[i].extension_no + "'> (" + response[i].extension_no +") "+ response[i].extension_name+ "</option>";
                    content += item;
                }
                $("#sub_fones").html(content);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
        }
    });
}
//==============================================================|| ajax form

function editBookingRow(id, rowId) {
//    toggleForm('show');
//    // select main service based on ticket called
//    updateMainService(lastCalledCategory);
//    // update subservices list based on main service
//    updateSubServices(lastCalledCategory);

    // show updated modal
//        $("#booking-modal").modal('show');
    $("#row-id").val(rowId);

    editBookingForm(id);
    $('#booking-modal').modal({
        keyboard: true
    });

    // call editForm

}

function editBookingForm(id) {
//    if (rowId == null || rowId == '' || rowId == 0) {
//        rowId = $("#row-id").val();
//        console.log("rowid: " + rowId);
//    } else {
//        $("#row-id").val(rowId);
//    }
    $.ajax({
        type: 'get',
        dataType: 'json',
        cache: false,
        url: '../api/counter/followupData.php',
        data: {method: 'row', id: id},
        success: function (response, textStatus, jqXHR) {

            if (response) {

                var row = response.row;
                var subcategories = response.subcategories;

                toggleForm('show');

                var content = ''
                for (var i = 0; i < subcategories.length; i++) {
                    var item = subcategories[i];
                    var selected = (item.subcategory_id == row.subcategory_id) ? "selected='true'" : '';
                    var op = "<option " + selected + " value='" + item.subcategory_id + "'>" + item.subcategory_name + "</option>";
                    content += op;
                }

                $("#subcategory-id").html(content);


                $("#client-name").val(row.client_name);
                $("#phone-number").val(row.mobile_number);
                $("#category-id").val(row.category_id);
//                $("#subcategory-id").val(row.subcategory_id);
                $("#followup-id").val(row.followup_id);
                $("#event-id").val(row.event_id);
                $("#clerk-id").val(row.clerk_id);
                $("#sub_fones").val(row.extension_no);
//                $("#ajaxMode").val("update");

//                updateSubServiceEdit();


            }
//            console.log(response);
        },
        error: function (jqXHR, textStatus, errorThrown) {
        }
    });
}
function SendBookingForm() {

    var values = $("#booking-form").serialize();
    $.ajax({
        type: 'post',
        dataType: 'json',
        cache: false,
        url: '../api/counter/followupForm.php',
        data: values,
        success: function (response, textStatus, jqXHR) {

            if (response) {
                var errors = response.errors;
                var status = response.status;
                if (status) {
                    var rowId = $("#row-id").val();
                    fillpreview(response.data, rowId);
                    toggleForm('hide');
                    updateRow(response.data, rowId);
                    console.log(response.data);
                } else {
                    var errTXT = "";
                    for (var i = 0; i < errors.length; i++) {
                        errTXT += "<li class = 'error-item'>" + errors[i] + "</li>";
                    }

                    $("#ajax-error-list").html(errTXT);
                    $("#ajax-error").fadeIn();
//                    console.log(response.data);
                }
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
        }
    });
}
function updateRow(info, rowId) {

    var dateDone = info.date_done != null ? info.date_done : '-';
    var markDone = info.date_done == null ? "<a class='btn btn-info btn-xs' onclick='markProcessed(" + info.followup_id + ", &apos;" + rowId + "&apos;)' href='javascript:void(0);'>" + lang_markProcessed + " </a> " : "";
    var totalDays = info.total_days != null ? info.total_days : '-';
    var order = rowId.substr(2);

    var smsDone = info.date_sms_sent != null ? '<span class="badge pad-3  text-primary">SMS <i class="fa fa-check-circle"></i> </span>' : '';

    var row = "<tr id=" + rowId + ">" +
            "<td>" + order + " | <i class='fa fa-check-circle'></i> " + "</td>" +
            "<td>" + info.serial_no + "<br>" + smsDone + "</td>" +
            "<td class='text-left'>" +
            "<div>" + info.client_name + "</div>" +
            "<div class='bg-success pad-h-5'>" + info.mobile_number + "</div>" +
            "</td>" +
            "<td class='text-left'>" +
            "<div>" + info.category_name + "</div>" +
            "<div class='bg-success pad-h-5'>" + info.subcategory_name + "</div>" +
            "</td>" +
            "<td>" + info.clerk_name + "</td>" +
            "<td>" + info.date_created + "</td>" +
            "<td>" + dateDone + "</td>" +
            "<td>" + info.wait_time_days + "</td>" +
            "<td>" + totalDays + "</td>" +
            "<td class='tb-remove text-left no-print'>" +
            "<a class='btn btn-success btn-xs' onclick='editBookingRow(" + info.followup_id + ", &apos;" + rowId + "&apos;)' href='javascript:void(0);'>" + lang_edit + "</a> | " +
            "<a class='btn btn-danger btn-xs' onclick='deleteRow(" + info.followup_id + ", &apos;" + rowId + "&apos;)' href='javascript:void(0);'>" + lang_delete + "</a> | " +
            "<a class='btn btn-link btn-xs' onclick='showFollowupPreview(" + info.followup_id + ", &apos;" + rowId + "&apos;)' href='javascript:void(0);'>" +
            "<i class='fa fa-print'></i>" +
            "</a>" +
            "</td><td class='tb-remove text-left no-print'>" + markDone + "</td>" +
            "</tr>";

    $("#" + rowId).replaceWith(row);
}
function fillpreview(info, rowId) {

    $("#f-clerkName").text(info.clerk_name);
    $("#f-datetime").text(info.date_created);
    $("#f-serialNo").text(info.serial_no);
    $("#f-clientName").text(info.client_name);
    $("#f-phoneNumber").text(info.mobile_number);
    $("#f-mainService").text(info.category_name);
    $("#f-subService").text(info.subcategory_name);
    $("#f-waittime").text(info.wait_time_days);
    $("#row-id").val(rowId);

//    $("#sub_fone").text(info.extension_no);

    $(".fedit-btn").val(info.followup_id);
}
function showFollowupPreview(id, rowId) {
//    console.log("clicked");
    $.ajax({
        type: 'get',
        dataType: 'json',
        cache: false,
        url: '../api/counter/followupData.php',
        data: {method: 'getpreview', id: id, langId: currentLang},
        success: function (response, textStatus, jqXHR) {
//            console.log(response);
            if (response) {
//                $('.nav-tabs a[href="#booking"]').tab('show');
                fillpreview(response, rowId);
                toggleForm('hide');
                $('#booking-modal').modal({
                    keyboard: true
                });

            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
        }
    });
}
function deleteRow(id, rowId) {
    if (confirm(lang_deleteQuestion)) {

        $.ajax({
            type: 'post',
            dataType: 'json',
            cache: false,
            url: '../api/counter/followupForm.php',
            data: {ajaxMode: 'delete', followup_id: id},
            success: function (response, textStatus, jqXHR) {

                if (response) {
                    var errors = response.errors;
                    var status = response.status;
                    if (status) {
//                        alert("deleted successfully");
                        //updatefollowupsList(currentPage);1
                        $("#" + rowId).remove();
                    } else {

                    }
                }
//                console.log(response);
            },
            error: function (jqXHR, textStatus, errorThrown) {
            }
        });
    }
}
function toggleForm(status) {
    if (status == 'hide') {
        $("#booking-preview").fadeIn();
        $("#booking-form-con").fadeOut('fast');

    } else {
        $("#booking-form-con").fadeIn();
        $("#booking-preview").fadeOut('fast');


    }
    emptyForm();
}

function emptyForm() {
    $(".form-ok").val("");
    $("#ajax-error-list").html("");
    $("#ajax-error").hide();
}
//==============================================================|| Sub Services
function updateSubServices(categoryID, section = 1) {

    if (categoryID > 0) {
        $.ajax({
            type: 'get',
            dataType: 'json',
            cache: false,
            url: '../api/counter/followupData.php',
            data: {method: 'getsubcategories', categoryID: categoryID},
            success: function (response, textStatus, jqXHR) {

                if (response) {
                    if (section == 1) {
                        var content = '<option value="0" selected="">-</option>'
                        for (var i = 0; i < response.length; i++) {
//                        var selected = i == 0 ? "selected='true'" : '';
                            var item = "<option value='" + response[i].subcategory_id + "'>" + response[i].subcategory_name + "</option>";
                            content += item;
                        }
                        $("#fsubcategory-id").html(content);
                    } else {
                        var content = ''
                        for (var i = 0; i < response.length; i++) {
                            var selected = i == 0 ? "selected='true'" : '';
                            var item = "<option " + selected + " value='" + response[i].subcategory_id + "'>" + response[i].subcategory_name + "</option>";
                            content += item;
                        }
                        $("#subcategory-id").html(content);
                    }

                } else {

                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
            }
        });
}
}
//==============================================================|| functions
function markProcessed(id, rowId) {
    if (confirm(lang_confirmQuestion)) {

        $.ajax({
            type: 'get',
            dataType: 'json',
            cache: false,
            url: '../api/counter/followupForm.php',
            data: {ajaxMode: 'mark_done', id: id},
            success: function (response, textStatus, jqXHR) {
                console.log(response);
                if (response) {
                    var ok = " | <i class='fa fa-check-circle'></i> "
                    var order = $("#" + rowId + " > td:nth-child(1)").text();
                    $("#" + rowId + " > td:nth-child(1)").html(order + ok);
                    $("#" + rowId + " > td:nth-child(11) > a:nth-child(1)").fadeOut('slow').remove();
                    $("#" + rowId + " > td:nth-child(7)").text(response.date_done);
                    $("#" + rowId + " > td:nth-child(9)").text(response.total_days);

                } else {
                    alert("Error");
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
            }
        });
    }

}
//==============================================================|| Form


$(".date-pick").datepicker({
    dateFormat: "yy-mm-dd"
});

$("#show-advanced").click(function () {
    $("#advanced-search").slideToggle('fast', function(){
        var shown = '1';
        if ($(this).css('display') == 'none') {
            shown = '0';
        }
        
        $("#adv").val(shown);
    });
});

//==============================================================|| pager

function goToPage(page) {
    $("#page").val(page);
    $("#search-form").submit();
}


//==============================================================|| lang switcher

$(".lang-radio").click(function () {
    markActive(this);
    if (this.id == 'all') {
        refreshAllPages = true;
    } else {
        refreshAllPages = false;
    }
});

function markActive(el)
{
    $(".lang-radio").removeClass('pressed');
    $(el).addClass('pressed');
}


//==============================================================|| print

function printSeachResult(id) {

    var item = document.getElementById(id);
    var clone = item.cloneNode(true);
//    $(clone).find("#fig1").remove();
//    $(clone).find("#img-fig").show();
//    $(clone).addClass("no-marg");
//    $(clone).removeClass("marg-v-50");


    $(clone).print({
        globalStyles: true,
        mediaPrint: false,
        iframe: true,
        noPrintSelector: ".no-print",
        prepend: "<hr><img class='text-center' src='../files/logos/env-logo.jpg' alt=''/><h6><hr>" + title + "</h6><hr>",
        append: "<hr><p class='small'>reported by idealchip iDEAL-Q QMS - on " + todayDate + "</p>"

    });
}