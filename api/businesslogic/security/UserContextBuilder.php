<?php

namespace BusinessLogic\Security;


use BusinessLogic\Exceptions\InvalidAuthenticationTokenException;
use BusinessLogic\Exceptions\MissingAuthenticationTokenException;
use BusinessLogic\Helpers;
use DataAccess\Security\UserGateway;

class UserContextBuilder {
    /**
     * @var UserGateway
     */
    private $userGateway;

    function __construct($userGateway) {
        $this->userGateway = $userGateway;
    }

    function buildUserContext($authToken, $heskSettings) {
        $NULL_OR_EMPTY_STRING = 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f2b0ff8318d2877eec2f63b931bd47417a81a538327af927da3e';

        $hashedToken = Helpers::hashToken($authToken);

        if ($hashedToken === $NULL_OR_EMPTY_STRING) {
            throw new MissingAuthenticationTokenException();
        }

        $userRow = $this->userGateway->getUserForAuthToken($hashedToken, $heskSettings);

        if ($userRow === null) {
            throw new InvalidAuthenticationTokenException();
        }

        return $this->fromDataRow($userRow);
    }

    /**
     * Builds a user context based on the current session. **The session must be active!**
     * @param $dataRow array the $_SESSION superglobal or the hesk_users result set
     * @return UserContext the built user context
     */
    function fromDataRow($dataRow) {
        $userContext = new UserContext();
        $userContext->id = $dataRow['id'];
        $userContext->username = $dataRow['user'];
        $userContext->admin = $dataRow['isadmin'];
        $userContext->name = $dataRow['name'];
        $userContext->email = $dataRow['email'];
        $userContext->signature = $dataRow['signature'];
        $userContext->language = $dataRow['language'];
        $userContext->categories = explode(',', $dataRow['categories']);
        $userContext->permissions = explode(',', $dataRow['heskprivileges']);
        $userContext->autoAssign = $dataRow['autoassign'];
        $userContext->ratingNegative = $dataRow['ratingneg'];
        $userContext->ratingPositive = $dataRow['ratingpos'];
        $userContext->rating = $dataRow['rating'];
        $userContext->totalNumberOfReplies = $dataRow['replies'];
        $userContext->active = $dataRow['active'];

        $preferences = new UserContextPreferences();
        $preferences->afterReply = $dataRow['afterreply'];
        $preferences->autoStartTimeWorked = $dataRow['autostart'];
        $preferences->autoreload = $dataRow['autoreload'];
        $preferences->defaultNotifyCustomerNewTicket = $dataRow['notify_customer_new'];
        $preferences->defaultNotifyCustomerReply = $dataRow['notify_customer_reply'];
        $preferences->showSuggestedKnowledgebaseArticles = $dataRow['show_suggested'];
        $preferences->defaultCalendarView = $dataRow['default_calendar_view'];
        $preferences->defaultTicketView = $dataRow['default_list'];
        $userContext->preferences = $preferences;

        $notifications = new UserContextNotifications();
        $notifications->newUnassigned = $dataRow['notify_new_unassigned'];
        $notifications->newAssignedToMe = $dataRow['notify_new_my'];
        $notifications->replyUnassigned = $dataRow['notify_reply_unassigned'];
        $notifications->replyToMe = $dataRow['notify_reply_my'];
        $notifications->ticketAssignedToMe = $dataRow['notify_assigned'];
        $notifications->privateMessage = $dataRow['notify_pm'];
        $notifications->noteOnTicketAssignedToMe = $dataRow['notify_note'];
        $notifications->noteOnTicketNotAssignedToMe = $dataRow['notify_note_unassigned'];
        $notifications->overdueTicketUnassigned = $dataRow['notify_overdue_unassigned'];
        $userContext->notificationSettings = $notifications;

        return $userContext;
    }
}