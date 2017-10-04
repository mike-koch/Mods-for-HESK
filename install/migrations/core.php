<?php

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
        16 => new UpdateMigration('1.6.1', '1.6.0'),
        //1.7.0
        17 => new \v170\CreateVerifiedEmailsTable(),
        18 => new \v170\CreatePendingVerificationEmailsTable(),
        19 => new \v170\CreateStageTicketsTable(),
        20 => new UpdateMigration('1.7.0', '1.6.1'),
        //2.0.0
        21 => new \v200\RemoveNoteIdFromAttachments(),
        22 => new \v200\RemoveEditInfoFromNotes(),
        23 => new \v200\RemoveDefaultNotifyCustomerEmailPreference(),
        24 => new \v200\AddMissingKeyToTickets(),
        25 => new \v200\MigrateIpAndEmailBans(),
        26 => new UpdateMigration('2.0.0', '1.7.0'),
        //2.0.1
        27 => new UpdateMigration('2.0.1', '2.0.0'),
        //2.1.0
        28 => new UpdateMigration('2.1.0', '2.0.1'),
        //2.1.1
        29 => new \v211\FixStageTicketsTable(),
        30 => new UpdateMigration('2.1.1', '2.1.0'),
        //2.2.0
        31 => new \v220\AddIsAutocloseOptionToStatuses(),
        32 => new \v220\AddClosableColumnToStatuses(),
        33 => new UpdateMigration('2.2.0', '2.1.1'),
        //2.2.1
        34 => new UpdateMigration('2.2.1', '2.2.0'),
        //2.3.0
        35 => new \v230\AddIconToServiceMessages(),
        36 => new \v230\ConsolidateStatusColumns(),
        37 => new \v230\AddCoordinatesToTickets(),
        38 => new \v230\AddCategoryManager(),
        39 => new \v230\MovePermissionsToHeskPrivilegesColumn(),
        40 => new UpdateMigration('2.3.0', '2.2.1'),
        //2.3.1
        41 => new UpdateMigration('2.3.1', '2.3.0'),
        //2.3.2
        42 => new UpdateMigration('2.3.2', '2.3.1'),
        //2.4.0
        43 => new \v240\CreateQuickHelpSectionsTable(),
        44 => new \v240\CreateNewStatusNameTable(),
        45 => new \v240\AddDownloadCountToAttachments(),
        46 => new \v240\AddHtmlColumnToTickets(),
        47 => new UpdateMigration('2.4.0', '2.3.2'),
        //2.4.1
        48 => new UpdateMigration('2.4.1', '2.4.0'),
        //2.4.2
        49 => new UpdateMigration('2.4.2', '2.4.1'),
        //2.5.0
        50 => new \v250\MigrateSettingsToDatabase(),
        51 => new \v250\AddUserAgentAndScreenResToTickets(),
        52 => new \v250\AddNavbarTitleUrl(),
        53 => new UpdateMigration('2.5.0', '2.4.2'),
        //2.5.1
        54 => new UpdateMigration('2.5.1', '2.5.0'),
        //2.5.2
        55 => new UpdateMigration('2.5.2', '2.5.1'),
        //2.5.3
        56 => new UpdateMigration('2.5.3', '2.5.2'),
        //2.5.4
        57 => new UpdateMigration('2.5.4', '2.5.3'),
        //2.5.5
        58 => new UpdateMigration('2.5.5', '2.5.4'),
        //2.6.0
        59 => new \v260\AddApiTables(),
        60 => new \v260\AddLoggingTable(),
        61 => new \v260\AddTempAttachmentTable(),
        62 => new \v260\AddCalendarModule(),
        63 => new \v260\AddPrimaryKeyToSettings(),
        64 => new \v260\ConvertStatusPropertiesToInts(),
        65 => new UpdateMigration('2.6.0', '2.5.5'),
        //2.6.1
        66 => new UpdateMigration('2.6.1', '2.6.0'),
        //2.6.2
        67 => new \v262\AddMissingColumnsToStageTickets(),
        68 => new UpdateMigration('2.6.2', '2.6.1'),
        //2.6.3
        69 => new UpdateMigration('2.6.3', '2.6.2'),
        //2.6.4
        70 => new UpdateMigration('2.6.4', '2.6.3'),
        //3.0.0
        71 => new \v300\MigrateHeskCustomStatuses(),
        72 => new \v300\MigrateAutorefreshOption(),
        73 => new \v300\AddColorSchemeSetting(),
        74 => new UpdateMigration('3.0.0', '2.6.4'),
        //3.0.1
        75 => new UpdateMigration('3.0.1', '3.0.0'),
        //3.0.2
        76 => new \v302\AddMissingCustomFields(),
        77 => new UpdateMigration('3.0.2', '3.0.1'),
        //3.0.3 - 3.0.7
        78 => new UpdateMigration('3.0.3', '3.0.2'),
        79 => new UpdateMigration('3.0.4', '3.0.3'),
        80 => new UpdateMigration('3.0.5', '3.0.4'),
        81 => new UpdateMigration('3.0.6', '3.0.5'),
        82 => new UpdateMigration('3.0.7', '3.0.6'),
        //3.1.0
        83 => new \v310\AddStackTraceToLogs(),
        84 => new \v310\AddCustomNavElements(),
        85 => new \v310\AddMoreColorOptionsToCategories(),
        86 => new \v310\AddNewLoginSettings(),
        87 => new \v310\AddApiUrlRewriteSetting(),
        88 => new \v310\ConvertPresetToIndividualColors(),
        89 => new UpdateMigration('3.1.0', '3.0.7'),
        //3.1.1
        90 => new UpdateMigration('3.1.1', '3.1.0'),
        //3.2.0
        91 => new \v320\AddDescriptionToCategoriesAndCustomFields(),
        92 => new \v320\AddAuditTrail(),
    );
}