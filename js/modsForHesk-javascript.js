//-- Activate anything Hesk UI needs, such as tooltips.
var loadJquery = function()
{
    //-- Activate tooltips
    $('[data-toggle="tooltip"]').tooltip();

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
        success: function() {
            appendToInstallConsole('Version: ' + version);
            console.log('Version: ' + version);
            markUpdateAsSuccess(cssclass);
            if (version == 200) {
                //go to ipbanmigration
                appendToInstallConsole('Going to IP/Email Ban Migration...');
                console.log('Going to IP/Email Ban Migration...');
            }
            processUpdates(version);
        },
        error: function() {
            markUpdateAsFailure(cssclass);
            console.error('ERROR!');
        }
    });
}

function migrateIpEmailBans() {

}

jQuery(document).ready(loadJquery);
