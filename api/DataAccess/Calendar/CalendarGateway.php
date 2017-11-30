<?php

namespace DataAccess\Calendar;


use BusinessLogic\Security\UserContext;
use DataAccess\CommonDao;

class CalendarGateway extends CommonDao {

    /**
     * @param $startTime int
     * @param $endTime int
     * @param $userContext UserContext
     * @param $heskSettings array
     * @return array
     */
    public function getEventsForStaff($startTime, $endTime, $userContext, $heskSettings) {
        $this->init();

        $startTimeSql = "CONVERT_TZ(FROM_UNIXTIME(" . hesk_dbEscape($startTime) . " / 1000), @@session.time_zone, '+00:00')";
        $endTimeSql = "CONVERT_TZ(FROM_UNIXTIME(" . hesk_dbEscape($endTime) . " / 1000), @@session.time_zone, '+00:00')";

        $sql = "SELECT `events`.*, `categories`.`name` AS `category_name`, `categories`.`background_color` AS `background_color`, 
        `categories`.`foreground_color` AS `foreground_color`, `categories`.`display_border_outline` AS `display_border` ";

        if ($staff) {
            $sql .= ",`reminders`.`amount` AS `reminder_value`, `reminders`.`unit` AS `reminder_unit` ";
        }

        $sql .= "FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "calendar_event` AS `events`
        INNER JOIN `" . hesk_dbEscape($heskSettings['db_pfix']) . "categories` AS `categories`
            ON `events`.`category` = `categories`.`id` ";

        if ($staff) {
            $sql .= "LEFT JOIN `" . hesk_dbEscape($heskSettings['db_pfix']) . "calendar_event_reminder` AS `reminders` ON
        `reminders`.`user_id` = " . intval($_SESSION['id']) . " AND `reminders`.`event_id` = `events`.`id`";
        }
        $sql .= "WHERE NOT (`end` < {$startTimeSql} OR `start` > {$endTimeSql}) AND `categories`.`usage` <> 1";

        if (!$staff) {
            $sql .= " AND `categories`.`type` = '0'";
        }

        $rs = hesk_dbQuery($sql);

        $events = array();
        while ($row = hesk_dbFetchAssoc($rs)) {
            // Skip the event if the user does not have access to it
            // TODO This should be business logic
            if ($staff && !$_SESSION['isadmin'] && !in_array($row['category'], $_SESSION['categories'])) {
                continue;
            }

            $event['type'] = 'CALENDAR';
            $event['id'] = intval($row['id']);
            $event['startTime'] = $row['start'];
            $event['endTime'] = $row['end'];
            $event['allDay'] = $row['all_day'] ? true : false;
            $event['title'] = $row['name'];
            $event['location'] = $row['location'];
            $event['comments'] = $row['comments'];
            $event['categoryId'] = $row['category'];
            $event['categoryName'] = $row['category_name'];
            $event['backgroundColor'] = $row['background_color'];
            $event['foregroundColor'] = $row['foreground_color'];
            $event['displayBorder'] = $row['display_border'];

            if ($staff) {
                $event['reminderValue'] = $row['reminder_value'];
                $event['reminderUnits'] = $row['reminder_unit'];
            }

            $events[] = $event;
        }

        if ($staff) {
            $oldTimeSetting = $heskSettings['timeformat'];
            $heskSettings['timeformat'] = 'Y-m-d';
            $currentDate = hesk_date();
            $heskSettings['timeformat'] = $oldTimeSetting;

            $sql = "SELECT `trackid`, `subject`, `due_date`, `category`, `categories`.`name` AS `category_name`, `categories`.`background_color` AS `background_color`, 
        `categories`.`foreground_color` AS `foreground_color`, `categories`.`display_border_outline` AS `display_border`,
          CASE WHEN `due_date` < '{$currentDate}' THEN 1 ELSE 0 END AS `overdue`, `owner`.`name` AS `owner_name`, `tickets`.`owner` AS `owner_id`,
           `tickets`.`priority` AS `priority`
        FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "tickets` AS `tickets`
        INNER JOIN `" . hesk_dbEscape($heskSettings['db_pfix']) . "categories` AS `categories`
            ON `categories`.`id` = `tickets`.`category`
            AND `categories`.`usage` <> 2
        LEFT JOIN `" . hesk_dbEscape($heskSettings['db_pfix']) . "users` AS `owner`
            ON `tickets`.`owner` = `owner`.`id`
        WHERE `due_date` >= CONVERT_TZ(FROM_UNIXTIME(" . hesk_dbEscape($startTime)
                . " / 1000), @@session.time_zone, '+00:00')
        AND `due_date` <= CONVERT_TZ(FROM_UNIXTIME(" . hesk_dbEscape($endTime) . " / 1000), @@session.time_zone, '+00:00')
        AND `status` IN (SELECT `id` FROM `" . hesk_dbEscape($heskSettings['db_pfix']) . "statuses` WHERE `IsClosed` = 0) ";

            $rs = hesk_dbQuery($sql);
            while ($row = hesk_dbFetchAssoc($rs)) {
                // Skip the ticket if the user does not have access to it
                if (!hesk_checkPermission('can_view_tickets', 0)
                    || ($row['owner_id'] && $row['owner_id'] != $_SESSION['id'] && !hesk_checkPermission('can_view_ass_others', 0))
                    || (!$row['owner_id'] && !hesk_checkPermission('can_view_unassigned', 0))) {
                    continue;
                }

                $event['type'] = 'TICKET';
                $event['trackingId'] = $row['trackid'];
                $event['subject'] = $row['subject'];
                $event['title'] = $row['subject'];
                $event['startTime'] = $row['due_date'];
                $event['url'] = $heskSettings['hesk_url'] . '/' . $heskSettings['admin_dir'] . '/admin_ticket.php?track=' . $event['trackingId'];
                $event['categoryId'] = $row['category'];
                $event['categoryName'] = $row['category_name'];
                $event['backgroundColor'] = $row['background_color'];
                $event['foregroundColor'] = $row['foreground_color'];
                $event['displayBorder'] = $row['display_border'];
                $event['owner'] = $row['owner_name'];
                $event['priority'] = $row['priority'];

                $events[] = $event;
            }
        }

        $this->close();

        return $events;
    }
}