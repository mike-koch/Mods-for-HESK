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

function markUpdateAsSuccess(version, formattedVersion) {
    removeSpinner(version);
    $('#spinner-'+version).addClass('fa-check-circle');
    changeTextTo('span', version, 'Completed Successfully');
    changeRowTo('row', version, 'success');
    appendToInstallConsole('<tr><td><span class="label label-success">SUCCESS</span></td><td>Updates for ' + formattedVersion + ' complete</td></tr>');
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
    $('#consoleBody').append(text);
}

function installationFinished() {
    appendToInstallConsole('<tr><td><span class="label label-success">SUCCESS</span></td><td>Installation complete</td></tr>');
    var output = '<div class="panel-body">' +
        '<div class="col-md-12 text-center">' +
        '<i class="fa fa-check-circle fa-4x" style="color: #008000"></i><br><br>' +
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

jQuery(document).ready(loadJquery);