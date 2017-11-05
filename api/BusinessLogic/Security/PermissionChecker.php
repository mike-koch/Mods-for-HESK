<?php

namespace BusinessLogic\Security;


class PermissionChecker extends \BaseClass {
    /**
     * @param $userContext UserContext
     * @param $permission string
     * @return bool
     */
    function doesUserHavePermission($userContext, $permission) {
        if ($userContext->admin) {
            return true;
        }

        if (in_array($permission, $userContext->permissions)) {
            return true;
        }

        return false;
    }
}