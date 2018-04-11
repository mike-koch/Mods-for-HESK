$(document).ready(function() {
    $('form[action="reply_ticket.php"]').submit(function(e) {
        var heskUrl = $('p#hesk-path').text();
        var ticketId = $('input[name="ticket_id"]').val();
        var html = $('input[name="html"]').val() === '1';
        e.preventDefault();

        $.ajax({
            method: 'POST',
            url: heskUrl + 'api/index.php/v1/tickets/' + ticketId + '/replies',
            headers: { 'X-Internal-Call': true },
            data: JSON.stringify({
                email: $('input[name="e"]').val(),
                trackingId: $('input[name="orig_track"]').val(),
                message: getMessage(),
                html: html
            }),
            success: function(data) {
                mfhAlert.success('Reply Submitted');
                console.log(data);
            },
            error: function(data) {
                mfhAlert.error('An error occurred when trying to submit the reply.');
                console.error(data);
            }
        });
    });
});

function getMessage() {
    if ($('input[name="html"]').val() === "1") {
        return tinyMCE.get('message').getContent();
    }

    return $('textarea[name="message"]').val();
}