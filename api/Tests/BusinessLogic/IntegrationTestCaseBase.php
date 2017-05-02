<?php
namespace BusinessLogic;

use PHPUnit\Framework\TestCase;

class IntegrationTestCaseBase extends TestCase {
    function skip() {
        $this->markTestSkipped(sprintf("Skipping Integration Test %s", get_class($this)));
    }
}
