function processUninstallation() {
    var tasks = ['status-change', 'autorefresh', 'parent-child', 'settings-access', 'activate-user',
        'notify-note-unassigned', 'user-manage-notification-settings', 'settings-table', 'verified-emails-table',
        'pending-verification-emails-table', 'pending-verification-tickets-table'];
    //-- Change status column to default HESK values
    tasks.forEach(function(task) {
        startUninstallation(task);
        executeUninstallation(task);
    });
}

function startUninstallation(task) {
    appendToInstallConsole('<tr><td><span class="label label-info">INFO</span></td><td>Starting task code: ' + task + '</td></tr>');
    $.ajax({
        type: 'POST',
        url: 'ajax/install-database-ajax.php',
        data: { task: task },
        success: function(data) {
            markUninstallAsSuccess(cssclass, formattedVersion);
        },
        error: function(data) {
            appendToInstallConsole('<tr><td><span class="label label-danger">ERROR</span></td><td>'+ data.responseText + '</td></tr>');
            markUninstallAsFailure(cssclass);
        }
    });
}

jQuery(document).ready(loadJquery);