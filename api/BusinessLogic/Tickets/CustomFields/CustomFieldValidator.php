<?php
/**
 * Created by PhpStorm.
 * User: cokoch
 * Date: 2/9/2017
 * Time: 12:28 PM
 */

namespace BusinessLogic\Tickets\CustomFields;


class CustomFieldValidator {
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