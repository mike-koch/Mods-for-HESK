<?php

namespace Controllers\Categories;


use BusinessLogic\Categories\CategoryGroupRetriever;
use BusinessLogic\Exceptions\ApiFriendlyException;

class CategoryGroupController extends \BaseClass {
    public static function getAll() {
        return output(array());
    }

    private static function getAllCategoryGroups() {
        global $hesk_settings, $applicationContext, $userContext;

        /* @var $categoryGroupRetriever CategoryGroupRetriever */
        $categoryGroupRetriever = $applicationContext->get(CategoryGroupRetriever::clazz());

        return $categoryGroupRetriever->getAllCategoryGroups($hesk_settings, $userContext);
    }

    public function get($id) {
        $categoryGroups = self::getAllCategoryGroups();

        foreach ($categoryGroups as $categoryGroup) {
            if ($categoryGroup->id === $id) {
                return output($categoryGroup);
            }
        }

        throw new ApiFriendlyException("Category group {$id} not found!", "Category Group Not Found", 404);
    }

    public function post() {

    }

    public function put() {

    }

    public function delete($id) {

    }
}