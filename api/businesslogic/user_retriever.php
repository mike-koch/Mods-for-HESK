<?php
require_once(API_PATH . 'dao/user_dao.php');

function retrieve_user($hesk_settings, $id = NULL) {
    $users = get_user($hesk_settings, $id);

    if ($id === NULL) {
        $original_users = $users;
        $users = array();
        foreach ($original_users as $user) {
            $user = remove_unneeded_properties($user);
            $user = convert_to_camel_case($user);
            $users[] = $user;
        }
    } else {
        $users = remove_unneeded_properties($users);
        $users = convert_to_camel_case($users);
    }

    return $users;
}

function remove_unneeded_properties($user) {
    unset($user['pass']);
    unset($user['permission_template']);
    unset($user['language']);
    unset($user['replies']);

    return $user;
}

function convert_to_camel_case($user) {
    $user['username'] = $user['user'];
    unset($user['user']);
    $user['admin'] = $user['isadmin'];
    unset($user['isadmin']);
    $user['afterReply'] = $user['afterreply'];
    unset($user['afterreply']);
    $user['autoStart'] = $user['autostart'];
    unset($user['autostart']);
    $user['notifyCustomerNew'] = $user['notify_customer_new'];
    unset($user['notify_customer_new']);
    $user['notifyCustomerReply'] = $user['notify_customer_reply'];
    unset($user['notify_customer_reply']);
    $user['showSuggested'] = $user['show_suggested'];
    unset($user['show_suggested']);
    $user['notifyNewUnassigned'] = $user['notify_new_unassigned'];
    unset($user['notify_new_unassigned']);
    $user['notifyNewMy'] = $user['notify_new_my'];
    unset($user['notify_new_my']);
    $user['notifyReplyUnassigned'] = $user['notify_reply_unassigned'];
    unset($user['notify_reply_unassigned']);
    $user['notifyReplyMy'] = $user['notify_reply_my'];
    unset($user['notify_reply_my']);
    $user['notifyAssigned'] = $user['notify_assigned'];
    unset($user['notify_assigned']);
    $user['notifyPm'] = $user['notify_pm'];
    unset($user['notify_pm']);
    $user['notifyNote'] = $user['notify_note'];
    unset($user['notify_note']);
    $user['notifyNoteUnassigned'] = $user['notify_note_unassigned'];
    unset($user['notify_note_unassigned']);
    $user['defaultList'] = $user['default_list'];
    unset($user['default_list']);
    $user['ratingNeg'] = $user['ratingneg'];
    unset($user['ratingneg']);
    $user['ratingPos'] = $user['ratingpos'];
    unset($user['ratingpos']);
    $user['heskPrivileges'] = $user['heskprivileges'];
    unset($user['heskprivileges']);
    $user['defaultCalendarView'] = $user['default_calendar_view'];
    unset($user['default_calendar_view']);
    $user['notifyOverdueUnassigned'] = $user['notify_overdue_unassigned'];
    unset($user['notify_overdue_unassigned']);

    return $user;
}