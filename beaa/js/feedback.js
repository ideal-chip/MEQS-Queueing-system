// iDEAL-Q — feedback kiosk screen
// Turns the fb0..fbN <select> rating fields into star widgets (jquery.barrating,
// "fontawesome-stars" theme), validates that every question was rated, shows the
// confirmation modal with the computed score, and posts the rating to the
// backend (../api/feedback/set.php) on confirm.

var feedbackReady = false;

$(document).ready(function () {
    var $stars = $('select[id^="stars"]');

    $stars.each(function () {
        var $select = $(this);
        var index = parseInt($select.attr('id').replace('stars', ''), 10);
        var $val = $('#s' + (index + 1));

        $select.barrating({
            theme: 'fontawesome-stars',
            onSelect: function (value) {
                $val.text(value ? value + '/5' : '');
            }
        });
    });

    feedbackReady = true;

    $('#show_fb').on('click', function () {
        showFeedbackModal();
    });
});

function getRatings() {
    var ratings = [];
    $('select[id^="stars"]').each(function () {
        var v = parseInt($(this).val(), 10);
        ratings.push(isNaN(v) ? 0 : v);
    });
    return ratings;
}

function showFeedbackModal() {
    var ratings = getRatings();
    var allRated = ratings.length > 0 && ratings.every(function (v) { return v > 0; });

    if (allRated) {
        var sum = ratings.reduce(function (a, b) { return a + b; }, 0);
        var avg = sum / ratings.length;
        $('#final-score').text(avg.toFixed(1) + '/5').show();
        $('#final-note').hide();
    } else {
        $('#final-score').text('0/5').show();
        $('#final-note').show();
    }

    window.feedbackAllRated = allRated;
    $('#feedback-modal').modal('show');
}

function sendFeedback() {
    if (!window.feedbackAllRated) {
        return;
    }

    var ratings = getRatings();
    var data = { lang: currentLang };
    ratings.forEach(function (v, i) {
        data['fb' + i] = v;
    });
    if (typeof feedbackCounterId !== 'undefined' && feedbackCounterId) {
        data.counter_id = feedbackCounterId;
    }

    $.post('../api/feedback/set.php', data, function (resp) {
        if (String(resp).trim() === '1') {
            $('#feedback-main').hide();
            $('#feedback-note').removeClass('animated fadeIn').show()
                .addClass('animated fadeIn');
        }
    });
}

function clearRating() {
    $('select[id^="stars"]').each(function () {
        $(this).barrating('clear');
        var index = parseInt($(this).attr('id').replace('stars', ''), 10);
        $('#s' + (index + 1)).text('');
    });
    window.feedbackAllRated = false;
}

function changeLang(lang) {
    var url = new URL(window.location.href);
    url.searchParams.set('language', lang);
    window.location.href = url.toString();
}
