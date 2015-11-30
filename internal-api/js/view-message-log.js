$(document).ready(function() {
    // We should show the latest 50 logs when the user first views the page.
    var endpoint = getHelpdeskUrl();
    endpoint += '/internal-api/admin/message-log/';
    $.ajax({
        url: endpoint,
        data: {
            location: null,
            fromDate: null,
            toDate: null,
            severityId: null
        },
        method: 'POST',
        success: function(data) {
            console.log(data);
        },
        error: function(data) {
            console.error(data);
        }
    })
})