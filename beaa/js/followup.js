$(document).ready(function () {
    
    updateSubFone();
    
    $('#booking-modal').on('shown.bs.modal', function () {
        $('#client-name').focus();
    });
    $('.nav-tabs a[href="#papers"]').on('show.bs.tab', function () {
        updateSubServices($("#category-id-papers").val(), 'papers');
    });
    $('.nav-tabs a[href="#papers"]').on('shown.bs.tab', function () {
        updatePapersBySubcategory("subcategory-id-papers");
    });
    $('.nav-tabs a[href="#booking"]').on('show.bs.tab', function () {
        emptyForm();

    });
    $('.nav-tabs a[href="#followups"]').on('shown.bs.tab', function () {
        updatefollowupsList();
    });

    updateNavButtons(currentPage);
});
//==============================================================  || Vars

var followupHeads = [lang_clientName, lang_phoneNumber, lang_directorate, lang_serviceType, lang_serialNo, lang_dateTime, lang_clerkName, 'followup_id'];
var currentPage = 1;
var listSize = 0;

var lastSubService = 0;
var lastMainService = 0;

//==============================================================  || showBooking

function showBooking() {

    resetAndShowForm();

    // select main service based on ticket called
    updateMainService(lastCalledCategory);
    // update subservices list based on main service
    updateSubServices(lastCalledCategory);

    // show updated modal
//        $("#booking-modal").modal('show');
    $('#booking-modal').modal({
        keyboard: true
    });
}

function updateWaitTime(item) {
    var option = $(item).find(":selected");
    var waitTime = $(option).data('waittime');
    $("#wait-time").text(waitTime);
}

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

function updateMainService(categoryID) {
    categoryID = (categoryID > 0) ? categoryID : 1;
    $("#category-id").val(categoryID);
}

function updateSubServices(categoryID, section) {

    categoryID = categoryID == 0 ? 1 : categoryID;
    $.ajax({
        type: 'get',
        dataType: 'json',
        cache: false,
        url: '../api/counter/followupData.php',
        data: {method: 'getsubcategories', categoryID: categoryID},
        success: function (response, textStatus, jqXHR) {
            var content = '';

            if (response) {


                for (var i = 0; i < response.length; i++) {
                    var selected = i == 0 ? "selected='true'" : '';
                    var item = "<option " + selected + " value='" + response[i].subcategory_id + "'>" + response[i].subcategory_name + "</option>";
                    content += item;
                }
                if (section == 'papers') {
                    $("#subcategory-id-papers").html(content);
                    updatePapersBySubcategory('subcategory-id-papers');
                } else {
                    $("#subcategory-id").html(content);
                }
            } else {
                if (section == 'papers') {
                    $("#subcategory-id-papers").html(content);
                    updatePapersBySubcategory('subcategory-id-papers', 1);
                } else {
                    $("#subcategory-id").html(content);
                }
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
        }
    });
}

//==============================================================  || FORM

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

                    fillpreview(response.data);
                    toggleForm('hide');
//                    console.log(response.data);
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

function showFollowupPreview(id) {
    $.ajax({
        type: 'get',
        dataType: 'json',
        cache: false,
        url: '../api/counter/followupData.php',
        data: {method: 'getpreview', id: id, langId: interface_lang},
        success: function (response, textStatus, jqXHR) {

            if (response) {
                $('.nav-tabs a[href="#booking"]').tab('show');
                fillpreview(response);
                toggleForm('hide');
//                console.log(response);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
        }
    });
}

function editBookingRow(id) {

    // switch to tab edit form
    $('.nav-tabs a[href="#booking"]').tab('show');

    // call editForm
    editBookingForm(id);
}

function updateSubServiceEdit() {
    updateSubServices(lastMainService, '');
    $("#subcategory-id").val(lastSubService);
}

function editBookingForm(id) {

//    var followupID = $("#" + id).attr('data-edit');
    var followupID = id;
    // get data based on ID
    // switch ajaxMode to edit

    $.ajax({
        type: 'get',
        dataType: 'json',
        cache: false,
        url: '../api/counter/followupData.php',
        data: {method: 'row', id: followupID},
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
                $("#ajaxMode").val("update");
                $("#sub_fones").val(row.extension_no);

//                updateSubServiceEdit();


            }
//            console.log(response);
        },
        error: function (jqXHR, textStatus, errorThrown) {
        }
    });
}

function updatePapersBySubcategory(itemID, status) {

    if (status && status == 1) {
        $("#req-papers-preview").html("<div class='empty'>" + lang_empty + "</div>");

        return;
    }
    var subcategoryID = $("#" + itemID).val();
    $.ajax({
        type: 'get',
        dataType: 'json',
        cache: false,
        url: '../api/counter/followupData.php',
        data: {method: 'getpapers', id: subcategoryID},
        success: function (response, textStatus, jqXHR) {

            if (response) {

                $("#req-papers-preview").html(response.papers);
                $("#req-papers-preview p").each(function () {
                    if ($(this).text().trim() == '') {
                        $(this).remove();
                    }
                });
                $(".sceditor-nlf").remove();
            }
//            console.log(response);
        },
        error: function (jqXHR, textStatus, errorThrown) {
        }
    });
}

function getNextPrevPageFollowups(type) {

    switch (type) {
        case 'first':
            currentPage = 1;
            break;
        case 'last':
            currentPage = listSize;
            break;
        case 'next':
            currentPage++;
            if (currentPage >= listSize) {
                currentPage = listSize;
            }
            break;
        case 'prev':
            currentPage--;
            if (currentPage <= 1) {
                currentPage = 1;
            }
            break;
        default :
            break;
    }

    updatefollowupsList(currentPage);

}

function updateNavButtons(page) {

    if (listSize <= 1) {
        $("#nav-prev").attr('disabled', 'disabled');
        $("#nav-first").attr('disabled', 'disabled');
        $("#nav-next").attr('disabled', 'disabled');
        $("#nav-last").attr('disabled', 'disabled');
    } else {
        if (page == 1) {
            $("#nav-prev").attr('disabled', 'disabled');
            $("#nav-first").attr('disabled', 'disabled');
            $("#nav-next").removeAttr('disabled');
            $("#nav-last").removeAttr('disabled');
        } else if (page == listSize) {
            $("#nav-next").attr('disabled', 'disabled');
            $("#nav-last").attr('disabled', 'disabled');
            $("#nav-prev").removeAttr('disabled');
            $("#nav-first").removeAttr('disabled');
        } else {
            $("#nav-prev").removeAttr('disabled');
            $("#nav-next").removeAttr('disabled');
            $("#nav-first").removeAttr('disabled');
            $("#nav-last").removeAttr('disabled');
        }
    }

}

function updatefollowupsList(page = 1) {
//alert(page);
    $.ajax({
        type: 'get',
        dataType: 'json',
        cache: false,
        url: '../api/counter/followupData.php',
        data: {method: 'getlist', clerkId: clerkID, max: 10, page: page, langId: interface_lang},
        success: function (response, textStatus, jqXHR) {

            if (response) {

                var list = response.list;
                var size = response.size;
                var total = response.total;

                var count = list.length;

                var content = '';
                var table = $("#followups-table");
                $(table).html('');
                content += "<thead><tr class='bg-green-dark text-whito text-center-th'>" +
                        "<th>" + lang_clientName + "</th>" +
                        "<th>" + lang_phoneNumber + " </th>" +
                        "<th>" + lang_directorate + " </th>" +
                        "<th>" + lang_serviceType + " </th>" +
                        "<th>" + lang_serialNo + " </th>" +
                        "<th>" + lang_dateTime + " </th>" +
                        "<th></th></tr></thead>";
//                content += renderHead(followupHeads, followupHeads.length - 1);

                content += "<tbody>";
                for (var i = 0; i < count; i++) {
                    var obj = list[i];
                    content += "<tr>" +
                            "<td>" + obj.client_name + " </td>" +
                            "<td>" + obj.mobile_number + " </td>" +
                            "<td>" + obj.category_name + " </td>" +
                            "<td>" + obj.subcategory_name + " </td>" +
                            "<td>" + obj.serial_no + " </td>" +
                            "<td>" + obj.date_created + " </td>" +
                            "<td class='tb-remove'>" +
                            "<a class='btn btn-success btn-xs' onclick='editBookingRow(" + obj.followup_id + ")' href='javascript:void(0);'>" + lang_edit + "</a> | " +
                            "<a class='btn btn-danger btn-xs' onclick='deleteRow(" + obj.followup_id + ")' href='javascript:void(0);'>" + lang_delete + "</a> | " +
                            "<a class='btn btn-link btn-xs' onclick='showFollowupPreview(" + obj.followup_id + ")' href='javascript:void(0);'><i class='fa fa-print'></i></a>" +
                            "</td>" +
                            "</tr>";
                }
                content += "</tbody>";
                $("#list-page").text(page);
                $("#totalItems").text(total);
//                $("#page").val(page);

//                currentPage = page;
                listSize = size;
                updateNavButtons(page);

                $(table).html(content);
            }
//            console.log(response);
        },
        error: function (jqXHR, textStatus, errorThrown) {
        }
    });
}

function deleteRow(id) {
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
                        updatefollowupsList(currentPage);
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

function fillpreview(info) {

    $("#f-clerkName").text(info.clerk_name);
    $("#f-datetime").text(info.date_created);
    $("#f-serialNo").text(info.serial_no);
    $("#f-clientName").text(info.client_name);
    $("#f-phoneNumber").text(info.mobile_number);
    $("#f-mainService").text(info.category_name);
    $("#f-subService").text(info.subcategory_name);
    $("#f-waittime").text(info.wait_time_days);

//    $("#sub_fone").text($("#sub_fones").val());
    $("#sub_fone").text(info.extension_no);

    $(".fedit-btn").val(info.followup_id);
}

function resetAndShowForm() {
    toggleForm('show');
    //add/update last event ID <hidden fields>
    $("input[name=event-id]").val(lastID);
    $("#ajaxMode").val("add");
    $("#followup-id").val(0);
}
function toggleForm(status) {
    if (status == 'hide') {
        $("#booking-form-con").fadeOut('fast');
        $("#booking-preview").fadeIn();
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