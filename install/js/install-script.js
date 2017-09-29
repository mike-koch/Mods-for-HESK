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
            //executeMigration(startingMigrationNumber, startingMigrationNumber, data.latestMigrationNumber);
        }
    })
}

function executeMigration(startingMigrationNumber, migrationNumber, latestMigrationNumber) {
    $.ajax({
        url: '',
        method: 'POST',
        data: {
            migrationNumber: migrationNumber,
            direction: 'up'
        },
        success: function(data) {
            updateProgressBar(migrationNumber, latestMigrationNumber);

            if (migrationNumber === latestMigrationNumber) {
                // done
            } else {
                executeMigration(startingMigrationNumber, migrationNumber + 1, latestMigrationNumber);
            }
        }
    })
}