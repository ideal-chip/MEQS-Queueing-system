// iDEAL-Q Common JavaScript Functions

// Copy a counter-feedback link to the clipboard, with a brief button flash
// as feedback (used on admin/counters.php and its edit form).
function copyFeedbackLink(url, btnEl) {
    var done = function () {
        if (!btnEl) return;
        var original = btnEl.innerHTML;
        btnEl.innerHTML = '<i class="glyphicon glyphicon-ok"></i>';
        setTimeout(function () { btnEl.innerHTML = original; }, 1200);
    };
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(done).catch(function () { fallbackCopy(url, done); });
    } else {
        fallbackCopy(url, done);
    }
}

function fallbackCopy(text, cb) {
    var tmp = document.createElement('textarea');
    tmp.value = text;
    tmp.style.position = 'fixed';
    tmp.style.opacity = '0';
    document.body.appendChild(tmp);
    tmp.select();
    try { document.execCommand('copy'); } catch (e) {}
    document.body.removeChild(tmp);
    if (cb) cb();
}

// Get current date
function getDate() {
    var today = new Date();
    var dd = String(today.getDate()).padStart(2, '0');
    var mm = String(today.getMonth() + 1).padStart(2, '0');
    var yyyy = today.getFullYear();
    return dd + '/' + mm + '/' + yyyy;
}

// Get current time
function getTime() {
    var today = new Date();
    var h = String(today.getHours()).padStart(2, '0');
    var m = String(today.getMinutes()).padStart(2, '0');
    var s = String(today.getSeconds()).padStart(2, '0');
    return h + ':' + m + ':' + s;
}

// Get current date and time
function getDateTime() {
    return getDate() + ' ' + getTime();
}

// Format number with leading zeros
function pad(num, size) {
    var s = num + "";
    while (s.length < size) s = "0" + s;
    return s;
}

// Show loading indicator
function showLoading() {
    if ($('#loading-indicator').length === 0) {
        $('body').append('<div id="loading-indicator" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;display:flex;align-items:center;justify-content:center;"><div style="background:white;padding:20px;border-radius:5px;"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Loading...</div></div>');
    } else {
        $('#loading-indicator').show();
    }
}

// Hide loading indicator
function hideLoading() {
    $('#loading-indicator').hide();
}

// Show alert message
function showAlert(message, type) {
    type = type || 'info';
    var alertClass = 'alert-' + type;
    var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible" role="alert">' +
        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
        '<span aria-hidden="true">&times;</span></button>' +
        message + '</div>';
    
    if ($('#alert-container').length === 0) {
        $('body').prepend('<div id="alert-container" style="position:fixed;top:10px;right:10px;z-index:9998;max-width:400px;"></div>');
    }
    
    $('#alert-container').append(alertHtml);
    
    setTimeout(function() {
        $('#alert-container .alert:first').fadeOut(function() {
            $(this).remove();
        });
    }, 5000);
}

// Confirm dialog
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Format currency
function formatCurrency(amount) {
    return parseFloat(amount).toFixed(2);
}

// AJAX error handler
function handleAjaxError(xhr, status, error) {
    console.error('AJAX Error:', status, error);
    showAlert('An error occurred: ' + error, 'danger');
    hideLoading();
}

// Initialize tooltips
function initTooltips() {
    if (typeof $().tooltip === 'function') {
        $('[data-toggle="tooltip"]').tooltip();
    }
}

// Initialize popovers
function initPopovers() {
    if (typeof $().popover === 'function') {
        $('[data-toggle="popover"]').popover();
    }
}

// Document ready
$(document).ready(function() {
    // Initialize Bootstrap components
    initTooltips();
    initPopovers();
    
    // Add active class to current menu item
    var currentPage = window.location.pathname.split("/").pop();
    $('.navbar-nav li a').each(function() {
        var href = $(this).attr('href');
        if (href && href.indexOf(currentPage) !== -1) {
            $(this).parent('li').addClass('active');
        }
    });
    
    // Handle form validation
    $('form[data-validate="true"]').submit(function(e) {
        var isValid = true;
        $(this).find('input[required], select[required], textarea[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).closest('.form-group').addClass('has-error');
            } else {
                $(this).closest('.form-group').removeClass('has-error');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            showAlert('Please fill in all required fields', 'warning');
        }
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
});
