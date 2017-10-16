function executeMigration(migrationNumber, direction, success, error) {
    var heskPath = $('p#hesk-path').text();

    $.ajax({
        url: heskPath + 'install/ajax/process-migration.php',
        method: 'POST',
        data: JSON.stringify({
            migrationNumber: migrationNumber,
            direction: direction
        }),
        success: success(data),
        error: error(data)
    })
}