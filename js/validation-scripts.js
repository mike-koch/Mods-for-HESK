function validateRichText(helpBlock, messageGroup, messageContainer, errorText) {
    $('#' + helpBlock).text("");
    $('#' + messageGroup).removeClass('has-error');

    var content = tinyMCE.get(messageContainer).getContent();
    if (content == '') {
        $('#' + helpBlock).text(errorText).focus();
        $('#' + messageGroup).addClass('has-error');
        $('#' + messageGroup).get(0).scrollIntoView();
        return false;
    }
    return true;
}

function buildValidatorForTicketSubmission(formName, validationText) {
    $('form[name="' + formName + '"]').validator({
        custom: {
            checkbox: function($el) {
                var name = $el.data('checkbox');
                var $checkboxes = $el.closest('form').find('input[name="' + name + '[]"]');

                return $checkboxes.is(':checked');
            }
        },
        errors: {
            checkbox: validationText
        }
    }).on('change.bs.validator', '[data-checkbox]', function (e) {
        var $el  = $(e.target);
        var name = $el.data('checkbox');
        var $checkboxes = $el.closest('form').find('input[name="' + name + '[]"]');

        $checkboxes.not(':checked').trigger('input');
    });
}

function buildValidatorForPermissionTemplates(formId, validationText) {
    $('#' + formId).validator({
        custom: {
            checkbox: function($el) {
                var name = $el.data('checkbox');
                var $checkboxes = $el.closest('form').find('input[data-modal="new-' + name + '"]');

                return $checkboxes.is(':checked');
            }
        },
        errors: {
            checkbox: validationText
        }
    }).on('change.bs.validator', '[data-modal]', function (e) {
        var $el  = $(e.target);
        var name = $el.data('checkbox');
        var $checkboxes = $el.closest('form').find('input[data-modal="new-' + name + '"]');

        $checkboxes.not(':checked').trigger('input');
    });
}