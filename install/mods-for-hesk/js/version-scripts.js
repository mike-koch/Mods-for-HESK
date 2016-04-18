function processUpdates(startingVersion) {
    if (startingVersion < 2) {
        startVersionUpgrade('p140');
        executeUpdate(2, 'p140', 'Pre 1.4.0');
    } else if (startingVersion < 3) {
        startVersionUpgrade('140');
        executeUpdate(3, '140', '1.4.0');
    } else if (startingVersion < 4) {
        startVersionUpgrade('141');
        executeUpdate(4, '141', '1.4.1');
    } else if (startingVersion < 5) {
        startVersionUpgrade('150');
        executeUpdate(5, '150', '1.5.0');
    } else if (startingVersion < 6) {
        startVersionUpgrade('160');
        executeUpdate(6, '160', '1.6.0');
    } else if (startingVersion < 7) {
        startVersionUpgrade('161');
        executeUpdate(7, '161', '1.6.1');
    } else if (startingVersion < 8) {
        startVersionUpgrade('170');
        executeUpdate(8, '170', '1.7.0');
    } else if (startingVersion < 9) {
        startVersionUpgrade('200');
        executeUpdate(9, '200', '2.0.0');
    } else if (startingVersion < 10) {
        startVersionUpgrade('201');
        executeUpdate(10, '201', '2.0.1');
    } else if (startingVersion < 11) {
        startVersionUpgrade('210');
        executeUpdate(11, '210', '2.1.0');
    } else if (startingVersion < 12) {
        startVersionUpgrade('211');
        executeUpdate(12, '211', '2.1.1');
    } else if (startingVersion < 13) {
        startVersionUpgrade('220');
        executeUpdate(13, '220', '2.2.0');
    } else if (startingVersion < 14) {
        startVersionUpgrade('221');
        executeUpdate(14, '221', '2.2.1');
    } else if (startingVersion < 15) {
        startVersionUpgrade('230');
        executeUpdate(15, '230', '2.3.0');
    } else if (startingVersion < 16) {
        startVersionUpgrade('231');
        executeUpdate(16, '231', '2.3.1');
    } else if (startingVersion < 17) {
        startVersionUpgrade('232');
        executeUpdate(17, '232', '2.3.2');
    } else if (startingVersion < 18) {
        startVersionUpgrade('240');
        executeUpdate(18, '240', '2.4.0');
    } else if (startingVersion < 19) {
        startVersionUpgrade('241');
        executeUpdate(19, '241', '2.4.1');
    } else if (startingVersion < 20) {
        startVersionUpgrade('242');
        executeUpdate(20, '242', '2.4.2');
    } else if (startingVersion < 21) {
        startVersionUpgrade('250');
        executeUpdate(21, '250', '2.5.0');
    } else if (startingVersion < 22) {
        startVersionUpgrade('251');
        executeUpdate(22, '251', '2.5.1');
    } else if (startingVersion < 23) {
        startVersionUpgrade('252');
        executeUpdate(23, '252', '2.5.2');
    } else if (startingVersion < 24) {
        startVersionUpgrade('253');
        executeUpdate(24, '253', '2.5.3');
    } else if (startingVersion < 25) {
        startVersionUpgrade('254');
        executeUpdate(25, '254', '2.5.4');
    } else if (startingVersion < 26) {
        startVersionUpgrade('255');
        executeUpdate(26, '255', '2.5.5');
    } else {
        installationFinished();
    }
}


function executeUpdate(version, cssclass, formattedVersion) {
    appendToInstallConsole('<tr><td><span class="label label-info">INFO</span></td><td>Starting updates for ' + formattedVersion + '</td></tr>');
    $.ajax({
        type: 'POST',
        url: 'ajax/install-database-ajax.php',
        data: {version: version},
        success: function (data) {
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