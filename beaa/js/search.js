// iDEAL-Q — admin ticket/followup search page (beaa/admin/search.php)
// Talks to the same ../api/counter/followupData.php + followupForm.php
// endpoints used by the counter's booking modal (beaa/js/followup.js).

function goToPage(page) {
    $('#page').val(page);
    $('#search-form').submit();
}

$(document).ready(function () {
    if (typeof $.fn.datepicker === 'function') {
        $('.date-pick').datepicker({dateFormat: 'yy-mm-dd'});
    }

    $('#show-advanced').on('click', function () {
        $('#advanced-search').slideToggle();
    });
});

function updateSubServices(categoryID, context) {
    var targetSelect = context ? '#subcategory-id' : '#fsubcategory-id';
    $.get('../api/counter/followupData.php?method=getsubcategories&categoryID=' + categoryID, function (data) {
        var list = (data && data !== 0 && data !== '0') ? (typeof data === 'string' ? JSON.parse(data) : data) : [];
        var $sel = $(targetSelect);
        $sel.empty().append($('<option>').val('0').text('-'));
        list.forEach(function (sub) {
            $sel.append($('<option>').val(sub.subcategory_id).text(sub.subcategory_name));
        });
    });
}

function updateWaitTime(selectEl) {
    // no live UI dependent on this; kept for markup parity.
}

//====================================================================  | Edit modal

function editBookingRow(followupID) {
    $.get('../api/counter/followupData.php?method=row&id=' + followupID, function (data) {
        var result = (typeof data === 'string') ? JSON.parse(data) : data;
        if (!result || !result.row) return;
        var row = result.row;
        $('#ajaxMode').val('update');
        $('#followup-id').val(row.followup_id);
        $('#event-id').val(row.event_id || 0);
        $('#clerk-id').val(row.clerk_id || 0);
        $('#client-name').val(row.client_name);
        $('#phone-number').val(row.mobile_number);
        $('#category-id').val(row.category_id);
        updateSubServices(row.category_id, 2);
        setTimeout(function () { $('#subcategory-id').val(row.subcategory_id); }, 300);
        $('#booking-form-con').show();
        $('#booking-preview').hide();
        $('#booking-modal').modal('show');
    });
}

function SendBookingForm() {
    $('#ajax-error').hide();
    $('#ajax-error-list').empty();

    $.post('../api/counter/followupForm.php', $('#booking-form').serialize() + '&ajaxMode=update', function (resp) {
        var result = (typeof resp === 'string') ? JSON.parse(resp) : resp;
        if (result && result.status) {
            $('#booking-modal').modal('hide');
            location.reload();
        } else {
            var errors = (result && result.errors) ? result.errors : ['Unknown error'];
            var $list = $('#ajax-error-list');
            errors.forEach(function (e) {
                $list.append($('<li>').addClass('list-group-item').text(e));
            });
            $('#ajax-error').show();
        }
    }, 'json');
}

//====================================================================  | Row actions

function deleteRow(followupID, rowId) {
    if (!confirm(lang_deleteQuestion)) return;
    $.post('../api/counter/followupForm.php', {ajaxMode: 'delete', followup_id: followupID}, function (resp) {
        var result = (typeof resp === 'string') ? JSON.parse(resp) : resp;
        if (result && result.status) {
            $('#' + rowId).fadeOut(200, function () { $(this).remove(); });
        }
    }, 'json');
}

function markProcessed(followupID, rowId) {
    $.get('../api/counter/followupForm.php?ajaxMode=mark_done&id=' + followupID, function (data) {
        var result = (typeof data === 'string') ? JSON.parse(data) : data;
        if (result && result.date_done) {
            $('#' + rowId + ' td').eq(6).text(result.date_done);
            $('#' + rowId + ' .btn-info').remove();
        }
    });
}

function showFollowupPreview(followupID) {
    $.get('../api/counter/followupData.php?method=getpreview&id=' + followupID + '&langId=' + $('#lang-id').val(), function (data) {
        var row = (typeof data === 'string') ? JSON.parse(data) : data;
        if (!row) return;
        $('#f-datetime').text(row.date_created || '');
        $('#f-clerkName').text(row.clerk_name || '');
        $('#f-serialNo').text(row.serial_no || '');
        $('#f-clientName').text(row.client_name || '');
        $('#f-phoneNumber').text(row.mobile_number || '');
        $('#f-mainService').text(row.category_name || '');
        $('#f-subService').text(row.subcategory_name || '');
        $('#f-waittime').text(row.wait_time_days != null ? row.wait_time_days : '-');
        $('#sub_fone').text(row.extension_no || '');
        $('#booking-form-con').hide();
        $('#booking-preview').show();
        $('#booking-modal').modal('show');
    });
}

function printElement(elementID) {
    $('#' + elementID).print({globalStyles: true, mediaPrint: false});
}

//====================================================================  | Export / print result table

function exportData(elementId) {
    var el = document.getElementById(elementId);
    if (!el) return;
    var html = '<html><head><meta charset="utf-8"></head><body>' + el.outerHTML + '</body></html>';
    var blob = new Blob([html], {type: 'application/vnd.ms-excel'});
    saveAs(blob, 'search-results.xls');
}

function printSeachResult(elementId) {
    $('#' + elementId).print({globalStyles: true, mediaPrint: false});
}
