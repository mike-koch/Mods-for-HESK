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

    public static function updateTreeState() {
        global $hesk_settings, $applicationContext, $userContext;

        $data = JsonRetriever::getJsonData();

        /* @var $categoryGroupHandler CategoryGroupHandler */
        $categoryGroupHandler = $applicationContext->get(CategoryGroupHandler::clazz());
        self::updateCategoryGroup($categoryGroupHandler, $data, 10, $hesk_settings, null);

        return http_response_code(204);
    }

    /**
     * @param $categoryGroupHandler CategoryGroupHandler
     * @param $entries array[]
     * @param $sort int
     * @param null $parent
     */
    private static function updateCategoryGroup($categoryGroupHandler, $entries, $sort, $heskSettings, $parent) {
        foreach ($entries as $entry) {
            $categoryGroupHandler->updateCategorySortAndParent($entry['id'], $sort, $parent, $heskSettings);

            self::updateCategoryGroup($categoryGroupHandler, $entry['children'], $sort, $heskSettings, $entry['id']);

            $sort += 10;
        }
    }

    public function put() {

    }

    public function delete($id) {

    }
}