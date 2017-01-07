var availableUpdates = [
    createUpdate(2, 'p140', 'Pre 1.4.0'),
    createUpdate(3, '140', '1.4.0'),
    createUpdate(4, '141', '1.4.1'),
    createUpdate(5, '150', '1.5.0'),
    createUpdate(6, '160', '1.6.0'),
    createUpdate(7, '161', '1.6.1'),
    createUpdate(8, '170', '1.7.0'),
    createUpdate(9, '200', '2.0.0'),
    createUpdate(10, '201', '2.0.1'),
    createUpdate(11, '210', '2.1.0'),
    createUpdate(12, '211', '2.1.1'),
    createUpdate(13, '220', '2.2.0'),
    createUpdate(14, '221', '2.2.1'),
    createUpdate(15, '230', '2.3.0'),
    createUpdate(16, '231', '2.3.1'),
    createUpdate(17, '232', '2.3.2'),
    createUpdate(18, '240', '2.4.0'),
    createUpdate(19, '241', '2.4.1'),
    createUpdate(20, '242', '2.4.2'),
    createUpdate(21, '250', '2.5.0'),
    createUpdate(22, '251', '2.5.1'),
    createUpdate(23, '252', '2.5.2'),
    createUpdate(24, '253', '2.5.3'),
    createUpdate(25, '254', '2.5.4'),
    createUpdate(26, '255', '2.5.5'),
    createUpdate(27, '260', '2.6.0'),
    createUpdate(28, '261', '2.6.1'),
    createUpdate(29, '262', '2.6.2'),
    createUpdate(30, '263', '2.6.3'),
    createUpdate(31, '264', '2.6.4'),
    createUpdate(32, '300b1', '3.0.0 beta 1'),
    createUpdate(33, '300rc1', '3.0.0 RC 1'),
    createUpdate(34, '300', '3.0.0'),
    createUpdate(35, '301', '3.0.1'),
    createUpdate(36, '302', '3.0.2')
];

function createUpdate(buildNumber, cssClass, display) {
    return {
        buildNumber: buildNumber,
        cssClass: cssClass,
        display: display
    };
}

function processUpdates(startingVersion) {
    var ranInstall = false;
    $.each(availableUpdates, function() {
        if (startingVersion < this.buildNumber) {
            ranInstall = true;
            startVersionUpgrade(this.buildNumber, this.cssClass, this.display);
            executeUpdate(this.buildNumber, this.cssClass, this.display);
        }
    });

    if (!ranInstall) {
        installationFinished();
    }
}


function executeUpdate(version, cssclass, formattedVersion) {
    appendToInstallConsole('<tr><td><span class="label label-info">INFO</span></td><td>Starting updates for ' + formattedVersion + '</td></tr>');
    $.ajax({
        type: 'POST',
        url: 'ajax/install-database-ajax.php',
        data: {version: version},
        success: function () {
            markUpdateAsSuccess(cssclass, formattedVersion);
            if (version == 9) {
                migrateIpEmailBans('banmigrate', 'banmigrate');
            } else if (version == 18) {
                initializeStatuses('initialize-statuses', 'initialize-statuses');
            } else {
                processUpdates(version);
            }
        },
        error: function (data) {
            appendToInstallConsole('<tr><td><span class="label label-danger">ERROR</span></td><td>' + data.responseText + '</td></tr>');
            markUpdateAsFailure(cssclass);
        }
    });
}

function migrateIpEmailBans(version, cssclass) {
    startVersionUpgrade(version);
    appendToInstallConsole('<tr><td><span class="label label-info">INFO</span></td><td>Checking for IP / Email address bans to migrate</td></tr>');
    $.ajax({
        type: 'POST',
        url: 'ajax/task-ajax.php',
        data: {task: 'ip-email-bans'},
        success: function (data) {
            var parsedData = $.parseJSON(data);
            if (parsedData.status == 'ATTENTION') {
                appendToInstallConsole('<tr><td><span class="label label-warning">WARNING</span></td><td>Your response is needed. Please check above.</td></tr>');
                markUpdateAsAttention(version);
                prepareAttentionPanel(getContentForMigratePrompt(parsedData.users));
            } else {
                migrateComplete();
            }
        },
        error: function (data) {
            appendToInstallConsole('<tr><td><span class="label label-danger">ERROR</span></td><td>' + data.responseText + '</td></tr>');
            markUpdateAsFailure(version);
        }
    });
}

function initializeStatuses(version, cssclass) {
    startVersionUpgrade(version);
    appendToInstallConsole('<tr><td><span class="label label-info">INFO</span></td><td>Initializing Statuses</td></tr>');
    $.ajax({
        type: 'POST',
        url: 'ajax/task-ajax.php',
        data: {task: 'initialize-statuses'},
        success: function (data) {
            markUpdateAsSuccess(cssclass, 'Initializing Statuses');
            statusesInitialized();
        },
        error: function (data) {
            appendToInstallConsole('<tr><td><span class="label label-danger">ERROR</span></td><td>' + data.responseText + '</td></tr>');
            markUpdateAsFailure(version);
        }
    });
}

function statusesInitialized() {
    processUpdates(18);
}


function runMigration() {
    // Get user ID that is selected
    var userId = $('#user-dropdown').val();
    // Hide the div, switch back to in progress
    $('#attention-row').hide();
    startVersionUpgrade('banmigrate');
    $.ajax({
        type: 'POST',
        url: 'ajax/task-ajax.php',
        data: {task: 'migrate-bans', user: userId},
        success: function (data) {
            migrateComplete();
        },
        error: function (data) {
            appendToInstallConsole('ERROR: ' + data.responseText);
            markUpdateAsFailure('banmigrate');
        }
    })
}

function migrateComplete() {
    $('#attention-row').hide();
    markUpdateAsSuccess('banmigrate', 'IP and Email address bans');
    processUpdates(9);
}

jQuery(document).ready(loadJquery);