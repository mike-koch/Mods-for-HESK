<?php

namespace BusinessLogic\Security;


class UserContext {
    /* @var $id int */
    public $id;

    /* @var $username string */
    public $username;

    /* @var $admin bool */
    public $admin;

    /* @var $name string */
    public $name;

    /* @var $email string */
    public $email;

    /* @var $signature string */
    public $signature;

    /* @var $language string|null */
    public $language;

    /* @var $categories int[] */
    public $categories;

    /* @var $permissions string[] */
    public $permissions;

    /* @var UserContextPreferences */
    public $preferences;

    /* @var UserContextNotifications */
    public $notificationSettings;

    /* @var $autoAssign bool */
    public $autoAssign;

    /* @var $ratingNegative int */
    public $ratingNegative;

    /* @var $ratingPositive int */
    public $ratingPositive;

    /* @var $rating float */
    public $rating;

    /* @var $totalNumberOfReplies int */
    public $totalNumberOfReplies;

    /* @var $active bool */
    public $active;

    /**
     * Builds a user context based on the current session. **The session must be active!**
     * @param $dataRow array the $_SESSION superglobal or the hesk_users result set
     * @return UserContext the built user context
     */
    static function fromDataRow($dataRow) {
        var_dump($dataRow);
        $userContext = new UserContext();
        $userContext->id = intval($dataRow['id']);
        $userContext->username = $dataRow['user'];
        $userContext->admin = boolval($dataRow['isadmin']);
        $userContext->name = $dataRow['name'];
        $userContext->email = $dataRow['email'];
        $userContext->signature = $dataRow['signature'];
        $userContext->language = $dataRow['language'];
        $userContext->categories = explode(',', $dataRow['categories']);
        $userContext->permissions = explode(',', $dataRow['heskprivileges']);
        $userContext->autoAssign = boolval($dataRow['autoassign']);
        $userContext->ratingNegative = intval($dataRow['ratingneg']);
        $userContext->ratingPositive = intval($dataRow['ratingpos']);
        $userContext->rating = floatval($dataRow['rating']);
        $userContext->totalNumberOfReplies = intval($dataRow['replies']);
        $userContext->active = boolval($dataRow['active']);

        $preferences = new UserContextPreferences();
        $preferences->afterReply = intval($dataRow['afterreply']);
        $preferences->autoStartTimeWorked = boolval($dataRow['autostart']);
        $preferences->autoreload = intval($dataRow['autoreload']);
        $preferences->defaultNotifyCustomerNewTicket = boolval($dataRow['notify_customer_new']);
        $preferences->defaultNotifyCustomerReply = boolval($dataRow['notify_customer_reply']);
        $preferences->showSuggestedKnowledgebaseArticles = boolval($dataRow['show_suggested']);
        $preferences->defaultCalendarView = intval($dataRow['default_calendar_view']);
        $preferences->defaultTicketView = $dataRow['default_list'];
        $userContext->preferences = $preferences;

        $notifications = new UserContextNotifications();
        $notifications->newUnassigned = boolval($dataRow['notify_new_unassigned']);
        $notifications->newAssignedToMe = boolval($dataRow['notify_new_my']);
        $notifications->replyUnassigned = boolval($dataRow['notify_reply_unassigned']);
        $notifications->replyToMe = boolval($dataRow['notify_reply_my']);
        $notifications->ticketAssignedToMe = boolval($dataRow['notify_assigned']);
        $notifications->privateMessage = boolval($dataRow['notify_pm']);
        $notifications->noteOnTicketAssignedToMe = boolval($dataRow['notify_note']);
        $notifications->noteOnTicketNotAssignedToMe = boolval($dataRow['notify_note_unassigned']);
        $notifications->overdueTicketUnassigned = boolval($dataRow['notify_overdue_unassigned']);
        $userContext->notificationSettings = $notifications;

        return $userContext;
    }
}