//-- Activate anything Hesk UI needs, such as tooltips.
var loadJquery = function()
{
    //-- Activate tooltips
    $('[data-toggle="tooltip"]').tooltip();

    //-- Active popovers
    $('[data-toggle="popover"]').popover({
        trigger: 'hover'
    });

    //-- Activate jQuery's date picker
    $(function() {
        $('.datepicker').datepicker({
            todayBtn: "linked",
            clearBtn: true,
            autoclose: true,
            todayHighlight: true
        });
    });
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

function toggleChildrenForm(show) {
    if (show) {
        $('#childrenForm').show();
        $('#addChildText').hide();
    } else {
        $('#childrenForm').hide();
        $('#addChildText').show();
    }
}

function toggleNote(noteId, showForm) {
    if (showForm) {
        $('#note-' + noteId + '-p').hide();
        $('#note-' + noteId + '-form').show();
    } else {
        $('#note-' + noteId + '-p').show();
        $('#note-' + noteId + '-form').hide();
        $('#note-' + noteId + '-textarea').val($('#note-' + noteId + '-p').text())
    }
}

jQuery(document).ready(loadJquery);
