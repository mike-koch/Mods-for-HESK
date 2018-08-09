<?php

namespace BusinessLogic\Categories;


use BusinessLogic\Security\UserContext;
use BusinessLogic\Security\UserPrivilege;
use DataAccess\Categories\CategoryGroupGateway;

class CategoryGroupHandler extends \BaseClass {
    private $categoryGroupGateway;

    public function __construct(CategoryGroupGateway $categoryGateway) {
        $this->categoryGroupGateway = $categoryGateway;
    }

    public function createCategory(CategoryGroup $categoryGroup,
                                   UserContext $userContext,
                                   $heskSettings) {
        if (!$userContext->admin && !in_array(UserPrivilege::CAN_MANAGE_CATEGORIES, $userContext->permissions)) {
            throw new \Exception("User {$userContext->id} does not have permission to create category groups!");
        }

        return $this->categoryGroupGateway->createCategoryGroup($heskSettings, $categoryGroup);
    }

    public function updateCategorySortAndParent($id, $sort, $parent, $heskSettings) {
        $this->categoryGroupGateway->updateCategorySortAndParent(intval($id), intval($sort), $parent, $heskSettings);
    }
}