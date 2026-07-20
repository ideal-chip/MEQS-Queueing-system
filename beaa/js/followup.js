// iDEAL-Q — follow-up card modal (booking / required papers / card history)
// Talks to ../api/counter/followupData.php (reads) and
// ../api/counter/followupForm.php (add/update/delete), using the existing,
// already-implemented backend contract.

var followupsPage = 1;
var followupsPages = 1;

function showBooking() {
    $('#booking-modal').modal('show');
    loadExtensionNumbers();
    resetAndShowForm();
    emptyForm();
    var $cat = $('#category-id');
    if ($cat.val()) {
        updateSubServices($cat.val());
    }
    loadFollowupsList(1);
}

function hideBooking() {
    $('#booking-modal').modal('hide');
}

function loadExtensionNumbers() {
    $.get('../api/counter/followupData.php?method=getextlist', function (data) {
        var list = (data && data !== 0 && data !== '0') ? (typeof data === 'string' ? JSON.parse(data) : data) : [];
        var $sel = $('#sub_fones');
        $sel.empty();
        list.forEach(function (ext) {
            $sel.append($('<option>').val(ext.extension_no).text(ext.extension_name ? ext.extension_name + ' (' + ext.extension_no + ')' : ext.extension_no));
        });
    });
}

function updateSubServices(categoryID, context) {
    var targetSelect = (context === 'papers') ? '#subcategory-id-papers' : '#subcategory-id';
    $.get('../api/counter/followupData.php?method=getsubcategories&categoryID=' + categoryID, function (data) {
        var list = (data && data !== 0 && data !== '0') ? (typeof data === 'string' ? JSON.parse(data) : data) : [];
        var $sel = $(targetSelect);
        $sel.empty();
        list.forEach(function (sub) {
            $sel.append($('<option>').val(sub.subcategory_id).text(sub.subcategory_name));
        });
        if (context === 'papers' && list.length) {
            updatePapersBySubcategory(targetSelect.replace('#', ''));
        } else if (list.length) {
            updateWaitTime($sel[0]);
        }
    });
}

function updateWaitTime(selectEl) {
    // wait time isn't returned by getsubcategories; pulled from the preview data
    // after issuing/loading a card, so nothing to do live here except keep the
    // selection in sync — placeholder hook kept for parity with the markup.
}

function updatePapersBySubcategory(selectID) {
    var subID = $('#' + selectID).val();
    if (!subID) return;
    $.get('../api/counter/followupData.php?method=getpapers&id=' + subID, function (data) {
        var row = (data && data !== 0 && data !== '0') ? (typeof data === 'string' ? JSON.parse(data) : data) : null;
        var papers = row && row.papers ? row.papers.split(';').map(function (p) { return p.trim(); }).filter(Boolean) : [];
        var $preview = $('#req-papers-preview');
        $preview.empty();
        if (!papers.length) {
            $preview.append($('<div>').addClass('text-gray').text('-'));
            return;
        }
        var $ul = $('<ul>').addClass('list-group');
        papers.forEach(function (p) {
            $ul.append($('<li>').addClass('list-group-item').text(p));
        });
        $preview.append($ul);
    });
}

function emptyForm() {
    $('#booking-form')[0].reset();
    $('#ajaxMode').val('add');
    $('#followup-id').val('0');
    $('#event-id').val(typeof currentEvent !== 'undefined' && currentEvent ? currentEvent.eventID : 0);
}

function resetAndShowForm() {
    $('#booking-preview').hide();
    $('#booking-form-con').show();
}

function fillPreview(data) {
    $('#f-datetime').text(data.date_created || '');
    $('#f-clerkName').text(data.clerk_name || '');
    $('#f-serialNo').text(data.serial_no || '');
    $('#f-clientName').text(data.client_name || '');
    $('#f-phoneNumber').text(data.mobile_number || '');
    $('#f-mainService').text(data.category_name || '');
    $('#f-subService').text(data.subcategory_name || '');
    $('#f-waittime').text(data.wait_time_days != null ? data.wait_time_days : '-');
    $('#sub_fone').text(data.extension_no || '');
    $('.fedit-btn').val(data.followup_id || '');
}

function SendBookingForm() {
    $('#ajax-error').hide();
    $('#ajax-error-list').empty();

    var mode = $('#ajaxMode').val() || 'add';
    var payload = $('#booking-form').serialize() + '&ajaxMode=' + mode;

    $.post('../api/counter/followupForm.php', payload, function (resp) {
        var result = (typeof resp === 'string') ? JSON.parse(resp) : resp;
        if (result && result.status) {
            fillPreview(result.data);
            $('#booking-form-con').hide();
            $('#booking-preview').show();
            loadFollowupsList(1);
        } else {
            var errors = (result && result.errors) ? result.errors : ['Unknown error'];
            var $list = $('#ajax-error-list');
            errors.forEach(function (e) {
                $list.append($('<li>').addClass('list-group-item').text(e));
            });
            $('#ajax-error').show();
        }
    }, 'json').fail(function () {
        $('#ajax-error-list').append($('<li>').addClass('list-group-item').text('Request failed'));
        $('#ajax-error').show();
    });
}

function editBookingForm(followupID) {
    if (!followupID) return;
    $.get('../api/counter/followupData.php?method=row&id=' + followupID, function (data) {
        var result = (typeof data === 'string') ? JSON.parse(data) : data;
        if (!result || !result.row) return;
        var row = result.row;
        $('#ajaxMode').val('update');
        $('#followup-id').val(row.followup_id);
        $('#event-id').val(row.event_id || 0);
        $('#client-name').val(row.client_name);
        $('#phone-number').val(row.mobile_number);
        $('#category-id').val(row.category_id);
        updateSubServices(row.category_id);
        setTimeout(function () { $('#subcategory-id').val(row.subcategory_id); }, 300);
        resetAndShowForm();
        $('a[href="#booking"]').tab('show');
    });
}

function printElement(elementID) {
    $('#' + elementID).print({
        globalStyles: true,
        mediaPrint: false,
        stylesheet: '../css/paper.bootstrap.min.css,../css/common.css,../css/counter.css'
    });
}

//====================================================================  | Followup card history (paginated list)

function loadFollowupsList(page) {
    followupsPage = page;
    var params = 'method=getlist&clerkId=' + clerkID + '&langId=' + interface_lang + '&page=' + page + '&max=10';
    $.get('../api/counter/followupData.php?' + params, function (data) {
        var result = (data && data !== 0 && data !== '0') ? (typeof data === 'string' ? JSON.parse(data) : data) : {list: [], total: 0, size: 1};
        followupsPages = result.size || 1;
        $('#totalItems').text(result.total || 0);
        $('#list-page').text(followupsPage + ' / ' + followupsPages);

        var $tbl = $('#followups-table');
        $tbl.empty();
        (result.list || []).forEach(function (row) {
            var $tr = $('<tr>');
            $tr.append($('<td>').text(row.serial_no));
            $tr.append($('<td>').text(row.client_name));
            $tr.append($('<td>').text(row.mobile_number));
            $tr.append($('<td>').text(row.category_name));
            $tr.append($('<td>').text(row.subcategory_name));
            $tr.append($('<td>').text(row.date_created));
            var $editBtn = $('<button>').addClass('btn btn-xs btn-primary fedit-btn').text(lang_edit)
                .on('click', function () { editBookingForm(row.followup_id); });
            $tr.append($('<td>').append($editBtn));
            $tbl.append($tr);
        });
    });
}

function getNextPrevPageFollowups(direction) {
    var target = followupsPage;
    switch (direction) {
        case 'first': target = 1; break;
        case 'prev': target = Math.max(1, followupsPage - 1); break;
        case 'next': target = Math.min(followupsPages, followupsPage + 1); break;
        case 'last': target = followupsPages; break;
    }
    loadFollowupsList(target);
}
