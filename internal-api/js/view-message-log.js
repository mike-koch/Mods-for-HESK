$(document).ready(function() {
    // We should show the latest 50 logs when the user first views the page.
    searchLogs(null, null, null, null);

    $('#search-button').click(function() {
        var location = getNullableField($('input[name="location"]').val());
        var dateFrom = getNullableField($('input[name="from-date"]').val());
        var dateTo = getNullableField($('input[name="to-date"]').val());
        var severity = $('select[name="severity"]').val();
        if (severity == -1) {
            severity = null;
        }

        searchLogs(location, dateFrom, dateTo, severity);
    });
});

function getNullableField(value) {
    return value !== "" ? value : null;
}

function searchLogs(location, fromDate, toDate, severity) {
    var endpoint = getHelpdeskUrl();
    endpoint += '/internal-api/admin/message-log/';

    $.ajax({
        url: endpoint,
        data: {
            location: location,
            fromDate: fromDate,
            toDate: toDate,
            severityId: severity
        },
        method: 'POST',
        success: displayResults,
        error: function(data) {
            console.error(data);
        }
    });
}

function displayResults(data) {
    data = $.parseJSON(data);
    var table = $('#results-table > tbody');
    table.empty();

    if (data.length === 0) {
        table.append('<tr><td colspan="4">No results found</td></tr>');
    } else {
        for (var index in data) {
            var result = data[index];
            table.append('<tr ' + getRowColor(result) + '>' +
                '<td>' + result.timestamp + '</td>' +
                '<td>' + result.username + '</td>' +
                '<td>' + result.location + '</td>' +
                '<td>' + result.message + '</td>');
        }
    }
}

function getRowColor(result) {
    switch (result.severity) {
        case "1":
            return 'class="info"';
        case "2":
            return 'class="warning"';
        case "3":
            return 'class="danger"';
    }

    return '';
}