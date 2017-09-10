<?php

namespace Controllers\Categories;

use BusinessLogic\Categories\Category;
use BusinessLogic\Categories\CategoryHandler;
use BusinessLogic\Categories\CategoryRetriever;
use BusinessLogic\Exceptions\ApiFriendlyException;
use BusinessLogic\Helpers;
use Controllers\JsonRetriever;

class CategoryController {
    function get($id) {
        $categories = self::getAllCategories();

        foreach ($categories as $category) {
            if ($category->id === $id) {
                return output($category);
            }
        }

        throw new ApiFriendlyException("Category {$id} not found!", "Category Not Found", 404);
    }

    static function printAllCategories() {
        output(self::getAllCategories());
    }

    private static function getAllCategories() {
        global $hesk_settings, $applicationContext, $userContext;

        /* @var $categoryRetriever CategoryRetriever */
        $categoryRetriever = $applicationContext->get(CategoryRetriever::class);

        return $categoryRetriever->getAllCategories($hesk_settings, $userContext);
    }

    function post() {
        global $hesk_settings, $userContext, $applicationContext;

        $data = JsonRetriever::getJsonData();

        $category = $this->buildCategoryFromJson($data);

        /* @var $categoryHandler CategoryHandler */
        $categoryHandler = $applicationContext->get(CategoryHandler::class);

        $category = $categoryHandler->createCategory($category, $userContext, $hesk_settings);

        return output($category, 201);
    }

    /**
     * @param $json
     * @return Category
     */
    private function buildCategoryFromJson($json) {
        $category = new Category();

        $category->autoAssign = Helpers::safeArrayGet($json, 'autoassign');
        $category->backgroundColor = Helpers::safeArrayGet($json, 'backgroundColor');
        $category->catOrder = Helpers::safeArrayGet($json, 'catOrder');
        $category->description = Helpers::safeArrayGet($json, 'description');
        $category->displayBorder = Helpers::safeArrayGet($json, 'displayBorder');
        $category->foregroundColor = Helpers::safeArrayGet($json, 'foregroundColor');
        $category->manager = Helpers::safeArrayGet($json, 'manager');
        $category->name = Helpers::safeArrayGet($json, 'name');
        $category->priority = Helpers::safeArrayGet($json, 'priority');
        $category->type = Helpers::safeArrayGet($json, 'type');
        $category->usage = Helpers::safeArrayGet($json, 'usage');

        return $category;
    }

    function put($id) {
        global $hesk_settings, $userContext, $applicationContext;

        $data = JsonRetriever::getJsonData();

        $category = $this->buildCategoryFromJson($data);
        $category->id = intval($id);

        /* @var $categoryHandler CategoryHandler */
        $categoryHandler = $applicationContext->get(CategoryHandler::class);

        $category = $categoryHandler->editCategory($category, $userContext, $hesk_settings);

        return output($category);
    }

    function delete($id) {
        global $hesk_settings, $userContext, $applicationContext;

        /* @var $categoryHandler CategoryHandler */
        $categoryHandler = $applicationContext->get(CategoryHandler::class);

        $categoryHandler->deleteCategory($id, $userContext, $hesk_settings);

        return http_response_code(204);
    }

    static function sort($id, $direction) {
        global $applicationContext, $hesk_settings;

        /* @var $handler CategoryHandler */
        $handler = $applicationContext->get(CategoryHandler::class);

        $handler->sortCategory(intval($id), $direction, $hesk_settings);
    }
}