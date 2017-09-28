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

    $('#header-text').text(steps[step].text);
}