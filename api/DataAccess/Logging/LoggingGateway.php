<?php

namespace DataAccess\Logging;


use BusinessLogic\Security\UserContext;
use DataAccess\CommonDao;

class LoggingGateway extends CommonDao {
    function logDebug($location, $message, $stackTrace, $userContext, $heskSettings) {
        return $this->log(Severity::DEBUG, $location, $message, $stackTrace, $userContext, $heskSettings);
    }

    function logInfo($location, $message, $stackTrace, $userContext, $heskSettings) {
        return $this->log(Severity::INFO, $location, $message, $stackTrace, $userContext, $heskSettings);
    }

    function logWarning($location, $message, $stackTrace, $userContext, $heskSettings) {
        return $this->log(Severity::WARNING, $location, $message, $stackTrace, $userContext, $heskSettings);
    }

    function logError($location, $message, $stackTrace, $userContext, $heskSettings) {
        return $this->log(Severity::ERROR, $location, $message, $stackTrace, $userContext, $heskSettings);
    }

    /**
     * @param $severity int (from Severity)
     * @param $location string
     * @param $message string
     * @param $userContext UserContext
     * @param $heskSettings array
     * @return int|null|string The inserted ID, or null on failure.
     */
    private function log($severity, $location, $message, $stackTrace, $userContext, $heskSettings) {
        $this->init();

        hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($heskSettings['db_pfix']) . "logging` (`username`, `message`, `severity`, `location`, `timestamp`, `stack_trace`)
        VALUES ('" . hesk_dbEscape($userContext->username) . "',
        '" . hesk_dbEscape($message) . "', " . intval($severity) . ", '" . hesk_dbEscape($location) . "', NOW(), '" . hesk_dbEscape($stackTrace) . "')");

        $insertedId = hesk_dbInsertID();

        $this->close();

        return $insertedId;
    }
}