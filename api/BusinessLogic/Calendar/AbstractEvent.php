<?php

namespace BusinessLogic\Calendar;


class AbstractEvent {
    public $id;

    public $startTime;

    public $title;

    public $categoryId;

    public $categoryName;

    public $backgroundColor;

    public $foregroundColor;

    public $displayBorder;
}