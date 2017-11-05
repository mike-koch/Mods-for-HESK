<?php

namespace BusinessLogic\Tickets\CustomFields;


class CustomFieldValidator extends \BaseClass {
    static function isCustomFieldInCategory($customFieldId, $categoryId, $staff, $heskSettings) {
        $customField = $heskSettings['custom_fields']["custom{$customFieldId}"];

        if (!$customField['use'] ||
            (!$staff && $customField['use'] === 2)) {
            return false;
        }

        return count($customField['category']) === 0 ||
            in_array($categoryId, $customField['category']);
    }
}