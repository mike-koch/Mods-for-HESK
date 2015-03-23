function processUpdates(startingVersion) {
    if (startingVersion < 1) {
        startVersionUpgrade('p140');
        executeUpdate(1, 'p140', 'Pre 1.4.0');
    } else if (startingVersion < 140) {
        startVersionUpgrade('140');
        executeUpdate(140, '140', '1.4.0');
    } else if (startingVersion < 141) {
        startVersionUpgrade('141');
        executeUpdate(141, '141', '1.4.1');
    } else if (startingVersion < 150) {
        startVersionUpgrade('150');
        executeUpdate(150, '150', '1.5.0');
    } else if (startingVersion < 160) {
        startVersionUpgrade('160');
        executeUpdate(160, '160', '1.6.0');
    } else if (startingVersion < 161) {
        startVersionUpgrade('161');
        executeUpdate(161, '161', '1.6.1');
    } else if (startingVersion < 170) {
        startVersionUpgrade('170');
        executeUpdate(170, '170', '1.7.0');
    } else if (startingVersion < 200) {
        startVersionUpgrade('200');
        executeUpdate(200, '200', '2.0.0');
    } else if (startingVersion < 201) {
        startVersionUpgrade('201');
        executeUpdate(201, '201', '2.0.1');
    } else if (startingVersion < 210) {
        startVersionUpgrade('210');
        executeUpdate(210, '210', '2.1.0');
    } else if (startingVersion < 211) {
        startVersionUpgrade('211');
        executeUpdate(211, '211', '2.1.1');
    } else {
        installationFinished();
    }
}


function executeUpdate(version, cssclass, formattedVersion) {
    appendToInstallConsole('<tr><td><span class="label label-info">INFO</span></td><td>Starting updates for ' + formattedVersion + '</td></tr>');
    $.ajax({
        type: 'POST',
        url: 'ajax/install-database-ajax.php',
        data: { version: version },
        success: function(data) {
            markUpdateAsSuccess(cssclass, formattedVersion);
            if (version == 200) {
                migrateIpEmailBans('banmigrate', cssclass);
            } else {
                processUpdates(version);
            }
        },
        error: function(data) {
            appendToInstallConsole('<tr><td><span class="label label-danger">ERROR</span></td><td>'+ data.responseText + '</td></tr>');
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
        data: { task: 'ip-email-bans' },
        success: function(data) {
            var parsedData = $.parseJSON(data);
            console.info(parsedData);
            if (parsedData.status == 'ATTENTION') {
                appendToInstallConsole('<tr><td><span class="label label-warning">WARNING</span></td><td>Your response is needed. Please check above.</td></tr>');
                markUpdateAsAttention(version);
                prepareAttentionPanel(getContentForMigratePrompt(parsedData.users));
            } else {
                migrateComplete();
            }
        },
        error: function(data) {
            appendToInstallConsole('<tr><td><span class="label label-danger">ERROR</span></td><td>' + data.responseText + '</td></tr>');
            markUpdateAsFailure(cssclass);
        }
    });
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
        data: { task: 'migrate-bans', user: userId },
        success: function(data) {
            migrateComplete();
        },
        error: function(data) {
            appendToInstallConsole('ERROR: ' + data.responseText);
            markUpdateAsFailure('banmigrate');
        }
    })
}

function migrateComplete() {
    $('#attention-row').hide();
    markUpdateAsSuccess('banmigrate', 'IP and Email address bans');
    processUpdates(200);
}

jQuery(document).ready(loadJquery);