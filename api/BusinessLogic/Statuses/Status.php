<?php

namespace BusinessLogic\Statuses;


class Status {
    static function fromDatabase($row, $languageRs) {
        $status = new Status();
        $status->id = $row['ID'];
        $status->textColor = $row['TextColor'];
        $status->defaultActions = array();

        foreach (DefaultStatusForAction::getAll() as $defaultStatus) {
            $status = self::addDefaultStatusIfSet($status, $row, $defaultStatus);
        }

        $status->closable = $row['Closable'];

        $localizedLanguages = array();
        while ($languageRow = hesk_dbFetchAssoc($languageRs)) {
            $localizedLanguages[$languageRow['language']] = new StatusLanguage($languageRow['language'], $languageRow['text']);
        }
        $status->localizedNames = $localizedLanguages;
        $status->sort = $row['sort'];

        return $status;
    }

    /**
     * @param $status Status
     * @param $row array
     * @param $key string
     * @return Status
     */
    private static function addDefaultStatusIfSet($status, $row, $key) {
        if ($row[$key]) {
            $status->defaultActions[] = $key;
        }

        return $status;
    }

    /**
     * @var $id int
     */
    public $id;

    /**
     * @var $textColor string
     */
    public $textColor;

    /**
     * @var $defaultActions DefaultStatusForAction[]
     */
    public $defaultActions;

    /**
     * @var $closable Closable
     */
    public $closable;

    /**
     * @var $sort int
     */
    public $sort;

    /**
     * @var $name StatusLanguage[]
     */
    public $localizedNames;
}