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
        1 => new \Pre140\Statuses\AddIntColumnUpDropTableDown(),
        2 => new \Pre140\Statuses\MoveStatusesToNewColumn(),
        3 => new \Pre140\Statuses\DropOldStatusColumn(),
        4 => new \Pre140\Statuses\RenameTempColumn(),
        5 => new \Pre140\Statuses\CreateStatusesTable(),
        6 => new \Pre140\Statuses\InsertStatusRecords(),
        //1.4.0
        7 => new \v140\AddAutorefreshColumn(),
        8 => new \v140\AddDeniedIpsTable(),
        //1.4.1
        9 => new \v141\AddDeniedEmailsTable(),
        10 => new \v141\AddTicketParentColumn(),
        //1.5.0
        11 => new \v150\AddActiveColumnToUser(),
        12 => new \v150\AddCanManSettingsPermissionToUser(),
        13 => new \v150\AddDefaultNotifyCustomerEmailPreference(),
        //1.6.0
        14 => new \v160\AddNotifyNoteUnassignedProperty(),
        15 => new \v160\AddCanChangeNotificationSettingsPermission(),
        16 => new \v160\AddEditInfoToNotes\AddEditDateColumn(),
        17 => new \v160\AddEditInfoToNotes\AddNumberOfEditsColumn(),
        18 => new \v160\AddNoteIdToAttachments(),
        19 => new \v160\ModifyTicketIdOnAttachments(),
        20 => new \v160\CreateSettingsTable(),
        21 => new \v160\InsertVersionRecord(),
        //1.6.1
        22 => new LegacyUpdateMigration('1.6.1', '1.6.0'),
        //1.7.0
        23 => new \v170\CreateVerifiedEmailsTable(),
        24 => new \v170\CreatePendingVerificationEmailsTable(),
        25 => new \v170\CreateStageTicketsTable(),
        26 => new LegacyUpdateMigration('1.7.0', '1.6.1'),
        //2.0.0
        27 => new \v200\RemoveNoteIdFromAttachments(),
        28 => new \v200\RemoveEditInfoFromNotes\DropEditDate(),
        29 => new \v200\RemoveEditInfoFromNotes\DropNumberOfEditsColumn(),
        30 => new \v200\RemoveDefaultNotifyCustomerEmailPreference(),
        31 => new \v200\AddMissingKeyToTickets(),
        32 => new \v200\MigrateIpAndEmailBans\InsertIpBans(),
        33 => new \v200\MigrateIpAndEmailBans\InsertEmailBans(),
        34 => new \v200\MigrateIpAndEmailBans\DropOldEmailBansTable(),
        35 => new \v200\MigrateIpAndEmailBans\DropOldIpBansTable(),
        36 => new LegacyUpdateMigration('2.0.0', '1.7.0'),
        //2.0.1
        37 => new LegacyUpdateMigration('2.0.1', '2.0.0'),
        //2.1.0
        38 => new LegacyUpdateMigration('2.1.0', '2.0.1'),
        //2.1.1
        39 => new \v211\FixStageTicketsTable\ChangeDtColumnType(),
        40 => new \v211\FixStageTicketsTable\FixStageTicketsTable(),
        41 => new LegacyUpdateMigration('2.1.1', '2.1.0'),
        //2.2.0
        42 => new \v220\AddIsAutocloseOptionToStatuses\AddNewColumn(),
        43 => new \v220\AddIsAutocloseOptionToStatuses\SetDefaultValue(),
        44 => new \v220\AddClosableColumnToStatuses\AddNewColumn(),
        45 => new \v220\AddClosableColumnToStatuses\SetDefaultValue(),
        46 => new LegacyUpdateMigration('2.2.0', '2.1.1'),
        //2.2.1
        47 => new LegacyUpdateMigration('2.2.1', '2.2.0'),
        //2.3.0
        48 => new \v230\AddIconToServiceMessages(),
        49 => new \v230\ConsolidateStatusColumns\AddKeyColumn(),
        50 => new \v230\ConsolidateStatusColumns\SetNewKeyColumnValue(),
        51 => new \v230\ConsolidateStatusColumns\DropShortNameColumn(),
        52 => new \v230\ConsolidateStatusColumns\DropTicketViewContentKeyColumn(),
        53 => new \v230\AddCoordinatesToTickets\AddLatitudeToTickets(),
        54 => new \v230\AddCoordinatesToTickets\AddLongitudeToTickets(),
        55 => new \v230\AddCoordinatesToTickets\AddLatitudeToStageTickets(),
        56 => new \v230\AddCoordinatesToTickets\AddLongitudeToStageTickets(),
        57 => new \v230\AddCategoryManager(),
        58 => new \v230\MovePermissionsToHeskPrivilegesColumn\CopyCanManSettings(),
        59 => new \v230\MovePermissionsToHeskPrivilegesColumn\CopyCanChangeNotificationSettings(),
        60 => new \v230\MovePermissionsToHeskPrivilegesColumn\DropCanManSettingsColumn(),
        61 => new \v230\MovePermissionsToHeskPrivilegesColumn\DropCanChangeNotificationSettingsColumn(),
        62 => new \v230\CreatePermissionTemplates\AddPermissionTemplateColumn(),
        63 => new \v230\CreatePermissionTemplates\CreatePermissionTemplatesTable(),
        64 => new \v230\CreatePermissionTemplates\InsertAdminPermissionTemplate(),
        65 => new \v230\CreatePermissionTemplates\InsertStaffPermissionTemplate(),
        66 => new \v230\CreatePermissionTemplates\UpdateAdminUsersTemplate(),
        67 => new LegacyUpdateMigration('2.3.0', '2.2.1'),
        //2.3.1
        68 => new LegacyUpdateMigration('2.3.1', '2.3.0'),
        //2.3.2
        69 => new LegacyUpdateMigration('2.3.2', '2.3.1'),
        //2.4.0
        70 => new \v240\CreateQuickHelpSections\CreateTable(),
        71 => new \v240\CreateQuickHelpSections\InsertCreateTicketRecord(),
        72 => new \v240\CreateQuickHelpSections\InsertKnowledgebaseRecord(),
        73 => new \v240\CreateQuickHelpSections\InsertStaffCreateTicketRecord(),
        74 => new \v240\CreateQuickHelpSections\InsertViewTicketFormRecord(),
        75 => new \v240\CreateQuickHelpSections\InsertViewTicketRecord(),
        76 => new \v240\CreateNewStatusNameTable\CreateTextToStatusXrefTable(),
        77 => new \v240\CreateNewStatusNameTable\AddSortColumnToStatuses(),
        78 => new \v240\CreateNewStatusNameTable\UpdateSortValues(),
        79 => new \v240\CreateNewStatusNameTable\InsertTextToStatusXrefValues(),
        80 => new \v240\AddDownloadCountToAttachments\AddToAttachmentsTable(),
        81 => new \v240\AddDownloadCountToAttachments\AddToKBAttachmentsTable(),
        82 => new \v240\AddHtmlColumnToTickets\UpdateTicketsTable(),
        83 => new \v240\AddHtmlColumnToTickets\UpdateStageTicketsTable(),
        84 => new \v240\AddHtmlColumnToTickets\UpdateRepliesTable(),
        85 => new LegacyUpdateMigration('2.4.0', '2.3.2'),
        //2.4.1
        86 => new LegacyUpdateMigration('2.4.1', '2.4.0'),
        //2.4.2
        87 => new LegacyUpdateMigration('2.4.2', '2.4.1'),
        //2.5.0
        88 => new \v250\MigrateSettingsToDatabase(),
        89 => new \v250\AddUserAgentAndScreenResToTickets\AddUserAgentToTickets(),
        90 => new \v250\AddUserAgentAndScreenResToTickets\AddUserAgentToStageTickets(),
        91 => new \v250\AddUserAgentAndScreenResToTickets\AddScreenResWidthToTickets(),
        92 => new \v250\AddUserAgentAndScreenResToTickets\AddScreenResHeightToTickets(),
        93 => new \v250\AddUserAgentAndScreenResToTickets\AddScreenResWidthToStageTickets(),
        94 => new \v250\AddUserAgentAndScreenResToTickets\AddScreenResHeightToStageTickets(),
        95 => new \v250\AddUserAgentAndScreenResToTickets\InsertSetting(),
        96 => new \v250\AddNavbarTitleUrl(),
        97 => new LegacyUpdateMigration('2.5.0', '2.4.2'),
        //2.5.1
        98 => new LegacyUpdateMigration('2.5.1', '2.5.0'),
        //2.5.2
        99 => new LegacyUpdateMigration('2.5.2', '2.5.1'),
        //2.5.3
        100 => new LegacyUpdateMigration('2.5.3', '2.5.2'),
        //2.5.4
        101 => new LegacyUpdateMigration('2.5.4', '2.5.3'),
        //2.5.5
        102 => new LegacyUpdateMigration('2.5.5', '2.5.4'),
        //2.6.0
        103 => new \v260\AddApiTables\CreateTable(),
        104 => new \v260\AddApiTables\InsertSetting(),
        105 => new \v260\AddLoggingTable(),
        106 => new \v260\AddTempAttachmentTable(),
        107 => new \v260\AddCalendarModule\CreateCalendarEventTable(),
        108 => new \v260\AddCalendarModule\CreateCalendarEventReminderTable(),
        109 => new \v260\AddCalendarModule\AddDueDateColumnToTickets(),
        110 => new \v260\AddCalendarModule\AddOverdueEmailSentColumnToTickets(),
        111 => new \v260\AddCalendarModule\AddColorColumnToCategories(),
        112 => new \v260\AddCalendarModule\AddUsageColumnToCategories(),
        113 => new \v260\AddCalendarModule\AddNotifyOverdueUnassignedColumnToUsers(),
        114 => new \v260\AddCalendarModule\AddDefaultCalendarViewToUsers(),
        115 => new \v260\AddCalendarModule\InsertEnableCalendarSetting(),
        116 => new \v260\AddCalendarModule\InsertFirstDayOfWeekSetting(),
        117 => new \v260\AddCalendarModule\InsertDefaultCalendarViewSetting(),
        118 => new \v260\AddPrimaryKeyToSettings(),
        119 => new \v260\ConvertStatusPropertiesToInts(),
        120 => new LegacyUpdateMigration('2.6.0', '2.5.5'),
        //2.6.1
        121 => new LegacyUpdateMigration('2.6.1', '2.6.0'),
        //2.6.2
        122 => new \v262\AddMissingColumnsToStageTickets\AddDueDateColumn(),
        123 => new \v262\AddMissingColumnsToStageTickets\AddOverdueEmailSentColumn(),
        124 => new LegacyUpdateMigration('2.6.2', '2.6.1'),
        //2.6.3
        125 => new LegacyUpdateMigration('2.6.3', '2.6.2'),
        //2.6.4
        126 => new LegacyUpdateMigration('2.6.4', '2.6.3'),
        //3.0.0
        127 => new \v300\MigrateHeskCustomStatuses(),
        128 => new \v300\MigrateAutorefreshOption\UpdateFromOldValue(),
        129 => new \v300\MigrateAutorefreshOption\DropOldColumn(),
        130 => new \v300\AddColorSchemeSetting(),
        131 => new LegacyUpdateMigration('3.0.0', '2.6.4'),
        //3.0.1
        132 => new LegacyUpdateMigration('3.0.1', '3.0.0'),
        //3.0.2
        133 => new \v302\AddMissingCustomFields(),
        134 => new LegacyUpdateMigration('3.0.2', '3.0.1'),
        //3.0.3 - 3.0.7
        135 => new LegacyUpdateMigration('3.0.3', '3.0.2'),
        136 => new LegacyUpdateMigration('3.0.4', '3.0.3'),
        137 => new LegacyUpdateMigration('3.0.5', '3.0.4'),
        138 => new LegacyUpdateMigration('3.0.6', '3.0.5'),
        139 => new LegacyUpdateMigration('3.0.7', '3.0.6'),
        //3.1.0
        140 => new \v310\AddStackTraceToLogs(),
        141 => new \v310\AddCustomNavElements\CreateCustomNavElementTable(),
        142 => new \v310\AddCustomNavElements\CreateCustomNavElementToTextTable(),
        143 => new \v310\AddMoreColorOptionsToCategories\AddForegroundColor(),
        144 => new \v310\AddMoreColorOptionsToCategories\AddDisplayBorderOutline(),
        145 => new \v310\AddMoreColorOptionsToCategories\AddBackgroundColor(),
        146 => new \v310\AddNewLoginSettings\AddLoginBackgroundType(),
        147 => new \v310\AddNewLoginSettings\AddLoginBackground(),
        148 => new \v310\AddNewLoginSettings\AddLoginBoxHeader(),
        149 => new \v310\AddNewLoginSettings\AddLoginBoxHeaderImage(),
        150 => new \v310\AddApiUrlRewriteSetting(),
        151 => new \v310\ConvertPresetToIndividualColors(),
        152 => new LegacyUpdateMigration('3.1.0', '3.0.7'),
        //3.1.1
        153 => new LegacyUpdateMigration('3.1.1', '3.1.0'),
        //3.2.0
        154 => new \v320\AddDescriptionToCategoriesAndCustomFields\AddDescriptionToCategories(),
        155 => new \v320\AddDescriptionToCategoriesAndCustomFields\AddDescriptionToCustomFields(),
        156 => new \v320\AddAuditTrail\CreateAuditTrailTable(),
        157 => new \v320\AddAuditTrail\CreateAuditTrailToReplacementValuesTable(),
        158 => new \v320\AddMigrationSetting(),
        159 => new UpdateMigration('3.2.0', '3.1.1', 159),
        //3.2.1
        160 => new UpdateMigration('3.2.1', '3.2.0', 160),
    );
}