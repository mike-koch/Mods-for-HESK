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