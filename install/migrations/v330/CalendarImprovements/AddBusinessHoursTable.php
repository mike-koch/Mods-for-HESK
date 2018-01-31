<?php

namespace v330\CalendarImprovements;

class AddBusinessHoursTable extends \AbstractUpdatableMigration {

    function innerUp($hesk_settings) {
        $this->executeQuery("CREATE TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mfh_calendar_business_hours`
            (`day_of_week` INT NOT NULL, `start_time` VARCHAR(5) NOT NULL, `end_time` VARCHAR(5) NOT NULL)");
    }

    function innerDown($hesk_settings) {
        $this->executeQuery("DROP TABLE `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mfh_calendar_business_hours`");
    }
}