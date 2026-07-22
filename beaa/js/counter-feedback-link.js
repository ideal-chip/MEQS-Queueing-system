function copyFeedbackLink(url, button) {
    function copied() {
        if (!button) return;
        var original = button.innerHTML;
        button.innerHTML = '<i class="glyphicon glyphicon-ok"></i>';
        setTimeout(function () { button.innerHTML = original; }, 1200);
    }

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(copied, function () {
            fallbackCopyFeedbackLink(url, copied);
        });
    } else {
        fallbackCopyFeedbackLink(url, copied);
    }
}

function fallbackCopyFeedbackLink(url, callback) {
    var field = document.createElement('textarea');
    field.value = url;
    field.style.position = 'fixed';
    field.style.opacity = '0';
    document.body.appendChild(field);
    field.select();
    try { document.execCommand('copy'); } catch (ignore) {}
    document.body.removeChild(field);
    callback();
}
