//-- Activate anything Hesk UI needs, such as tooltips.
var loadJquery = function()
{
    //-- Activate tooltips
    $('[data-toggle="tooltip"]').tooltip({
        container: 'body'
    });

    //-- Activate popovers
    $('[data-toggle="popover"]').popover({
        trigger: 'hover',
        container: 'body'
    });

    //-- Activate HTML popovers
    $('[data-toggle="htmlpopover"]').popover({
        trigger: 'hover',
        container: 'body',
        html: 'true'
    });

    //-- Activate jQuery's date picker
    $(function() {
        $('.datepicker').datepicker({
            todayBtn: "linked",
            clearBtn: true,
            autoclose: true,
            todayHighlight: true,
            format: "yyyy-mm-dd"
        });
    });
};

function selectAll(id) {
    $('#' + id + ' option').prop('selected', true);
}

function deselectAll(id) {
    $('#' + id + ' option').prop('selected', false);
}

function toggleRow(id) {
    if ($('#' + id).hasClass('danger'))
    {
        $('#' + id).removeClass('danger');
    } else
    {
        $('#' + id).addClass('danger');
    }
}

function toggleChildrenForm(show) {
    if (show) {
        $('#childrenForm').show();
        $('#addChildText').hide();
    } else {
        $('#childrenForm').hide();
        $('#addChildText').show();
    }
}

function toggleContainers(showIds, hideIds) {
    showIds.forEach(function (entry) {
        $('#' + entry).show();
    });
    hideIds.forEach(function (entry) {
        $('#' + entry).hide();
    });
}

function disableAllDisablable(exclusion) {
    $('.disablable').attr('disabled', 'disabled');
    $('#'+exclusion).removeAttr('disabled');
}

function enableAllDisablable() {
    $('.disablable').removeAttr('disabled');
    $('#updateText').hide();
}

function startVersionUpgrade(version) {
    $('#spinner-'+version)
        .removeClass('fa-exclamation-triangle')
        .addClass('fa-spinner')
        .addClass('fa-pulse');
    changeRowTo('row', version, 'info');
    changeTextTo('span', version, 'In Progress');
}

function markUpdateAsSuccess(version) {
    removeSpinner(version);
    $('#spinner-'+version).addClass('fa-check-circle');
    changeTextTo('span', version, 'Completed Successfully');
    changeRowTo('row', version, 'success');
}

function removeSpinner(version) {
    $('#spinner-'+version)
        .removeClass('fa-pulse')
        .removeClass('fa-spinner');
}

function markUpdateAsAttention(version) {
    removeSpinner(version);
    $('#spinner-'+version).addClass('fa-exclamation-triangle');
    changeRowTo('row', version, 'warning');
    changeTextTo('span', version, 'Attention! See below for more information');
}

function markUpdateAsFailure(version) {
    removeSpinner(version);
    $('#spinner-'+version).addClass('fa-times-circle');
    changeRowTo('row', version, 'danger');
    changeTextTo('span', version, 'Update failed! Check the console for more information');
}

function changeTextTo(prefix, version, text) {
    $('#'+prefix+'-'+version).text(text);
}

function changeRowTo(prefix, version, clazz) {
    //-- Remove all classes
    $('#'+prefix+'-'+version)
        .removeClass('info')
        .removeClass('warning')
        .removeClass('danger')
        .removeClass('success');

    //-- Re-add the requested class
    $('#'+prefix+'-'+version).addClass(clazz);
}

function appendToInstallConsole(text) {
    var currentText = $('#console-text').text();
    $('#console-text').append(text).append('<br>');
}

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

function installationFinished() {
    var output = '<div class="panel-body">' +
        '<div class="col-md-12 text-center">' +
        '<i class="fa fa-check fa-4x" style="color: #008000"></i><br><br>' +
        '<h4>Awesome! The installation / upgrade has completed. Please delete the <code>install</code> directory and then proceed to your helpdesk!</h4>' +
        '</div>' +
        '</div>';
    $('#install-information').html(output);
}

function getContentForMigratePrompt(users) {
    var beginningText = '<h2>Migrating IP / E-mail Bans</h2><p>Mods for HESK has detected that you have added IP address ' +
        'and/or email bans using Mods for HESK. As part of the upgrade process, Mods for HESK will migrate these bans ' +
        'for you to HESK 2.6.0\'s IP/email ban feature. Select the user below that will be the "creator" of the bans, ' +
        'then click "Submit".</p>';
    var selectMarkup = '<div class="row form-horizontal"><div class="control-label col-md-3 col-xs-12" style="text-align: right;vertical-align: middle"><b>User:</b></div>' +
        '<div class="col-md-9 col-x-12"><select name="user" class="form-control" id="user-dropdown">';
    users.forEach(function(user) {
       selectMarkup += '<option value="'+user.id+'">'+user.name+'</option>';
    });
    selectMarkup += '</select></div></div><br>';
    var submitMarkup = '<div class="row"><div class="col-md-9 col-md-offset-3 col-xs-12"><button onclick="runMigration()" class="btn btn-default">Migrate</button> ' +
        '<a href="javascript:void(0)" onclick="migrateComplete()" class="btn btn-danger">Don\'t Migrate</a> </div></div>';

    return beginningText + selectMarkup + submitMarkup;
}

function prepareAttentionPanel(content) {
    $('#attention-body').html(content);
    $('#attention-row').show();
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
    installationFinished();
}

jQuery(document).ready(loadJquery);
