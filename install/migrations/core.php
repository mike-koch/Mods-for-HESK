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
    );
}