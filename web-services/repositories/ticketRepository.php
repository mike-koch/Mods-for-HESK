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

        $sql = 'SELECT * FROM '.$settings['db_pfix'].'tickets WHERE id = '.$id;
        $results = $connection->query($sql);

        //-- There will only ever be one result, as ID is the primary key on the tickets table.
        $result = $results->fetch_assoc();

        $ticket = new Ticket();

        $ticket->id = $result['id'];
        $ticket->trackingId = $result['trackid'];
        $ticket->name = $result['name'];
        $ticket->email = $result['email'];
        $ticket->category = $result['category'];
        $ticket->priority = $result['priority'];
        $ticket->subject = $result['subject'];
        $ticket->message = $result['message'];
        $ticket->dateCreated = $result['dt'];
        $ticket->dateModified = $result['lastchange'];
        $ticket->ip = $result['ip'];
        $ticket->language = $result['language'];
        $ticket->status = $result['status'];
        $ticket->owner = $result['owner'];
        $ticket->timeWorked = $result['time_worked'];
        $ticket->lastReplier = $result['lastreplier'];
        $ticket->replierId = $result['replierid'];
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
}

?>
