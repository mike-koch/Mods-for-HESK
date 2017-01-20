<?php

namespace BusinessLogic\Security;


class UserContext {
    public $id;
    public $username;
    public $admin;
    public $name;
    public $email;
    public $signature;
    public $language;
    public $categories;
    public $permissions;

    /**
     * @var UserContextPreferences
     */
    public $preferences;

    /**
     * @var UserContextNotifications
     */
    public $notificationSettings;
    public $autoAssign;
    public $ratingNegative;
    public $ratingPositive;
    public $rating;
    public $totalNumberOfReplies;
    public $active;
}