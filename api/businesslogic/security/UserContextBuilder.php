<?php

namespace BusinessLogic\Security;


class UserContextBuilder {
    static function buildUserContext($authToken, $hesk_settings) {
        //$userForToken = gateway.getUserForToken($authToken);

    }

    /**
     * Builds a user context based on the current session. **The session must be active!**
     * @return UserContext the built user context
     */
    static function fromSession() {
        require_once(__DIR__ . '/UserContext.php');
        require_once(__DIR__ . '/UserContextPreferences.php');

        $userContext = new UserContext();
        $userContext->id = $_SESSION['id'];
        $userContext->username = $_SESSION['user'];
        $userContext->admin = $_SESSION['isadmin'];
        $userContext->name = $_SESSION['name'];
        $userContext->email = $_SESSION['email'];
        $userContext->signature = $_SESSION['signature'];
        $userContext->language = $_SESSION['language'];
        $userContext->categories = explode(',', $_SESSION['categories']);

        $preferences = new UserContextPreferences();
        $preferences->afterReply = $_SESSION['afterreply'];
        $preferences->autoStartTimeWorked = $_SESSION['autostart'];
        $preferences-


        return $userContext;
    }
}