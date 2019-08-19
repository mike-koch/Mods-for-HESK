<?php

namespace BusinessLogic\Categories;


use BusinessLogic\Exceptions\AccessViolationException;
use BusinessLogic\Exceptions\ApiFriendlyException;
use BusinessLogic\Exceptions\ValidationException;
use BusinessLogic\Security\UserContext;
use BusinessLogic\Security\UserPrivilege;
use BusinessLogic\ValidationModel;
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
            throw new AccessViolationException("User {$userContext->id} does not have permission to create category groups!");
        }

        if ($categoryGroup->names === null || count($categoryGroup->names) === 0) {
            $validationModel = new ValidationModel();
            $validationModel->errorKeys[] = "NAME_REQUIRED";
            throw new ValidationException($validationModel);
        }

        return $this->categoryGroupGateway->createCategoryGroup($heskSettings, $categoryGroup);
    }

    public function updateCategorySortAndParent($id, $sort, $parent, $heskSettings) {
        $this->categoryGroupGateway->updateCategorySortAndParent(intval($id), intval($sort), $parent, $heskSettings);
    }

    public function updateCategory(CategoryGroup $categoryGroup,
                                   UserContext $userContext,
                                   $heskSettings) {
        if (!$userContext->admin && !in_array(UserPrivilege::CAN_MANAGE_CATEGORIES, $userContext->permissions)) {
            throw new \Exception("User {$userContext->id} does not have permission to update category groups!");
        }

        $this->categoryGroupGateway->updateCategoryGroup($heskSettings, $categoryGroup);

        $categoryGroups = $this->categoryGroupGateway->getAllCategoryGroups($heskSettings);
        foreach ($categoryGroups as $innerCategoryGroup) {
            /* @var $innerCategoryGroup CategoryGroup */
            if ($innerCategoryGroup->id === $categoryGroup->id) {
                return $innerCategoryGroup;
            }
        }

        // Huh? Just return what we're given I guess
        return $categoryGroup;
    }

    public function deleteCategoryGroup($id, $userContext, $heskSettings) {
        if (!$userContext->admin && !in_array(UserPrivilege::CAN_MANAGE_CATEGORIES, $userContext->permissions)) {
            throw new AccessViolationException("User {$userContext->id} does not have permission to delete category groups!");
        }

        if (!$this->categoryGroupGateway->doesCategoryGroupExist($id, $heskSettings)) {
            throw new ApiFriendlyException("Category group {$id} not found.", "Category Group Not Found", 404);
        }

        $this->categoryGroupGateway->moveCategoriesToParentsParent($id, $heskSettings);
        $this->categoryGroupGateway->deleteCategoryGroup($id, $heskSettings);
    }
}