<?php

require('/../models/ticket.php');

class TicketRepository {

    private function __construct() {
    }

    public static function getInstance() {
        static $instance = null;
        if ($instance == null)
        {
            $instance = new TicketRepository();
        }
        return $instance;
    }

    public function getTicketForId($id, $settings) {

        $connection = new mysqli($settings['db_host'], $settings['db_user'], $settings['db_pass'], $settings['db_name']);
        if ($connection->connect_error)
        {
            return ('An error occured when establishing a connection to the database.');
        }

        $sql = 'SELECT T.id, '.
            'T.trackid, '.
            'T.name AS "ContactName", '.
            'T.email, '.
            'C.name AS "CategoryName", '.
            'T.priority, '.
            'T.subject, '.
            'T.message, '.
            'T.dt, '.
            'T.lastchange, '.
            'T.ip, '.
            'T.language, '.
            'T.status, '.
            'U.name AS "Owner", '.
            'T.time_worked, '.
            'T.lastreplier, '.
            'U2.name AS "LastReplierName", '.
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
            'INNER JOIN '.$settings['db_pfix'].'categories C ON C.id = T.category '.
            'LEFT JOIN '.$settings['db_pfix'].'users U ON U.id = T.owner '.
            'LEFT JOIN '.$settings['db_pfix'].'users U2 ON U2.id = T.replierid '.
            'WHERE T.id = '.$id;
        $results = $connection->query($sql);

        //-- There will only ever be one result, as ID is the primary key on the tickets table.
        $result = $results->fetch_assoc();

        $ticket = new Ticket();

        $ticket->id = $result['id'];
        $ticket->trackingId = $result['trackid'];
        $ticket->name = $result['ContactName'];
        $ticket->email = $result['email'];
        $ticket->category = $result['CategoryName'];
        $ticket->priority = self::getPriorityForId($result['priority']);
        $ticket->subject = $result['subject'];
        $ticket->message = $result['message'];
        $ticket->dateCreated = $result['dt'];
        $ticket->dateModified = $result['lastchange'];
        $ticket->ip = $result['ip'];
        $ticket->language = $result['language'];
        $ticket->status = self::getStatusForId($result['status']);
        $ticket->owner = $result['Owner'];
        $ticket->timeWorked = $result['time_worked'];
        $ticket->lastReplier = self::getWhoLastRepliedForId($result['lastreplier']);
        $ticket->replierId = $result['LastReplierName'];
        $ticket->isArchived = $result['archive'];
        $ticket->isLocked = $result['locked'];
        $ticket->attachments = $result['attachments'];
        $ticket->merged = $result['merged'];

        //-- Not currently returning history, as it can contain a metric shit-ton of HTML code and will cludder up the JSON response.
        //$ticket->history = $result['history'];
        $ticket->custom1 = $result['custom1'];
        $ticket->custom2 = $result['custom2'];
        $ticket->custom3 = $result['custom3'];
        $ticket->custom4 = $result['custom4'];
        $ticket->custom5 = $result['custom5'];
        $ticket->custom6 = $result['custom6'];
        $ticket->custom7 = $result['custom7'];
        $ticket->custom8 = $result['custom8'];
        $ticket->custom9 = $result['custom9'];
        $ticket->custom10 = $result['custom10'];
        $ticket->custom11 = $result['custom11'];
        $ticket->custom12 = $result['custom12'];
        $ticket->custom13 = $result['custom13'];
        $ticket->custom14 = $result['custom14'];
        $ticket->custom15 = $result['custom15'];
        $ticket->custom16 = $result['custom16'];
        $ticket->custom17 = $result['custom17'];
        $ticket->custom18 = $result['custom18'];
        $ticket->custom19 = $result['custom19'];
        $ticket->custom20 = $result['custom20'];

        return $ticket;
    }
    
    private function getPriorityForId($id) {
        if ($id == 0){
            return '* Critical *';
        } elseif ($id == 1){
            return 'High';
        } elseif ($id == 2){
            return 'Medium';
        } elseif ($id == 3){
            return 'Low';
        }
    }
    
    private function getStatusForId($id) {
        if ($id == 0) {
            return 'New';
        } elseif ($id == 1) {
            return 'Waiting Reply';
        } elseif ($id == 2) {
            return 'Replied';
        } elseif ($id == 3) {
            return 'Resolved';
        } elseif ($id == 4) {
            return 'In Progress';
        } elseif ($id == 5) {
            return 'On Hold';
        }
    }
    
    private function getWhoLastRepliedForId($id) {
        return ($id == 0 ? 'Contact' : 'Staff');
    }
}

?>
