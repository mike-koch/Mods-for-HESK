<?php

namespace BusinessLogic\Categories;


use BusinessLogic\Exceptions\AccessViolationException;
use BusinessLogic\Exceptions\ValidationException;
use BusinessLogic\Navigation\Direction;
use BusinessLogic\Security\PermissionChecker;
use BusinessLogic\Security\UserPrivilege;
use BusinessLogic\ValidationModel;
use DataAccess\Categories\CategoryGateway;
use DataAccess\Settings\ModsForHeskSettingsGateway;
use DataAccess\Tickets\TicketGateway;

class CategoryHandler extends \BaseClass {
    /* @var $categoryGateway CategoryGateway */
    private $categoryGateway;

    /* @var $ticketGateway TicketGateway */
    private $ticketGateway;

    /* @var $permissionChecker PermissionChecker */
    private $permissionChecker;

    /* @var $modsForHeskSettingsGateway ModsForHeskSettingsGateway */
    private $modsForHeskSettingsGateway;

    function __construct(CategoryGateway $categoryGateway,
                         TicketGateway $ticketGateway,
                         PermissionChecker $permissionChecker,
                         ModsForHeskSettingsGateway $modsForHeskSettingsGateway) {
        $this->categoryGateway = $categoryGateway;
        $this->ticketGateway = $ticketGateway;
        $this->permissionChecker = $permissionChecker;
        $this->modsForHeskSettingsGateway = $modsForHeskSettingsGateway;
    }

    /**
     * @param $category Category
     * @param $userContext
     * @param $heskSettings array
     * @return Category The newly created category with ID
     * @throws ValidationException When validation fails
     * @throws \Exception When the newly created category was not retrieved
     */
    //TODO Test
    function createCategory($category, $userContext, $heskSettings) {
        $modsForHeskSettings = $this->modsForHeskSettingsGateway->getAllSettings($heskSettings);

        $validationModel = $this->validate($category, $userContext);

        if (count($validationModel->errorKeys) > 0) {
            throw new ValidationException($validationModel);
        }

        $id = $this->categoryGateway->createCategory($category, $heskSettings);

        $allCategories = $this->categoryGateway->getAllCategories($heskSettings, $modsForHeskSettings);

        foreach ($allCategories as $innerCategory) {
            if ($innerCategory->id === $id) {
                return $innerCategory;
            }
        }

        throw new \BaseException("Newly created category {$id} lost! :O");
    }

    /**
     * @param $category Category
     * @param $userContext
     * @param $creating bool
     * @return ValidationModel
     * @throws AccessViolationException
     */
    //TODO Test
    private function validate($category, $userContext, $creating = true) {
        $validationModel = new ValidationModel();

        if (!$this->permissionChecker->doesUserHavePermission($userContext, UserPrivilege::CAN_MANAGE_CATEGORIES)) {
            throw new AccessViolationException('User cannot manage categories!');
        }

        if (!$creating && $category->id < 1) {
            $validationModel->errorKeys[] = 'ID_MISSING';
        }

        if ($category->backgroundColor === null || trim($category->backgroundColor) === '') {
            $validationModel->errorKeys[] = 'BACKGROUND_COLOR_MISSING';
        }

        if ($category->foregroundColor === null || trim($category->foregroundColor) === '') {
            $validationModel->errorKeys[] = 'FOREGROUND_COLOR_MISSING';
        }

        if ($category->name === null || trim($category->name) === '') {
            $validationModel->errorKeys[] = 'NAME_MISSING';
        }

        if ($category->priority === null || intval($category->priority) < 0 || intval($category->priority) > 3) {
            $validationModel->errorKeys[] = 'INVALID_PRIORITY';
        }

        if ($category->autoAssign === null || !is_bool($category->autoAssign)) {
            $validationModel->errorKeys[] = 'INVALID_AUTOASSIGN';
        }

        if ($category->displayBorder === null || !is_bool($category->displayBorder)) {
            $validationModel->errorKeys[] = 'INVALID_DISPLAY_BORDER';
        }

        if ($category->type === null || (intval($category->type) !== 0 && intval($category->type) !== 1)) {
            $validationModel->errorKeys[] = 'INVALID_TYPE';
        }

        return $validationModel;
    }

    /**
     * @param $category Category
     * @param $userContext
     * @param $heskSettings array
     * @return Category
     * @throws ValidationException
     * @throws \Exception When the category is missing
     */
    function editCategory($category, $userContext, $heskSettings) {
        $modsForHeskSettings = $this->modsForHeskSettingsGateway->getAllSettings($heskSettings);

        $validationModel = $this->validate($category, $userContext, false);

        if (count($validationModel->errorKeys) > 0) {
            throw new ValidationException($validationModel);
        }

        $this->categoryGateway->updateCategory($category, $heskSettings);
        $this->categoryGateway->resortAllCategories($heskSettings);

        $allCategories = $this->categoryGateway->getAllCategories($heskSettings, $modsForHeskSettings);

        foreach ($allCategories as $innerCategory) {
            if ($innerCategory->id === $category->id) {
                return $innerCategory;
            }
        }

        throw new \BaseException("Category {$category->id} vanished! :O");
    }

    function deleteCategory($id, $userContext, $heskSettings) {
        if (!$this->permissionChecker->doesUserHavePermission($userContext, UserPrivilege::CAN_MANAGE_CATEGORIES)) {
            throw new AccessViolationException('User cannot manage categories!');
        }

        if ($id === 1) {
            throw new \BaseException("Category 1 cannot be deleted!");
        }

        $this->ticketGateway->moveTicketsToDefaultCategory($id, $heskSettings);
        $this->categoryGateway->deleteCategory($id, $heskSettings);
        $this->categoryGateway->resortAllCategories($heskSettings);
    }

    function sortCategory($id, $direction, $heskSettings) {
        $modsForHeskSettings = $this->modsForHeskSettingsGateway->getAllSettings($heskSettings);

        $categories = $this->categoryGateway->getAllCategories($heskSettings, $modsForHeskSettings);
        $category = null;
        foreach ($categories as $innerCategory) {
            if ($innerCategory->id === intval($id)) {
                $category = $innerCategory;
                break;
            }
        }

        if ($category === null) {
            throw new \BaseException("Could not find category with ID {$id}!");
        }

        if ($direction === Direction::UP) {
            $category->catOrder -= 15;
        } else {
            $category->catOrder += 15;
        }

        $this->categoryGateway->updateCategory($category, $heskSettings);
        $this->categoryGateway->resortAllCategories($heskSettings);
    }
}