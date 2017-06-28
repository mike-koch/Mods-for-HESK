$(document).ready(function() {
    $('#enable-api-button').click(function() {
        updatePublicApi('1');
    });
    $('#disable-api-button').click(function() {
        updatePublicApi('0');
    });

    $('#enable-url-rewrite-button').click(function() {
        updateUrlRewrite('1');
    });
    $('#disable-url-rewrite-button').click(function() {
        updateUrlRewrite('0');
    });
});

function updatePublicApi(enable) {
    var heskPath = $('p#hesk-path').text();
    var endpoint = heskPath + 'internal-api/admin/api-settings/';
    var data = {
        key: 'public_api',
        value: enable
    };
    $('#enable-api-button').addClass('disabled');
    $('#disable-api-button').addClass('disabled');
    $.ajax({
        url: endpoint,
        data: data,
        method: 'POST',
        success: function() {
            mfhAlert.success(mfhLang.text('api_settings_saved'), mfhLang.text('success'));
            $('#enable-api-button').removeClass('disabled');
            $('#disable-api-button').removeClass('disabled');
        },
        error: function(data) {
            console.error(data);
            $('#enable-api-button').removeClass('disabled');
            $('#disable-api-button').removeClass('disabled');
            mfhAlert.error(mfhLang.text('an_error_occurred'), mfhLang.text('error'));
        }
    });
}

function updateUrlRewrite(enable) {
    var heskPath = $('p#hesk-path').text();
    var endpoint = heskPath + 'internal-api/admin/api-settings/';
    var data = {
        key: 'api_url_rewrite',
        value: enable
    };
    $('#enable-url-rewrite-button').addClass('disabled');
    $('#disable-url-rewrite-button').addClass('disabled');
    $.ajax({
        url: endpoint,
        data: data,
        method: 'POST',
        success: function() {
            mfhAlert.success(mfhLang.text('url_rewrite_saved'), mfhLang.text('success'));
            $('#enable-url-rewrite-button').removeClass('disabled');
            $('#disable-url-rewrite-button').removeClass('disabled');
        },
        error: function(data) {
            console.error(data);
            $('#enable-url-rewrite-button').removeClass('disabled');
            $('#disable-url-rewrite-button').removeClass('disabled');
            mfhAlert.error(mfhLang.text('an_error_occurred'), mfhLang.text('error'));
        }
    });
}

function generateToken(userId) {
    var heskPath = $('p#hesk-path').text();
    var endpoint = heskPath + 'internal-api/admin/api-authentication/';
    markSaving('token-' + userId);
    $('#token-' + userId + '-reset').addClass('hide');
    $('#token-' + userId + '-created').addClass('hide');
    var data = {
        userId: userId,
        action: 'generate'
    };
    $.ajax({
        url: endpoint,
        data: data,
        method: 'POST',
        success: function (data) {
            $('#token-' + userId + '-created > td > .token').text(data);
            $('#token-' + userId + '-created').removeClass('hide');
            markSuccess('token-' + userId);
            var oldNumberOfTokens = parseInt($('#token-' + userId + '-count').text());
            $('#token-' + userId + '-count').text(++oldNumberOfTokens);
        },
        error: function (data) {
            console.error(data);
            markFailure('token-' + userId);
        }
    });
}

function markSuccess(id) {
    $('#' + id + '-saving').addClass('hide');
    $('#' + id + '-failure').addClass('hide');
    $('#' + id + '-success').removeClass('hide');
}

function markSaving(id) {
    $('#' + id + '-saving').removeClass('hide');
    $('#' + id + '-failure').addClass('hide');
    $('#' + id + '-success').addClass('hide');
}

function markFailure(id) {
    $('#' + id + '-saving').addClass('hide');
    $('#' + id + '-failure').removeClass('hide');
    $('#' + id + '-success').addClass('hide');
}

function clearTokens(userId) {
    var heskPath = $('p#hesk-path').text();
    var endpoint = heskPath + 'internal-api/admin/api-authentication/';
    markSaving('token-' + userId);
    $('#token-' + userId + '-reset').addClass('hide');
    $('#token-' + userId + '-created').addClass('hide');
    var data = {
        userId: userId,
        action: 'reset'
    };
    $.ajax({
        url: endpoint,
        data: data,
        method: 'POST',
        success: function() {
            $('#token-' + userId + '-reset').removeClass('hide');
            $('#token-' + userId + '-count').text('0');
            markSuccess('token-' + userId);
        },
        error: function(data) {
            console.error(data);
            markFailure('token-' + userId);
        }
    });
}