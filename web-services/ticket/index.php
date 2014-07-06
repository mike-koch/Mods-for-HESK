<?php

//-- This service will return ticket information for a specific ticket ID (NOT TRACKING ID)
header('Content-Type: application/json');
define('IN_SCRIPT',1);
define('HESK_PATH','/../../');

require(HESK_PATH . 'hesk_settings.inc.php');
include('/../repositories/ticketRepository.php');

if(isset($_GET['id']))
{

    $ticketRepository = TicketRepository::getInstance();
    $ticket = $ticketRepository->getTicketForId($_GET['id'], $hesk_settings);
    //--A quick and dirty RESTful test using PHP.
    echo json_encode($ticket);
}
else
{
    header(http_response_code(400));
}

?>
