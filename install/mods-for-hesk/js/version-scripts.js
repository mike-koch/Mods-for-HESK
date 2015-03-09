function processUpdates(startingVersion) {
    if (startingVersion < 1) {
        startVersionUpgrade('p140');
        executeUpdate(1, 'p140');
    } else if (startingVersion < 140) {
        startVersionUpgrade('140');
        executeUpdate(140, '140');
    } else if (startingVersion < 141) {
        startVersionUpgrade('141');
        executeUpdate(141, '141');
    } else if (startingVersion < 150) {
        startVersionUpgrade('150');
        executeUpdate(150, '150');
    } else if (startingVersion < 160) {
        startVersionUpgrade('160');
        executeUpdate(160, '160');
    } else if (startingVersion < 161) {
        startVersionUpgrade('161');
        executeUpdate(161, '161');
    } else if (startingVersion < 170) {
        startVersionUpgrade('170');
        executeUpdate(170, '170');
    } else if (startingVersion < 200) {
        startVersionUpgrade('200');
        executeUpdate(200, '200');
    } else if (startingVersion < 201) {
        startVersionUpgrade('201');
        executeUpdate(201, '201');
    } else {
        installationFinished();
    }
}


function executeUpdate(version, cssclass) {
    $.ajax({
        type: 'POST',
        url: 'ajax/database-ajax.php',
        data: { version: version },
        success: function(data) {

            markUpdateAsSuccess(cssclass);
            if (version == 200) {
                migrateIpEmailBans('banmigrate', cssclass);
            }
            processUpdates(version);
        },
        error: function(data) {
            appendToInstallConsole('ERROR: ' + data.responseText);
            markUpdateAsFailure(cssclass);
        }
    });
}

function migrateIpEmailBans(version, cssclass) {
    startVersionUpgrade(version);
    $.ajax({
        type: 'POST',
        url: 'ajax/task-ajax.php',
        data: { task: 'ip-email-bans' },
        success: function(data) {
            var parsedData = $.parseJSON(data);
            console.info(parsedData);
            if (parsedData.status == 'ATTENTION') {
                markUpdateAsAttention(version);
                prepareAttentionPanel(getContentForMigratePrompt(parsedData.users));
            } else {
                migrateComplete();
            }
        },
        error: function(data) {
            appendToInstallConsole('ERROR: ' + data.responseText);
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
    markUpdateAsSuccess('banmigrate');
    processUpdates(200);
}

jQuery(document).ready(loadJquery);