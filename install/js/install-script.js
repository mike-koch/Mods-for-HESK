var steps = [
    {
        name: 'intro',
        text: 'Thanks for choosing Mods for HESK',
        callback: undefined
    },
    {
        name: 'db-confirm',
        text: 'Confirm the information below',
        backPossible: true,
        callback: undefined
    },
    {
        name: 'install-or-update',
        text: 'Updating to the latest version...',
        backPossible: false,
        callback: installOrUpdate
    }
];

$(document).ready(function() {
    var currentStep = 0;

    $('#next-button').click(function() {
        goToStep(++currentStep);
    });

    $('#back-button').click(function() {
        goToStep(--currentStep);
    });
});

function goToStep(step) {
    $('[data-step]').hide();
    $('[data-step="' + steps[step].name + '"]').show();

    if (step === 0) {
        $('#tools-button').show();
        $('#back-button').hide();
    } else {
        $('#tools-button').hide();
        $('#back-button').show();

        if (!steps[step].backPossible) {
            $('#back-button').addClass('disabled').attr('disabled', 'disabled');
        }
    }

    if (step === steps.length - 1) {
        $('#next-button').hide();
    } else {
        $('#next-button').show();
    }

    $('#header-text').text(steps[step].text);

    if (steps[step].callback !== undefined) {
        steps[step].callback();
    }
}

function installOrUpdate() {
    var startingMigrationNumber = $('input[name="starting-migration-number"]').val();

    var heskPath = $('p#hesk-path').text();

    $.ajax({
        url: heskPath + 'install/ajax/get-migration-ajax.php',
        method: 'GET',
        success: function(data) {
            data = JSON.parse(data);

            $('[data-step="install-or-update"] > .fa-spinner').hide();
            $('[data-step="install-or-update"] > .progress').show();

            console.log(data.lastMigrationNumber);

            // Recursive call that will increment by 1 each time
            executeMigration(startingMigrationNumber, startingMigrationNumber, data.latestMigrationNumber, 'up');
        }
    })
}

function executeMigration(startingMigrationNumber, migrationNumber, latestMigrationNumber, direction) {
    var heskPath = $('p#hesk-path').text();

    $.ajax({
        url: heskPath + 'install/ajax/process-migration.php',
        method: 'POST',
        data: {
            migrationNumber: migrationNumber,
            direction: direction
        },
        success: function(data) {
            if (migrationNumber === latestMigrationNumber) {
                updateProgressBar(migrationNumber, latestMigrationNumber, false, true);
                console.log('DONE');
            } else {
                updateProgressBar(migrationNumber, latestMigrationNumber, false, false);
                var newMigrationNumber = direction === 'up' ? migrationNumber + 1 : migrationNumber - 1;
                executeMigration(startingMigrationNumber, newMigrationNumber, latestMigrationNumber);
            }
        },
        error: function(data) {
            updateProgressBar(migrationNumber, latestMigrationNumber, true, true);
            console.error(data);
        }
    })
}

function updateProgressBar(migrationNumber, latestMigrationNumber, isError, isFinished) {
    var $progressBar = $('#progress-bar');

    if (isError === true) {
        $progressBar.find('.progress-bar').removeClass('progress-bar-success')
            .addClass('progress-bar-danger');
    } else {
        var percentage = Math.round(migrationNumber / latestMigrationNumber * 100);
        $progressBar.find('.progress-bar').css('width', percentage + '%');
    }

    if (isFinished) {
        $progressBar.find('.progress-bar').hide();
        $('#finished-install').show();
    }
}