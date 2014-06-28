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


jQuery(document).ready(loadJquery);
