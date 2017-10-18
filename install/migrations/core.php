<?php
set_error_handler(function($errorNumber, $errorMessage, $errorFile, $errorLine) {
    output("An error occurred: {$errorMessage} in {$errorFile} on {$errorLine}",
        500,
        "Content-Type: text/plain");
});

spl_autoload_register(function ($class) {
    // USED FOR MIGRATIONS
    $file = HESK_PATH . 'install/migrations/' . str_replace('\\', '/', $class) . '.php';

    if (file_exists($file)) {
        require($file);
    } else {
        output(array("message" => "{$file} not found!", 500));
    }
});

function getAllMigrations() {
    return array(
        1 => new \Pre140\StatusesMigration(),
        //1.4.0
        2 => new \v140\AddAutorefreshColumn(),
        3 => new \v140\AddDeniedIpsTable(),
        //1.4.1
        4 => new \v141\AddDeniedEmailsTable(),
        5 => new \v141\AddTicketParentColumn(),
        //1.5.0
        6 => new \v150\AddActiveColumnToUser(),
        7 => new \v150\AddCanManSettingsPermissionToUser(),
        8 => new \v150\AddDefaultNotifyCustomerEmailPreference(),
        //1.6.0
        9 => new \v160\AddNotifyNoteUnassignedProperty(),
        10 => new \v160\AddCanChangeNotificationSettingsPermission(),
        11 => new \v160\AddEditInfoToNotes(),
        12 => new \v160\AddNoteIdToAttachments(),
        13 => new \v160\ModifyTicketIdOnAttachments(),
        14 => new \v160\CreateSettingsTable(),
        15 => new \v160\InsertVersionRecord(),
        //1.6.1
        16 => new LegacyUpdateMigration('1.6.1', '1.6.0'),
        //1.7.0
        17 => new \v170\CreateVerifiedEmailsTable(),
        18 => new \v170\CreatePendingVerificationEmailsTable(),
        19 => new \v170\CreateStageTicketsTable(),
        20 => new LegacyUpdateMigration('1.7.0', '1.6.1'),
        //2.0.0
        21 => new \v200\RemoveNoteIdFromAttachments(),
        22 => new \v200\RemoveEditInfoFromNotes(),
        23 => new \v200\RemoveDefaultNotifyCustomerEmailPreference(),
        24 => new \v200\AddMissingKeyToTickets(),
        25 => new \v200\MigrateIpAndEmailBans(),
        26 => new LegacyUpdateMigration('2.0.0', '1.7.0'),
        //2.0.1
        27 => new LegacyUpdateMigration('2.0.1', '2.0.0'),
        //2.1.0
        28 => new LegacyUpdateMigration('2.1.0', '2.0.1'),
        //2.1.1
        29 => new \v211\FixStageTicketsTable(),
        30 => new LegacyUpdateMigration('2.1.1', '2.1.0'),
        //2.2.0
        31 => new \v220\AddIsAutocloseOptionToStatuses(),
        32 => new \v220\AddClosableColumnToStatuses(),
        33 => new LegacyUpdateMigration('2.2.0', '2.1.1'),
        //2.2.1
        34 => new LegacyUpdateMigration('2.2.1', '2.2.0'),
        //2.3.0
        35 => new \v230\AddIconToServiceMessages(),
        36 => new \v230\ConsolidateStatusColumns(),
        37 => new \v230\AddCoordinatesToTickets(),
        38 => new \v230\AddCategoryManager(),
        39 => new \v230\MovePermissionsToHeskPrivilegesColumn(),
        40 => new \v230\CreatePermissionTemplates(),
        41 => new LegacyUpdateMigration('2.3.0', '2.2.1'),
        //2.3.1
        42 => new LegacyUpdateMigration('2.3.1', '2.3.0'),
        //2.3.2
        43 => new LegacyUpdateMigration('2.3.2', '2.3.1'),
        //2.4.0
        44 => new \v240\CreateQuickHelpSectionsTable(),
        45 => new \v240\CreateNewStatusNameTable(),
        46 => new \v240\AddDownloadCountToAttachments(),
        47 => new \v240\AddHtmlColumnToTickets(),
        48 => new LegacyUpdateMigration('2.4.0', '2.3.2'),
        //2.4.1
        49 => new LegacyUpdateMigration('2.4.1', '2.4.0'),
        //2.4.2
        50 => new LegacyUpdateMigration('2.4.2', '2.4.1'),
        //2.5.0
        51 => new \v250\MigrateSettingsToDatabase(),
        52 => new \v250\AddUserAgentAndScreenResToTickets(),
        53 => new \v250\AddNavbarTitleUrl(),
        54 => new LegacyUpdateMigration('2.5.0', '2.4.2'),
        //2.5.1
        55 => new LegacyUpdateMigration('2.5.1', '2.5.0'),
        //2.5.2
        56 => new LegacyUpdateMigration('2.5.2', '2.5.1'),
        //2.5.3
        57 => new LegacyUpdateMigration('2.5.3', '2.5.2'),
        //2.5.4
        58 => new LegacyUpdateMigration('2.5.4', '2.5.3'),
        //2.5.5
        59 => new LegacyUpdateMigration('2.5.5', '2.5.4'),
        //2.6.0
        60 => new \v260\AddApiTables(),
        61 => new \v260\AddLoggingTable(),
        62 => new \v260\AddTempAttachmentTable(),
        63 => new \v260\AddCalendarModule(),
        64 => new \v260\AddPrimaryKeyToSettings(),
        65 => new \v260\ConvertStatusPropertiesToInts(),
        66 => new LegacyUpdateMigration('2.6.0', '2.5.5'),
        //2.6.1
        67 => new LegacyUpdateMigration('2.6.1', '2.6.0'),
        //2.6.2
        68 => new \v262\AddMissingColumnsToStageTickets(),
        69 => new LegacyUpdateMigration('2.6.2', '2.6.1'),
        //2.6.3
        70 => new LegacyUpdateMigration('2.6.3', '2.6.2'),
        //2.6.4
        71 => new LegacyUpdateMigration('2.6.4', '2.6.3'),
        //3.0.0
        72 => new \v300\MigrateHeskCustomStatuses(),
        73 => new \v300\MigrateAutorefreshOption(),
        74 => new \v300\AddColorSchemeSetting(),
        75 => new LegacyUpdateMigration('3.0.0', '2.6.4'),
        //3.0.1
        76 => new LegacyUpdateMigration('3.0.1', '3.0.0'),
        //3.0.2
        77 => new \v302\AddMissingCustomFields(),
        78 => new LegacyUpdateMigration('3.0.2', '3.0.1'),
        //3.0.3 - 3.0.7
        79 => new LegacyUpdateMigration('3.0.3', '3.0.2'),
        80 => new LegacyUpdateMigration('3.0.4', '3.0.3'),
        81 => new LegacyUpdateMigration('3.0.5', '3.0.4'),
        82 => new LegacyUpdateMigration('3.0.6', '3.0.5'),
        83 => new LegacyUpdateMigration('3.0.7', '3.0.6'),
        //3.1.0
        84 => new \v310\AddStackTraceToLogs(),
        85 => new \v310\AddCustomNavElements(),
        86 => new \v310\AddMoreColorOptionsToCategories(),
        87 => new \v310\AddNewLoginSettings(),
        88 => new \v310\AddApiUrlRewriteSetting(),
        89 => new \v310\ConvertPresetToIndividualColors(),
        90 => new LegacyUpdateMigration('3.1.0', '3.0.7'),
        //3.1.1
        91 => new LegacyUpdateMigration('3.1.1', '3.1.0'),
        //3.2.0
        92 => new \v320\AddDescriptionToCategoriesAndCustomFields(),
        93 => new \v320\AddAuditTrail(),
        94 => new \v320\AddMigrationSetting(),
        95 => new UpdateMigration('3.2.0', '3.1.1', 95),
    );
}