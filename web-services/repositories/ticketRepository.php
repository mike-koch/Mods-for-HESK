<?php

require('/../models/ticket.php');

class TicketRepository {

    private function __construct() {
    }

    public static function getTicketForId($id, $settings) {

        $connection = new mysqli($settings['db_host'], $settings['db_user'], $settings['db_pass'], $settings['db_name']);
        if ($connection->connect_error)
        {
            return ('An error occured when establishing a connection to the database.');
        }

        $sql = 'SELECT T.id, '.
            'T.trackid, '.
            'T.name AS "ContactName", '.
            'T.email, '.
            'T.category, '.
            'T.priority, '.
            'T.subject, '.
            'T.message, '.
            'T.dt, '.
            'T.lastchange, '.
            'T.ip, '.
            'T.language, '.
            'T.status, '.
            'T.owner, '.
            'T.time_worked, '.
            'T.lastreplier, '.
            'T.replierid, '.
            'T.archive, '.
            'T.locked, '.
            'T.attachments, '.
            'T.merged, '.
            'T.custom1, '.
            'T.custom2, '.
            'T.custom3, '.
            'T.custom4, '.
            'T.custom5, '.
            'T.custom6, '.
            'T.custom7, '.
            'T.custom8, '.
            'T.custom9, '.
            'T.custom10, '.
            'T.custom11, '.
            'T.custom12, '.
            'T.custom13, '.
            'T.custom14, '.
            'T.custom15, '.
            'T.custom16, '.
            'T.custom17, '.
            'T.custom18, '.
            'T.custom19, '.
            'T.custom20 '.
            'FROM '.$settings['db_pfix'].'tickets T '.
            'WHERE T.id = '.$id;
        $results = $connection->query($sql);

        //-- There will only ever be one result, as ID is the primary key on the tickets table.
        $result = $results->fetch_assoc();

        $ticket = new Ticket();

        settype($result['id'], 'int');
        $ticket->id = $result['id'];

        $ticket->trackingId = $result['trackid'];
        $ticket->name = $result['ContactName'];
        $ticket->email = $result['email'];

        settype($result['category'], 'int');
        $ticket->category = $result['category'];

        settype($result['priority'], 'int');
        $ticket->priority = $result['priority'];

        $ticket->subject = $result['subject'];
        $ticket->message = $result['message'];

        //-- Convert these to times so the receiver can use whatever format they want to display the date/time without extra work.
        $ticket->dateCreated = strtotime($result['dt']);
        $ticket->dateModified = strtotime($result['lastchange']);
        $ticket->ip = $result['ip'];
        $ticket->language = $result['language'];

        settype($result['status'], 'int');
        $ticket->status = $result['status'];

        settype($result['owner'], 'int');
        $ticket->owner = $result['owner'];

        $ticket->timeWorked = $result['time_worked'];

        settype($result['lastreplier'], 'int');
        $ticket->lastReplier = $result['lastreplier'];

        settype($result['replierid'], 'int');
        $ticket->replierId = $result['replierid'];
        $ticket->isArchived = ($result['archive'] ? true : false);
        $ticket->isLocked = ($result['locked'] ? true : false);
        $ticket->attachments = $result['attachments'];

        //-- explode handles splitting the list into an array, array_filter removes the empty string elements (""), and array_values resets the indicies.
        $ticket->merged = array_values(array_filter(explode('#',$result['merged'])));

        //-- Not currently returning history, as it can contain a metric shit-ton of HTML code and will cludder up the JSON response.
        //$ticket->history = $result['history'];
        $ticket->custom1 = $result['custom1'] == '' ? null : $result['custom1'];
        $ticket->custom2 = $result['custom2'] == '' ? null : $result['custom2'];
        $ticket->custom3 = $result['custom3'] == '' ? null : $result['custom3'];
        $ticket->custom4 = $result['custom4'] == '' ? null : $result['custom4'];
        $ticket->custom5 = $result['custom5'] == '' ? null : $result['custom5'];
        $ticket->custom6 = $result['custom6'] == '' ? null : $result['custom6'];
        $ticket->custom7 = $result['custom7'] == '' ? null : $result['custom7'];
        $ticket->custom8 = $result['custom8'] == '' ? null : $result['custom8'];
        $ticket->custom9 = $result['custom9'] == '' ? null : $result['custom9'];
        $ticket->custom10 = $result['custom10'] == '' ? null : $result['custom10'];
        $ticket->custom11 = $result['custom11'] == '' ? null : $result['custom11'];
        $ticket->custom12 = $result['custom12'] == '' ? null : $result['custom12'];
        $ticket->custom13 = $result['custom13'] == '' ? null : $result['custom13'];
        $ticket->custom14 = $result['custom14'] == '' ? null : $result['custom14'];
        $ticket->custom15 = $result['custom15'] == '' ? null : $result['custom15'];
        $ticket->custom16 = $result['custom16'] == '' ? null : $result['custom16'];
        $ticket->custom17 = $result['custom17'] == '' ? null : $result['custom17'];
        $ticket->custom18 = $result['custom18'] == '' ? null : $result['custom18'];
        $ticket->custom19 = $result['custom19'] == '' ? null : $result['custom19'];
        $ticket->custom20 = $result['custom20'] == '' ? null : $result['custom20'];

        return $ticket;
    }
}
