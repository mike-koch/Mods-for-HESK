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

jQuery(document).ready(loadJquery);
