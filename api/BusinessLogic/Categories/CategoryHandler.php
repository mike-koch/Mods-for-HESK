<?php

namespace BusinessLogic\Categories;


use BusinessLogic\Exceptions\ValidationException;
use BusinessLogic\ValidationModel;
use DataAccess\Categories\CategoryGateway;

class CategoryHandler {
    /* @var $categoryGateway CategoryGateway */
    private $categoryGateway;

    function __construct($categoryGateway) {
        $this->categoryGateway = $categoryGateway;
    }

    /**
     * @param $category Category
     * @param $heskSettings array
     * @return Category The newly created category with ID
     * @throws ValidationException When validation fails
     */
    //TODO Test
    function createCategory($category, $heskSettings) {
        $validationModel = $this->validate($category, $heskSettings);

        if (count($validationModel->errorKeys) > 0) {
            throw new ValidationException($validationModel);
        }

        $category->id = $this->categoryGateway->createCategory($category, $heskSettings);

        return $category;
    }

    /**
     * @param $category Category
     * @param $heskSettings array
     * @param $creating bool
     * @return ValidationModel
     */
    //TODO Test
    private function validate($category, $heskSettings, $creating = true) {
        $validationModel = new ValidationModel();
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

        return $validationModel;
    }

    function editCategory($category, $heskSettings) {


    }
}