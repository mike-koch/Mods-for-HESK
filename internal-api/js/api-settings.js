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
    $('#public-api-saving').removeClass('hide');
    $('#public-api-success').addClass('hide');
    $('#public-api-failure').addClass('hide');
    $.ajax({
        url: endpoint,
        data: data,
        method: 'POST',
        success: function() {
            $('#enable-api-button').removeClass('disabled');
            $('#disable-api-button').removeClass('disabled');
            $('#public-api-saving').addClass('hide');
            $('#public-api-success').removeClass('hide');

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
            $('#public-api-saving').addClass('hide');
            $('#public-api-failure').removeClass('hide');
        }
    });
}

function generateToken(userId) {
    alert(userId);
}

function clearTokens(userId) {
    alert(userId);
}