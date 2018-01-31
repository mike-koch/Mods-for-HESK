<?php

namespace v330\CalendarImprovements;


class InsertDefaultBusinessHours extends \AbstractUpdatableMigration {

    function innerUp($hesk_settings) {
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mfh_calendar_business_hours` (`day_of_week`, `start_time`, `end_time`)
            VALUES (0, '00:00', '23:59')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mfh_calendar_business_hours` (`day_of_week`, `start_time`, `end_time`)
            VALUES (1, '00:00', '23:59')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mfh_calendar_business_hours` (`day_of_week`, `start_time`, `end_time`)
            VALUES (2, '00:00', '23:59')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mfh_calendar_business_hours` (`day_of_week`, `start_time`, `end_time`)
            VALUES (3, '00:00', '23:59')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mfh_calendar_business_hours` (`day_of_week`, `start_time`, `end_time`)
            VALUES (4, '00:00', '23:59')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mfh_calendar_business_hours` (`day_of_week`, `start_time`, `end_time`)
            VALUES (5, '00:00', '23:59')");
        $this->executeQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mfh_calendar_business_hours` (`day_of_week`, `start_time`, `end_time`)
            VALUES (6, '00:00', '23:59')");
    }

    function innerDown($hesk_settings) {
        $this->executeQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mfh_calendar_business_hours`");
    }
}