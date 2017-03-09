<?php

namespace DataAccess\Entities;


class User extends BaseEntity {
    protected static $table = 'users';

    public static function fields() {
        //@formatter:off
        return [
            'id'                        => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'user'                      => ['type' => 'string', 'required' => true, 'default' => ''],
            'pass'                      => ['type' => 'string', 'required' => true],
            'isadmin'                   => ['type' => 'string', 'required' => true, 'default' => '0'],
            'name'                      => ['type' => 'string', 'required' => true, 'default' => ''],
            'email'                     => ['type' => 'string', 'required' => true, 'default' => ''],
            'signature'                 => ['type' => 'string', 'required' => true, 'default' => ''],
            'language'                  => ['type' => 'string', 'required' => false],
            'categories'                => ['type' => 'string', 'required' => true, 'default' => ''],
            'afterreply'                => ['type' => 'string', 'required' => true, 'default' => '0'],
            'autostart'                 => ['type' => 'string', 'required' => true, 'default' => '1'],
            'autoreload'                => ['type' => 'smallint', 'required' => true, 'default' => 0],
            'notify_customer_new'       => ['type' => 'string', 'required' => true, 'default' => '1'],
            'notify_customer_reply'     => ['type' => 'string', 'required' => true, 'default' => '1'],
            'show_suggested'            => ['type' => 'string', 'required' => true, 'default' => '1'],
            'notify_new_unassigned'     => ['type' => 'string', 'required' => true, 'default' => '1'],
            'notify_new_my'             => ['type' => 'string', 'required' => true, 'default' => '1'],
            'notify_reply_unassigned'   => ['type' => 'string', 'required' => true, 'default' => '1'],
            'notify_reply_my'           => ['type' => 'string', 'required' => true, 'default' => '1'],
            'notify_assigned'           => ['type' => 'string', 'required' => true, 'default' => '1'],
            'notify_pm'                 => ['type' => 'string', 'required' => true, 'default' => '1'],
            'notify_note'               => ['type' => 'string', 'required' => true, 'default' => '1'],
            'notify_note_unassigned'    => ['type' => 'string', 'required' => false, 'default' => '0'],
            'default_calendar_view'     => ['type' => 'integer', 'required' => true, 'default' => '0'],
            'notify_overdue_unassigned' => ['type' => 'string', 'required' => true, 'default' => '0'],
            'default_list'              => ['type' => 'string', 'required' => true, 'default' => ''],
            'autoassign'                => ['type' => 'string', 'required' => true, 'default' => '1'],
            'heskprivileges'            => ['type' => 'string', 'required' => false],
            'ratingneg'                 => ['type' => 'integer', 'required' => true, 'default' => 0],
            'ratingpos'                 => ['type' => 'integer', 'required' => true, 'default' => 0],
            'rating'                    => ['type' => 'float', 'required' => true, 'default' => 0],
            'replies'                   => ['type' => 'integer', 'required' => true, 'default' => 0],
            'active'                    => ['type' => 'string', 'required' => true, 'default' => '1'],
            'permission_template'       => ['type' => 'integer', 'required' => false]
        ];
        //@formatter:on
    }
}