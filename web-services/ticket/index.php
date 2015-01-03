<?php

//-- This service will return ticket information for a specific ticket ID (NOT TRACKING ID)
header('Content-Type: application/json');
define('IN_SCRIPT',1);
define('HESK_PATH','../../');

include(HESK_PATH . 'hesk_settings.inc.php');
include(__DIR__ . '/../repositories/ticketRepository.php');

if(isset($_GET['id']))
{
    $ticket = TicketRepository::getTicketForId($_GET['id'], $hesk_settings);
    echo json_encode($ticket);
}
else
{
    header(http_response_code(400));
}
