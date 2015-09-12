function getTasks() {
    return ['status-change', 'drop-columns'];
}

function processUninstallation() {
    var tasks = getTasks();
    //-- Change status column to default HESK values
    tasks.forEach(function (task) {
        startUninstallation(task);
        executeUninstallation(task);
    });
}
function startUninstallation(task) {
    $('#spinner-' + task)
        .removeClass('fa-exclamation-triangle')
        .addClass('fa-spinner')
        .addClass('fa-pulse');
    changeRowTo('row', task, 'info');
    changeTextTo('span', task, 'In Progress');
}

function changeTextTo(prefix, task, text) {
    $('#' + prefix + '-' + task).text(text);
}

function changeRowTo(prefix, task, clazz) {
    //-- Remove all classes
    $('#' + prefix + '-' + task)
        .removeClass('info')
        .removeClass('warning')
        .removeClass('danger')
        .removeClass('success');

    //-- Re-add the requested class
    $('#' + prefix + '-' + task).addClass(clazz);
}

function executeUninstallation(task) {
    appendToInstallConsole('<tr><td><span class="label label-info">INFO</span></td><td>Starting task code: ' + task + '</td></tr>');
    $.ajax({
        type: 'POST',
        url: 'ajax/uninstall-database-ajax.php',
        data: {task: task},
        success: function (data) {
            markUninstallAsSuccess(task);
            checkForCompletion();
        },
        error: function (data) {
            if (data.status == 400) {
                appendToInstallConsole('<tr><td><span class="label label-danger">ERROR</span></td><td>The task <code>' + task + '</code> was not recognized. Check the value submitted and try again.</td></tr>');
            } else {
                appendToInstallConsole('<tr><td><span class="label label-danger">ERROR</span></td><td>' + data.responseText + '</td></tr>');
            }
            markUninstallAsFailure(task);
        }
    });
}

function checkForCompletion() {
    // If all rows have a .success row, installation is finished
    var numberOfTasks = getTasks().length;
    var numberOfCompletions = $('tr.success').length;
    if (numberOfTasks == numberOfCompletions) {
        uninstallationFinished();
    }
}

function uninstallationFinished() {
    appendToInstallConsole('<tr><td><span class="label label-success">SUCCESS</span></td><td>Uninstallation complete</td></tr>');
    var output = '<div class="panel-body">' +
        '<div class="col-md-12 text-center">' +
        '<i class="fa fa-check-circle fa-4x" style="color: #008000"></i><br><br>' +
        '<h4>Awesome! The automated portion of uninstalling Mods for HESK has completed. ' +
        'Please follow <a href="http://mods-for-hesk.mkochcs.com/uninstall-instructions.php" target="_blank">these instructions</a> ' +
        'on the Mods for HESK website to finish uninstallation.</h4>' +
        '</div>' +
        '</div>';
    $('#uninstall-information').html(output);
}

function markUninstallAsSuccess(task) {
    removeSpinner(task);
    $('#spinner-' + task).addClass('fa-check-circle');
    changeTextTo('span', task, 'Completed Successfully');
    changeRowTo('row', task, 'success');
    appendToInstallConsole('<tr><td><span class="label label-success">SUCCESS</span></td><td>Uninstall for task code: <code>' + task + '</code> complete</td></tr>');
}

function markUninstallAsFailure(task) {
    removeSpinner(task);
    $('#spinner-' + task).addClass('fa-times-circle');
    changeRowTo('row', task, 'danger');
    changeTextTo('span', task, 'Uninstall failed! Check the console for more information');
}

function removeSpinner(task) {
    $('#spinner-' + task)
        .removeClass('fa-pulse')
        .removeClass('fa-spinner');
}

jQuery(document).ready(loadJquery);