<?php

namespace BusinessLogic\Tickets;


use BusinessLogic\Tickets\Exceptions\UnableToGenerateTrackingIdException;
use DataAccess\Tickets\TicketGateway;

class TrackingIdGenerator {
    /**
     * @var $ticketGateway TicketGateway
     */
    private $ticketGateway;

    function __construct($ticketGateway) {
        $this->ticketGateway = $ticketGateway;
    }

    /**
     * @param $heskSettings array
     * @return string
     * @throws UnableToGenerateTrackingIdException
     */
    function generateTrackingId($heskSettings) {
        $acceptableCharacters = 'AEUYBDGHJLMNPQRSTVWXZ123456789';

        /* Generate raw ID */
        $trackingId = '';

        /* Let's avoid duplicate ticket ID's, try up to 3 times */
        for ($i = 1; $i <= 3; $i++) {
            for ($i = 0; $i < 10; $i++) {
                $trackingId .= $acceptableCharacters[mt_rand(0, 29)];
            }

            $trackingId = $this->formatTrackingId($trackingId);

            /* Check for duplicate IDs */
            $goodId = !$this->ticketGateway->doesTicketExist($trackingId, $heskSettings);

            if ($goodId) {
                return $trackingId;
            }

            /* A duplicate ID has been found! Let's try again (up to 2 more) */
            $trackingId = '';
        }

        /* No valid tracking ID, try one more time with microtime() */
        $trackingId = $acceptableCharacters[mt_rand(0, 29)];
        $trackingId .= $acceptableCharacters[mt_rand(0, 29)];
        $trackingId .= $acceptableCharacters[mt_rand(0, 29)];
        $trackingId .= $acceptableCharacters[mt_rand(0, 29)];
        $trackingId .= $acceptableCharacters[mt_rand(0, 29)];
        $trackingId .= substr(microtime(), -5);

        /* Format the ID to the correct shape and check wording */
        $trackingId = $this->formatTrackingId($trackingId);

        $goodId = !$this->ticketGateway->doesTicketExist($trackingId, $heskSettings);

        if ($goodId) {
            return $trackingId;
        }

        throw new UnableToGenerateTrackingIdException();
    }

    /**
     * @param $id string
     * @return string
     */
    private function formatTrackingId($id) {
        $acceptableCharacters = 'AEUYBDGHJLMNPQRSTVWXZ123456789';

        $replace = $acceptableCharacters[mt_rand(0, 29)];
        $replace .= mt_rand(1, 9);
        $replace .= $acceptableCharacters[mt_rand(0, 29)];

        /*
        Remove 3 letter bad words from ID
        Possiblitiy: 1:27,000
        */
        $remove = array(
            'ASS',
            'CUM',
            'FAG',
            'FUK',
            'GAY',
            'SEX',
            'TIT',
            'XXX',
        );

        $id = str_replace($remove, $replace, $id);

        /*
        Remove 4 letter bad words from ID
        Possiblitiy: 1:810,000
        */
        $remove = array(
            'ANAL',
            'ANUS',
            'BUTT',
            'CAWK',
            'CLIT',
            'COCK',
            'CRAP',
            'CUNT',
            'DICK',
            'DYKE',
            'FART',
            'FUCK',
            'JAPS',
            'JERK',
            'JIZZ',
            'KNOB',
            'PISS',
            'POOP',
            'SHIT',
            'SLUT',
            'SUCK',
            'TURD',

            // Also, remove words that are known to trigger mod_security
            'WGET',
        );

        $replace .= mt_rand(1, 9);
        $id = str_replace($remove, $replace, $id);

        /* Format the ID string into XXX-XXX-XXXX format for easier readability */
        $id = $id[0] . $id[1] . $id[2] . '-' . $id[3] . $id[4] . $id[5] . '-' . $id[6] . $id[7] . $id[8] . $id[9];

        return $id;
    }
}