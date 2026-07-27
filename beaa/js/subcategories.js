$(document).ready(function () {
    createEditor();
});

var textarea = document.getElementById('req-papers');
var divDel = document.getElementById('req-papers-del');

function createEditor() {

    sceditor.create(textarea, {
        plugins: 'xhtml, undo, plaintext',
        autoUpdate: true,
        width: '100%',
        height: '100%',
        resizeHeight: false,
        resizeWidth: false,
        style: '../js/minified/themes/content/default.min.css',
        emoticonsEnabled: false,
        toolbarExclude: "emoticon,print,youtube"
    });

    sceditor.create(divDel, {
        plugins: 'xhtml, undo',
        resizeHeight: false,
        resizeWidth: false,
        readOnly: true,
        toolbar: '',
        style: '../js/minified/themes/content/default.min.css',
        emoticonsEnabled: false,
        toolbarExclude: "emoticon,print,youtube"
    });
}

function destroyEditor() {
    var instance = $('#req-papers').sceditor('instance');
    instance.destroy();
}

function showForm(mode, id = 0) {

    $("#ajaxMode").val(mode);

    switch (mode) {
        case 'add':
            emptyForm();

            $("#btn-submit").text(text_add);
            $("#del-section").hide();
            $("#field-section").show();

            break;
        case 'update':

            updateFormFields(id, mode);

            $("#btn-submit").text(text_update);
            $("#del-section").hide();
            $("#field-section").show();

            break;
        case 'delete':

            updateFormFields(id, mode);

            $("#btn-submit").text(text_delete);
            $("#del-section").show();
            $("#field-section").hide();
            break;
    }

    clearTableHighlight();
    $("#btn-add").hide();
    $("#sub-modal").fadeIn('fast');
}

function clearTableHighlight() {
    $("#sub-cat-list tr").removeClass("row-highlited");
}

function updateFormFields(id, mode) {

//    console.log("update fields");

    $.ajax({
        type: 'get',
        dataType: 'json',
        cache: false,
        url: "./views/subcategories/process.php",
        data: {subcategory: id, ajaxMode: 'get'},
        success: function (response, textStatus, jqXHR) {
//            console.log(response);
            if (response) {

                $('input[name=subcategory-id]').val(response.subcategory_id);
                $('input[name=id]').val(response.main_category_id);

                $("#row-" + id).addClass("row-highlited");

                if (mode == 'update') {
//                    alert("update");
                    $('input[name=sub-name]').val(response.subcategory_name);
                    $('input[name=sub-waittime]').val(response.wait_time_days);
                    var instance = $('#req-papers').sceditor('instance');
//                    instance.val("");
                    $('#req-papers').val(response.papers);
                    instance.val(response.papers);

                } else {
                    $("#sub-name-del").text(response.subcategory_name);
                    $("#sub-waittime-del").text(response.wait_time_days);
                    var instance2 = $('#req-papers-del').sceditor('instance');
//                    instance2.val("");
                    $("#req-papers-del").text(response.papers);
                    instance2.val(response.papers);
                }

            } else {

            }
//            hideForm();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            alert('Error - ' + errorThrown);
        }
    });
}

function SendSubCategory(id) {
    var values = $("#" + id).serialize();
    var mode = $("#ajaxMode").val();

//    console.log(mode);

    $.ajax({
        url: "./views/subcategories/process.php",
        type: "post",
        data: values,
        dataType: 'json',
        success: function (response) {
//            console.log(response);
            if (response) {
                var errors = response.errors;
                var status = response.status;

                if (status) {
                    //emptyForm();
                    hideForm();
                    updateRow(response.data, mode);
//                    console.log(response.data);

                } else {
                    var errTXT = "";
                    for (var i = 0; i < errors.length; i++) {
                        errTXT += "<li class = 'error-item'>" + errors[i] + "</li>";
                    }

                    $("#ajax-error-list").html(errTXT);
                    $("#ajax-error").fadeIn();
                }
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
//            console.log(textStatus, errorThrown);
        }
    });
}

function updateRow(data, mode) {

    var subId = parseInt(data.subcategory_id);
    var total = parseInt($("#sub-size").text());

    if (mode == 'delete') {

        $("#row-" + subId).replaceWith("");

        $("#sub-size").text(total - 1);
    } else {

        var row = "<tr id='row-" + subId + "' class='border-btm border-gray'>" +
                "<td>" + data.subcategory_name + "</td>" +
                "<td>" + data.wait_time_days + "</td>" +
                "<td><button onclick='showForm(&#39;update&#39;," + subId + ");' type='button' class='btn btn-success btn-xs no-marg marg-h-5'>" + text_edit + "</button>" +
                "  | <button onclick='showForm(&#39;delete&#39;," + subId + ");' type='button' class='btn btn-danger btn-xs no-marg marg-h-5'>" + text_delete + "</button></td></tr>";

        if (mode == 'add') {

            if (total == 0) {
                $(".empty-invert").replaceWith("");
            }

            $("#sub-size").text(total + 1);
            $("#sub-cat-list .table").prepend(row).fadeIn();

        } else {
            $("#row-" + subId).replaceWith(row).fadeIn();
        }
    }
}

function GetSubCategoryById(id) {

    var result;
    $.ajax({
        type: 'get',
        dataType: 'json',
        cache: false,
        url: "./views/subcategories/process.php",
        data: {subcategory: id, ajaxMode: 'get'},
        success: function (response, textStatus, jqXHR) {
//            console.log(response);
            if (response) {

                result = response;
//                console.log(result);
            } else {
                result = 0;
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            // alert('Error - ' + errorThrown);
        }
    });

    return result;
}

function GetLastSubCategory() {
    $.ajax({
        type: 'get',
        dataType: 'json',
        cache: false,
        url: "./views/subcategories/process.php",
        data: {},
        success: function (response, textStatus, jqXHR) {
//            console.log(response);
            if (response) {

            } else {
//                alert("error! please check connection.");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            alert('Error - ' + errorThrown);
        }
    });
}

function hideForm() {
    $("#sub-modal").fadeOut('fast');
    $("#btn-add").fadeIn();
    clearTableHighlight();
    emptyForm();
}

function emptyForm() {
    $(".form-ok").val("");
    $("#ajax-error-list").html("");
    $("#ajax-error").hide();
}

function FlipReport(id) {

    if (!id) {
        return;
    }
    $.ajax({
        type: 'post',
        dataType: 'json',
        cache: false,
        url: "./views/subcategories/process.php",
        data: {subcategory: id, ajaxMode: 'report'},
        success: function (response, textStatus, jqXHR) {
//            console.log(response);
            if (response >= 0) {
                
                flipReportBtn(id, response);
//                console.log("id: " + id + ", active:" + response);
            } 
        },
        error: function (jqXHR, textStatus, errorThrown) {
        }
    });
}

function flipReportBtn(id, status) {

    var elemante = "#repo_" + id;

    if (status > 0) {
        $(elemante).text(text_yes);
    } else {
        $(elemante).text(text_no);
    }
}