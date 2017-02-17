<?php

namespace BusinessLogic\Tickets\CustomFields;


use PHPUnit\Framework\TestCase;

class CustomFieldValidatorTest extends TestCase {
    function testItReturnsTrueWhenTheCustomFieldIsInTheCategory() {
        //-- Arrange
        $heskSettings = array(
            'custom_fields' => array(
                'custom1' => array(
                    'use' => 1,
                    'category' => array(1, 2)
                )
            )
        );

        //-- Act
        $result = CustomFieldValidator::isCustomFieldInCategory(1, 1, false, $heskSettings);

        //-- Assert
        $this->assertThat($result, $this->isTrue());
    }

    function testItReturnsTrueWhenTheCustomFieldIsForAllCategories() {
        //-- Arrange
        $heskSettings = array(
            'custom_fields' => array(
                'custom1' => array(
                    'use' => 1,
                    'category' => []
                )
            )
        );

        //-- Act
        $result = CustomFieldValidator::isCustomFieldInCategory(1, 1, false, $heskSettings);

        //-- Assert
        $this->assertThat($result, $this->isTrue());
    }

    function testItReturnsFalseWhenTheCustomFieldIsNotInTheCategory() {
        //-- Arrange
        $heskSettings = array(
            'custom_fields' => array(
                'custom1' => array(
                    'use' => 1,
                    'category' => array(1, 2)
                )
            )
        );

        //-- Act
        $result = CustomFieldValidator::isCustomFieldInCategory(1, 50, false, $heskSettings);

        //-- Assert
        $this->assertThat($result, $this->isFalse());
    }

    function testItReturnsFalseWhenTheCustomFieldIsForStaffOnly() {
        //-- Arrange
        $heskSettings = array(
            'custom_fields' => array(
                'custom1' => array(
                    'use' => 2,
                    'category' => array(1, 2)
                )
            )
        );

        //-- Act
        $result = CustomFieldValidator::isCustomFieldInCategory(1, 1, false, $heskSettings);

        //-- Assert
        $this->assertThat($result, $this->isFalse());
    }
}
