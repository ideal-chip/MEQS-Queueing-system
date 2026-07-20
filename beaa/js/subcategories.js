// iDEAL-Q — admin subcategories page (beaa/admin/subcategories.php)
// Talks directly to ./views/subcategories/process.php (add/update/delete/report/row).

var AJAX_URL = './views/subcategories/process.php';

$(document).ready(function () {
    $('#req-papers').sceditor({
        plugins: 'xhtml',
        style: '../js/minified/themes/default.min.css',
        width: '100%',
        height: 250
    });
});

function getEditor() {
    return $('#req-papers').sceditor('instance');
}

function showForm(mode, subID) {
    $('#ajax-error').hide();
    $('#ajax-error-list').empty();
    $('#sub-modal').show();

    if (mode === 'add') {
        $('#ajaxMode').val('add');
        $('#subcategory-id').val('');
        $('#sub-name').val('');
        $('#sub-waittime').val('');
        getEditor().val('');
        $('#field-section').show();
        $('#del-section').hide();
        $('#btn-submit').text(text_add);
        return;
    }

    // the GET branch in process.php is only reached when ajaxMode is non-empty,
    // even though its value isn't used for a plain row fetch.
    $.get(AJAX_URL + '?subcategory=' + subID + '&ajaxMode=row', function (data) {
        var row = (typeof data === 'string') ? JSON.parse(data) : data;
        if (!row) return;

        if (mode === 'update') {
            $('#ajaxMode').val('update');
            $('#subcategory-id').val(row.subcategory_id);
            $('#sub-name').val(row.subcategory_name);
            $('#sub-waittime').val(row.wait_time_days);
            getEditor().val(row.papers || '');
            $('#field-section').show();
            $('#del-section').hide();
            $('#btn-submit').text(text_update);
        } else if (mode === 'delete') {
            $('#ajaxMode').val('delete');
            $('#subcategory-id').val(row.subcategory_id);
            $('#sub-name-del').text(row.subcategory_name);
            $('#sub-waittime-del').text(row.wait_time_days);
            $('#req-papers-del').html(row.papers || '');
            $('#field-section').hide();
            $('#del-section').show();
            $('#btn-submit').text(text_delete);
        }
    });
}

function hideForm() {
    $('#sub-modal').hide();
}

function SendSubCategory() {
    var mode = $('#ajaxMode').val();
    var payload;

    if (mode === 'delete') {
        payload = {ajaxMode: 'delete', 'subcategory-id': $('#subcategory-id').val()};
    } else {
        getEditor().updateOriginal();
        payload = $('#sub-cat-form').serialize();
    }

    $.post(AJAX_URL, payload, function (resp) {
        var result = (typeof resp === 'string') ? JSON.parse(resp) : resp;
        if (result && result.status) {
            hideForm();
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

function FlipReport(subID) {
    $.post(AJAX_URL, {ajaxMode: 'report', subcategory: subID}, function (data) {
        var val = parseInt(data, 10);
        if (val === 0 || val === 1) {
            $('#repo_' + subID).text(val === 1 ? text_yes : text_no);
        }
    });
}
