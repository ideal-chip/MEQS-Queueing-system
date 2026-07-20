// iDEAL-Q — admin more-papers (uploaded documents) list: delete a file

function deleteFile(fileName) {
    if (!confirm(lang_delete_question)) return;
    $.get('./views/morepapers/delete.php?fn=' + encodeURIComponent(fileName), function () {
        location.reload();
    });
}
