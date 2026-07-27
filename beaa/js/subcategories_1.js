
function SendSubCategory(id) {
    var values = $("#" + id).serialize();

//    var postForm = {//Fetch form data
//        'sub-name': $('input[name=sub-name]').val(),
//        'sub-waittime': $('input[name=sub-waittime]').val(),
//        'id': $('input[name=id]').val(),
//        'subcategory-id': $('input[name=subcategory-id]').val(),
//        'ajax-mode': $('input[name=ajax-mode]').val(),
//        
//    };
    $.ajax({
        url: "./categoreis/subCategoresForm.php",
        type: "post",
        data: values,
        dataType: 'json',
        success: function (response) {
//            console.log(response);
            $("#result").html(response);
            if (response) {
                var errors = response.errors;
                var status = response.status;

                if (status) {
//                    alert("OK");
                    $(".form-ok").val("");
                    $("#ajax-error-list").html("");
                    $("#ajax-error").hide();
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
            console.log(textStatus, errorThrown);
        }
    });
}

function AddSubCategory() {

}

function SaveSubCategory() {
}

