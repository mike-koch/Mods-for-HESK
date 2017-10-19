var steps = [
    {
        name: 'intro',
        text: 'Uninstall',
        callback: undefined
    },
    {
        name: 'db-confirm',
        text: 'Confirm the information below',
        callback: undefined
    },
    {
        name: 'uninstall',
        text: 'Uninstalling...',
        showBack: false,
        showNext: false,
        callback: uninstall
    },
    {
        name: 'complete',
        text: 'Uninstall Process Complete',
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

function uninstall() {
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
            executeMigration(startingMigrationNumber, 1, 'down');
        }
    })
}

function executeMigration(migrationNumber, latestMigrationNumber, direction) {
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
            console.info('---');
            if (migrationNumber === latestMigrationNumber || (migrationNumber === 1 && direction === 'down')) {
                updateProgressBar(migrationNumber, latestMigrationNumber, direction === 'down', true);
                console.log('%c Success! ', 'color: white; background-color: green; font-size: 2em');
            } else {
                updateProgressBar(migrationNumber, latestMigrationNumber, false, false);
                var newMigrationNumber = direction === 'up' ? migrationNumber + 1 : migrationNumber - 1;
                executeMigration(newMigrationNumber, latestMigrationNumber, direction);
            }
        },
        error: function(response) {
            try {
                message = JSON.parse(response);
            } catch (e) {
                message = response.responseText;
            }
            $errorBlock = $('#error-block');
            $errorBlock.html($errorBlock.html() + "<br><br>An error occurred! (Error Code: " + migrationNumber + ")<br>" + message).show();

            updateProgressBar(migrationNumber, latestMigrationNumber, true, false);

            if (direction === 'up') {
                // Revert!
                executeMigration(migrationNumber - 1, latestMigrationNumber, 'down');
            } else {
                console.error("I even failed to roll back. Yikes! :'(");
            }
            console.error(message);
        }
    })
}

function updateProgressBar(migrationNumber, latestMigrationNumber, isError, isFinished) {
    var $progressBar = $('#progress-bar');

    if (isError === true) {
        $progressBar.find('.progress-bar').removeClass('progress-bar-success')
            .addClass('progress-bar-danger');

        if (isFinished) {
            var $errorBlock = $('#error-block');
            $errorBlock.html($errorBlock.html() + '<br><br>Successfully reverted database to before uninstalling.');
        }
    } else {
        var percentage = Math.round(migrationNumber / latestMigrationNumber * 100);
        $progressBar.find('.progress-bar').css('width', percentage + '%');
    }

    if (isFinished && !isError) {
        goToStep(steps.length - 1);
    }
}