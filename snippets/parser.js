var parseInput = function() {
    var json = '';
    $.getJSON('font-awesome-icons.json', function(data) {
        json = data;
        $.each(data.icons, function(key, val) {
            $('#output').append('<li>' + parseCategories(val.categories) + '</li>');
        });
    });
}

var parseCategories = function(categories) {
    var categoriesString = '';
    $.each(categories, function(key, val) {
        categoriesString += val + ',';
    });
    return categoriesString;
}