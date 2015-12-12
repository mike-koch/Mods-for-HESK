$(document).ready(function() {
    $('#enable-api-button').click(function() {
        updatePublicApi('1', '#enable-api-button');
    });
    $('#disable-api-button').click(function() {
        updatePublicApi('0', '#disable-api-button');
    });
});

function updatePublicApi(enable) {
    var endpoint = getHelpdeskUrl();
    endpoint += '/internal-api/admin/api-settings/';
    var data = {
        key: 'public_api',
        value: enable
    };
    $('#enable-api-button').addClass('disabled');
    $('#disable-api-button').addClass('disabled');
    markSaving('public-api');
    $.ajax({
        url: endpoint,
        data: data,
        method: 'POST',
        success: function() {
            $('#enable-api-button').removeClass('disabled');
            $('#disable-api-button').removeClass('disabled');
            markSuccess('public-api');

            if (enable == '1') {
                $('#public-api-sidebar').addClass('success')
                    .removeClass('danger');
                $('#public-api-sidebar-enabled').removeClass('hide');
                $('#public-api-sidebar-disabled').addClass('hide');
            } else {
                $('#public-api-sidebar').addClass('danger')
                    .removeClass('success');
                $('#public-api-sidebar-disabled').removeClass('hide');
                $('#public-api-sidebar-enabled').addClass('hide');
            }
        },
        error: function(data) {
            console.error(data);
            $('#enable-api-button').removeClass('disabled');
            $('#disable-api-button').removeClass('disabled');
            markFailure('public-api');
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

function generateToken(userId) {
    var endpoint = getHelpdeskUrl();
    endpoint += '/internal-api/admin/api-authentication/';
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

function clearTokens(userId) {
    var endpoint = getHelpdeskUrl();
    endpoint += '/internal-api/admin/api-authentication/';
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