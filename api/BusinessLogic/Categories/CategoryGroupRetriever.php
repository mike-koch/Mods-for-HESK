<?php

namespace BusinessLogic\Categories;


use BusinessLogic\Exceptions\ApiFriendlyException;
use BusinessLogic\Security\UserContext;
use BusinessLogic\Security\UserPrivilege;
use DataAccess\Categories\CategoryGroupGateway;

class CategoryGroupRetriever extends \BaseClass {
    /**
     * @var CategoryGroupGateway
     */
    private $categoryGroupGateway;

    public function __construct(CategoryGroupGateway $categoryGroupGateway) {
        $this->categoryGroupGateway = $categoryGroupGateway;
    }

    public function getAllCategoryGroups(array $heskSettings, UserContext $userContext) {
        if (!in_array(UserPrivilege::CAN_MANAGE_CATEGORIES, $userContext->permissions)) {
            throw new \Exception("User {$userContext->id} does not have permission to manage categories!");
        }

        return $this->categoryGroupGateway->getAllCategoryGroups($heskSettings);
    }
}