var steps = [
    {
        name: 'intro',
        text: 'Thanks for choosing Mods for HESK',
        callback: undefined
    },
    {
        name: 'db-confirm',
        text: 'Confirm the information below',
        callback: undefined
    },
    {
        name: 'install-or-update',
        text: 'Updating to the latest version...',
        showBack: false,
        showNext: false,
        callback: installOrUpdate
    },
    {
        name: 'complete',
        text: 'Installation / Upgrade Complete',
        showBack: false,
        callback: undefined
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
    }

    if (step === steps.length - 1) {
        $('#next-button').hide();
    } else {
        $('#next-button').show();
    }

    // Back/Next button overrides
    if (steps[step].showBack !== undefined && !steps[step].showBack) {
        $('#back-button').hide();
    }
    if (steps[step].showNext !== undefined && !steps[step].showNext) {
        console.log('hiding this');
        $('#next-button').hide();
    }

    $('#header-text').text(steps[step].text);

    if (steps[step].callback !== undefined) {
        steps[step].callback();
    }
}

function installOrUpdate() {
    var startingMigrationNumber = parseInt($('input[name="starting-migration-number"]').val());

    var heskPath = $('p#hesk-path').text();

    $.ajax({
        url: heskPath + 'install/ajax/get-migration-ajax.php',
        method: 'GET',
        success: function(data) {
            data = JSON.parse(data);

            $('[data-step="install-or-update"] > #spinner').hide();
            $('[data-step="install-or-update"] > .progress').show();

            // Recursive call that will increment by 1 each time
            executeMigration(startingMigrationNumber, startingMigrationNumber, data.lastMigrationNumber, 'up');
        }
    })
}

function executeMigration(startingMigrationNumber, migrationNumber, latestMigrationNumber, direction) {
    var heskPath = $('p#hesk-path').text();

    $.ajax({
        url: heskPath + 'install/ajax/process-migration.php',
        method: 'POST',
        data: JSON.stringify({
            migrationNumber: migrationNumber,
            direction: direction
        }),
        success: function(data) {
            console.log('migrationNumber: ' + migrationNumber);
            console.log('latestMigrationNumber: ' + latestMigrationNumber);
            console.log(migrationNumber === latestMigrationNumber);
            if (migrationNumber === latestMigrationNumber) {
                updateProgressBar(migrationNumber, latestMigrationNumber, false, true);
                console.log('DONE');
            } else {
                updateProgressBar(migrationNumber, latestMigrationNumber, false, false);
                var newMigrationNumber = direction === 'up' ? migrationNumber + 1 : migrationNumber - 1;
                executeMigration(startingMigrationNumber, newMigrationNumber, latestMigrationNumber, direction);
            }
        },
        error: function(response) {
            try {
                message = JSON.parse(response);
            } catch (e) {
                message = response.responseText;
            }
            $('#error-block').html("An error occurred! (Error Code: " + migrationNumber + ")<br>" + message).show();
            updateProgressBar(migrationNumber, latestMigrationNumber, true, true);
            console.error(message);
        }
    })
}

function updateProgressBar(migrationNumber, latestMigrationNumber, isError, isFinished) {
    var $progressBar = $('#progress-bar');

    if (isError === true) {
        $progressBar.find('.progress-bar').removeClass('progress-bar-success')
            .removeClass('active')
            .addClass('progress-bar-danger');
    } else {
        var percentage = Math.round(migrationNumber / latestMigrationNumber * 100);
        $progressBar.find('.progress-bar').css('width', percentage + '%');
    }

    if (isFinished && !isError) {
        goToStep(steps.length - 1);
    }
}