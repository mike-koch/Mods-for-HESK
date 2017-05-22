var mfhAlert = {
    success: success,
    error: error,
    errorWithLog: errorWithLog
};

function success(message, title) {
    if (title === undefined) {
        title = $('#lang_alert_success').text();
    }

    toastr.success(message, title);
}

function error(message, title) {
    if (title === undefined) {
        title = $('#lang_alert_error').text();
    }

    toastr.error(message, title);
}

function errorWithLog(message, responseJSON, title) {
    var displayMessage = message;
    if (responseJSON !== undefined &&
        responseJSON.logId !== undefined) {
        displayMessage += ' (' + responseJSON.logId + ')';
    }

    mfhAlert.error(displayMessage, title);
}