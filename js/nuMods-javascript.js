//-- Activate anything Hesk UI needs, such as tooltips.
var loadJquery = function()
{
    //-- Activate tooltips
    $('[data-toggle="tooltip"]').tooltip();

    //-- Active popovers
    $('[data-toggle="popover"]').popover({
        trigger: 'hover'
    })
};

function toggleRow(id) {
    if ($('#' + id).hasClass('danger'))
    {
        $('#' + id).removeClass('danger');
    } else
    {
        $('#' + id).addClass('danger');
    }
}

function toggleColumn(className) {
    if ($('.' + className).css('display') == 'none') {
        $('.' + className).show();
    } else {
        $('.' + className).hide();
    }
}

function toggleFilterCheckboxes(show) {
    if (show) {
        $('#filterCheckboxes').show();
        $('#showFiltersText').hide();
        $('#hideFiltersText').show();
    } else {
        $('#filterCheckboxes').hide();
        $('#showFiltersText').show();
        $('#hideFiltersText').hide();
    }
}

jQuery(document).ready(loadJquery);
