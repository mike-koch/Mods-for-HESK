<?php

namespace Controllers\Categories;


use BusinessLogic\Categories\CategoryGroup;
use BusinessLogic\Categories\CategoryGroupHandler;
use BusinessLogic\Categories\CategoryGroupRetriever;
use BusinessLogic\Exceptions\ApiFriendlyException;
use BusinessLogic\Helpers;
use Controllers\JsonRetriever;

class CategoryGroupController extends \BaseClass {

    private static function getAllCategoryGroups() {
        global $hesk_settings, $applicationContext, $userContext;

        /* @var $categoryGroupRetriever CategoryGroupRetriever */
        $categoryGroupRetriever = $applicationContext->get(CategoryGroupRetriever::clazz());

        return $categoryGroupRetriever->getAllCategoryGroups($hesk_settings, $userContext);
    }

    public function get() {
        output(self::getAllCategoryGroups());
    }

    public function post() {
        global $hesk_settings, $applicationContext, $userContext;

        $data = JsonRetriever::getJsonData();

        /* @var $categoryGroupHandler CategoryGroupHandler */
        $categoryGroupHandler = $applicationContext->get(CategoryGroupHandler::clazz());

        return output($categoryGroupHandler->createCategory($this->buildCategoryGroupModel($data), $userContext, $hesk_settings));
    }

    private function buildCategoryGroupModel($json, $id = null) {
        $categoryGroup = new CategoryGroup();
        $categoryGroup->id = $id;
        $categoryGroup->parentId = Helpers::safeArrayGet($json, 'parentId');

        $names = $json['names'];

        foreach ($names as $key => $value) {
            $categoryGroup->names[$key] = $value;
        }

        return $categoryGroup;
    }

    public function put() {

    }

    public function delete($id) {

    }
}