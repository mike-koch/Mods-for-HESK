<?php

namespace BusinessLogic\Categories;

use BusinessLogic\Security\UserContext;
use DataAccess\Categories\CategoryGateway;
use DataAccess\Settings\ModsForHeskSettingsGateway;

class CategoryRetriever {
    /**
     * @var CategoryGateway
     */
    private $categoryGateway;

    /**
     * @var ModsForHeskSettingsGateway
     */
    private $modsForHeskSettingsGateway;

    function __construct(CategoryGateway $categoryGateway,
                         ModsForHeskSettingsGateway $modsForHeskSettingsGateway) {
        $this->categoryGateway = $categoryGateway;
        $this->modsForHeskSettingsGateway = $modsForHeskSettingsGateway;
    }

    /**
     * @param $heskSettings array
     * @param $userContext UserContext
     * @return array
     */
    function getAllCategories($heskSettings, $userContext) {
        $modsForHeskSettings = $this->modsForHeskSettingsGateway->getAllSettings($heskSettings);

        $categories = $this->categoryGateway->getAllCategories($heskSettings, $modsForHeskSettings);

        foreach ($categories as $category) {
            $category->accessible = $userContext->admin ||
                in_array($category->id, $userContext->categories);
        }

        return $categories;
    }
}