var categoryGroups = [];

$(document).ready(function() {
    loadTable();
    bindEditModal();
    //bindModalCancelCallback();
    bindFormSubmit();
    //bindDeleteButton();
    bindCreateModal();
    //bindSortButtons();
});


function loadTable() {
    $('#overlay').show();
    var heskUrl = $('p#hesk-path').text();
    //var $tableBody = $('#table-body');

    $.ajax({
        method: 'GET',
        url: heskUrl + 'api/index.php/v1/category-groups',
        headers: { 'X-Internal-Call': true },
        success: function(data) {
            var treeJson = [];
            $.each(data, function() {
                treeJson.push({
                    id: this.id,
                    text: this.names[$('input[name="hesk_lang"]').val()],
                    parent: this.parentId === null ? '#' : this.parentId,
                    icon: false,
                    state: {
                        opened: true
                    }
                });

                categoryGroups[this.id] = this;
            });

            $('#tree')
                .bind('loaded.jstree', function() {
                    $('[data-toggle="tooltip"]').tooltip();
                })
                .jstree({
                    plugins: ['grid', 'dnd'],
                    grid: {
                        columns: [
                            {
                                header: 'Category Group Name',
                                wideCellClass: 'tree-column'
                            },
                            {
                                header: 'Edit',
                                value: function(node) { return $('#category-group-edit-template').html(); }
                            },
                            {
                                header: 'Delete',
                                value: function(node) { return $('#category-group-delete-template').html(); }
                            }
                        ]
                    },
                    core: {
                        check_callback: true,
                        data: treeJson
                    },
                    dnd: {
                        copy: false
                    }
                });

            $(document).on('dnd_stop.vakata', function(data, element, helper, event) {
                $.ajax({
                    method: 'POST',
                    url: heskUrl + 'api/index.php/v1-internal/category-group-tree',
                    headers: { 'X-Internal-Call': true },
                    data: JSON.stringify($('#tree').jstree().get_json()),
                    success: function() {
                        mfhAlert.success(mfhLang.text('category_group_hierarchy_updated'));
                    },
                    error: function(data) {
                        console.error(data);
                    }
                })
            });
        },
        error: function(data) {
            mfhAlert.errorWithLog(mfhLang.text('error_retrieving_category_groups'), data.responseJSON);
            console.error(data);
        },
        complete: function() {
            $('#overlay').hide();
        }
    });
}

function buildOptions(node) {
    return $('#category-group-options-template').html();
}

function bindEditModal() {
    $(document).on('click', '[data-action="edit"]', function() {
        var element = categoryGroups[$(this).parent().parent().find('[data-property="id"]').text()];
        var $modal = $('#category-modal');

        $modal.find('#edit-label').show();
        $modal.find('#create-label').hide();

        for (var key in element.names) {
            $modal.find('input[name="' + key + '"]').val(element.names[key]);
        }

        refreshParentCategoryGroups(element.id);
        $modal.find('select[name="parent-category-group"]').val(element.parentId).selectpicker('refresh');

        console.info(element);
        $modal.modal('show');
    });
}

function refreshParentCategoryGroups(ignoreId) {
    var $dropdown = $('#category-modal').find('select[name="parent-category-group"]');
    $dropdown.html('');
    $dropdown.append('<option value="">' + mfhLang.text('none') + '</option>');

    // Remove itself and any parents
    var exclude = [];
    if (ignoreId !== undefined) {
        var element = categoryGroups[ignoreId];
        exclude.push(element.id);

        while (element.parentId !== null) {
            element = categoryGroups[element.parentId];
            exclude.push(element.id);
        }
    }

    for (var key in categoryGroups) {
        if (exclude.indexOf(key) > -1) {
            continue;
        }

        var value = categoryGroups[key];

        $dropdown.append('<option value="' + value.id + '">' + mfhStrings.escape(value.names[$('input[name="hesk_lang"]').val()]) + '</option>');
    }
    $dropdown.selectpicker('refresh');
}

function bindCreateModal() {
    $('#create-button').click(function() {
        var $modal = $('#category-modal');
        $modal.find('#edit-label').hide();
        $modal.find('#create-label').show();

        $modal.find('input[data-type="name"]').val('');
        $modal.find('select[name="parent-category-group"]').val('');
        $modal.find('input[name="id"]').val('-1');
        refreshParentCategoryGroups(undefined);

        $modal.modal('show');
    });
}

function bindModalCancelCallback() {
    $('.cancel-callback').click();
}

function bindFormSubmit() {
    $('form#manage-category').submit(function(e) {
        e.preventDefault();
        var heskUrl = $('p#hesk-path').text();

        var $modal = $('#category-modal');
        var names = {};
        $.each($('input[data-type="name"]'), function() {
            names[$(this).attr('name')] = $(this).val();
        });

        var $parentCategoryGroupDropdown = $('select[name="parent-category-group"]');
        var data = {
            names: names,
            parentId: $parentCategoryGroupDropdown.val() !== '' ? parseInt($parentCategoryGroupDropdown.val()) : null
        };

        var url = heskUrl + 'api/index.php/v1/category-groups/';
        var method = 'POST';

        var categoryId = parseInt($modal.find('input[name="id"]').val());
        if (categoryId !== -1) {
            url += categoryId;
            method = 'PUT';
        }

        $modal.find('#action-buttons').find('.cancel-button').attr('disabled', 'disabled');
        $modal.find('#action-buttons').find('.save-button').attr('disabled', 'disabled');

        console.log(data);
        $.ajax({
            method: 'POST',
            url: url,
            headers: {
                'X-Internal-Call': true,
                'X-HTTP-Method-Override': method
            },
            data: JSON.stringify(data),
            success: function(data) {
                var format = undefined;
                if (categoryId === -1) {
                    mfhAlert.success(mfhLang.text('category_group_created'));
                } else {
                    mfhAlert.success(mfhLang.text('category_group_updated'));
                }
                resetModal();
                $modal.modal('hide');
                loadTable();
            },
            error: function(data) {
                mfhAlert.errorWithLog(mfhLang.text('error_saving_updating_category_group'), data.responseJSON);
                console.error(data);
            },
            complete: function(data) {
                $modal.find('#action-buttons').find('.cancel-button').removeAttr('disabled');
                $modal.find('#action-buttons').find('.save-button').removeAttr('disabled');
            }
        });
    });
}

function bindDeleteButton() {
    $(document).on('click', '[data-action="delete"]', function() {
        $('#overlay').show();

        var heskUrl = $('p#hesk-path').text();
        var element = categories[$(this).parent().parent().find('[data-property="id"]').text()];

        $.ajax({
            method: 'POST',
            url: heskUrl + 'api/index.php/v1/categories/' + element.id,
            headers: {
                'X-Internal-Call': true,
                'X-HTTP-Method-Override': 'DELETE'
            },
            success: function() {
                mfhAlert.success(mfhLang.text('cat_removed'));
                loadTable();
            },
            error: function(data) {
                $('#overlay').hide();
                mfhAlert.errorWithLog(mfhLang.text('error_deleting_category'), data.responseJSON);
                console.error(data);
            }
        });
    });
}

function bindSortButtons() {
    $(document).on('click', '[data-action="sort"]', function() {
        $('#overlay').show();
        var heskUrl = $('p#hesk-path').text();
        var direction = $(this).data('direction');
        var element = categories[$(this).parent().parent().parent().find('[data-property="id"]').text()];

        $.ajax({
            method: 'POST',
            url: heskUrl + 'api/index.php/v1-internal/categories/' + element.id + '/sort/' + direction,
            headers: { 'X-Internal-Call': true },
            success: function() {
                loadTable();
            },
            error: function(data) {
                mfhAlert.errorWithLog(mfhLang.text('error_sorting_categories'), data.responseJSON);
                console.error(data);
                $('#overlay').hide();
            }
        })
    });
}