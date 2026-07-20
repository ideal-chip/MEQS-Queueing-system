// iDEAL-Q — admin audios page: short-beep on/off toggle
// (add/edit/delete already work via plain links + confirm() inline in list.php)

$(document).ready(function () {
    $('#short-beep').on('click', function () {
        var $btn = $(this);
        var newValue = $btn.val() == 1 ? 0 : 1;
        $.get('../api/update.php?id=1&type=shortaudio&value=' + newValue, function (data) {
            if (data !== 0 && data !== '0') {
                $btn.val(newValue);
                if (newValue == 1) {
                    $btn.removeClass('btn-danger').addClass('btn-success').text(lang_active);
                } else {
                    $btn.removeClass('btn-success').addClass('btn-danger').text(lang_inactive);
                }
            }
        });
    });
});
