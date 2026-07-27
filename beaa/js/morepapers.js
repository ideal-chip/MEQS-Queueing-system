
function deleteFile(fn)
{
    if (confirm(lang_delete_question))
    {
        $.ajax({
            type: 'get',
            dataType: 'json',
            cache: false,
            url: '../admin/views/morepapers/delete.php',
            data: {fn: fn},
            success: function (response, textStatus, jqXHR) {
                
                console.log(response);
                if (response == 'OK') {
                    location.reload();
                } else {
                    alert(response);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                //alert('Error - ' + errorThrown);
            }
        });
    }
}

