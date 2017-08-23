var categories = [];

$(document).ready(function() {
    loadTable();
});


function loadTable() {
    $('#overlay').show();
    var heskUrl = $('p#hesk-path').text();
    var $tableBody = $('#table-body');

    $.ajax({
        method: 'GET',
        url: heskUrl + 'api/index.php/v1/categories/all',
        headers: { 'X-Internal-Call': true },
        success: function(data) {
            $tableBody.html('');

            if (data.length === 0) {
                mfhAlert.error("I couldn't find any categories :(", "No categories found");
                $('#overlay').hide();
                return;
            }

            var totalNumberOfTickets = 0;
            $.each(data, function() {
                totalNumberOfTickets += this.numberOfTickets;
            });

            var first = true;
            var lastElement = null;
            $.each(data, function() {
                var $template = $($('#category-row-template').html());

                $template.find('span[data-property="id"]').text(this.id).attr('data-value', this.id);
                $template.find('span[data-property="category-name"]').text(this.name);
                var $priority = $template.find('span[data-property="priority"]');
                if (this.priority === 0) {
                    // Critical
                    $priority.text(mfhLang.text('critical')).addClass('critical');
                } else if (this.priority === 1) {
                    // High
                    $priority.text(mfhLang.text('high')).addClass('important');
                } else if (this.priority === 2) {
                    // Medium
                    $priority.text(mfhLang.text('medium')).addClass('medium');
                } else {
                    // Low
                    $priority.text(mfhLang.text('low')).addClass('normal');
                }
                $template.find('a[data-property="number-of-tickets"]')
                    .text(this.numberOfTickets)
                    .attr('href', '#' + this.numberOfTickets);
                var percentText = mfhLang.text('perat');
                var percentage = Math.round(this.numberOfTickets / totalNumberOfTickets * 100);
                $template.find('div.progress').attr('title', percentText.replace('%s', percentage + '%'));
                $template.find('div.progress-bar').attr('aria-value-now', percentage).css('width', percentage + '%');

                if (this.usage === 1) {
                    // Tickets only
                    $template.find('.fa-calendar').removeClass('fa-calendar');
                } else if (this.usage === 2) {
                    // Events only
                    $template.find('.fa-ticket').removeClass('fa-ticket');
                }

                // TODO Action buttons

                $tableBody.append($template);

                categories[this.id] = this;

                lastElement = this;

                if (first) {
                    $template.find('[data-direction="up"]').css('visibility', 'hidden');
                    first = false;
                }
            });

            if (lastElement) {
                //-- Hide the down arrow on the last element
                $('[data-value="' + lastElement.id + '"]').parent().parent()
                    .find('[data-direction="down"]').css('visibility', 'hidden');
            }
        },
        error: function(data) {
            mfhAlert.errorWithLog(mfhLang.text('Something bad happened...'), data.responseJSON);
            console.error(data);
        },
        complete: function() {
            $('#overlay').hide();
        }
    });
}