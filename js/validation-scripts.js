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
                var checkboxes = $('input[name="' + $el.attr('data-checkbox') + '[]"]');

                for (var checkbox in checkboxes) {
                    if (checkboxes[checkbox].checked) {
                        return true;
                    }
                }
                return false;
            },
            multiselect: function($el) {
                var count = $('select[name="' + $el.attr('data-multiselect') + '[]"] :selected').length;
                return count > 0;
            }
        },
        errors: {
            checkbox: validationText,
            multiselect: validationText
        }
    });
}

function buildValidatorForPermissionTemplates(formId, validationText) {
    $('#' + formId).validator({
        custom: {
            checkbox: function($el) {
                var checkboxes = $('input[data-modal="new-' + $el.attr('data-checkbox') + '"]');

                for (var checkbox in checkboxes) {
                    if (checkboxes[checkbox].checked) {
                        return true;
                    }
                }
                return false;
            }
        },
        errors: {
            checkbox: validationText
        }
    });
}