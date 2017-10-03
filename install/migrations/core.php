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
        16 => new \v161\UpdateVersion(),
        //1.7.0
        17 => new \v170\CreateVerifiedEmailsTable(),
        18 => new \v170\CreatePendingVerificationEmailsTable(),
        19 => new \v170\CreateStageTicketsTable(),
        20 => new \v170\UpdateVersion(),
        //2.0.0
        21 => new \v200\RemoveNoteIdFromAttachments(),
        22 => new \v200\RemoveEditInfoFromNotes(),
        23 => new \v200\RemoveDefaultNotifyCustomerEmailPreference(),
        24 => new \v200\AddMissingKeyToTickets(),
        25 => new \v200\MigrateIpAndEmailBans(),
        26 => new \v200\UpdateVersion(),
        //2.0.1
        27 => new \v201\UpdateVersion(),
        //2.1.0
        28 => new \v210\UpdateVersion(),
        //2.1.1
        29 => new \v211\FixStageTicketsTable(),
        30 => new \v211\UpdateVersion(),
        //2.2.0
        31 => new \v220\AddIsAutocloseOptionToStatuses(),
        32 => new \v220\AddClosableColumnToStatuses(),
        33 => new \v220\UpdateVersion(),
        //2.2.1
        34 => new \v221\UpdateVersion(),
        //2.3.0
        35 => new \v230\AddIconToServiceMessages(),
        36 => new \v230\ConsolidateStatusColumns(),
        37 => new \v230\AddCoordinatesToTickets(),
        38 => new \v230\AddCategoryManager(),
        39 => new \v230\MovePermissionsToHeskPrivilegesColumn(),
        40 => new \v230\UpdateVersion(),
        //2.3.1
        41 => new \v231\UpdateVersion(),
        //2.3.2
        42 => new \v232\UpdateVersion(),
        //2.4.0
        43 => new \v240\CreateQuickHelpSectionsTable(),
        44 => new \v240\CreateNewStatusNameTable(),
        45 => new \v240\AddDownloadCountToAttachments(),
        46 => new \v240\AddHtmlColumnToTickets(),
        47 => new \v240\UpdateVersion(),
        //2.4.1
        48 => new \v241\UpdateVersion(),
        //2.4.2
        49 => new \v242\UpdateVersion(),
        //2.5.0
        50 => new \v250\MigrateSettingsToDatabase(),
        51 => new \v250\AddUserAgentAndScreenResToTickets(),
        52 => new \v250\AddNavbarTitleUrl(),
        53 => new \v250\UpdateVersion(),
    );
}