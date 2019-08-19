<?php

namespace BusinessLogic\Tickets;


use DataAccess\Tickets\VerifiedEmailGateway;
use PHPUnit\Framework\TestCase;

class VerifiedEmailCheckerTest extends TestCase {
    /**
     * @var $verifiedEmailGateway \PHPUnit_Framework_MockObject_MockObject
     */
    private $verifiedEmailGateway;

    /**
     * @var $verifiedEmailChecker VerifiedEmailChecker
     */
    private $verifiedEmailChecker;

    /**
     * @var $heskSettings array
     */
    private $heskSettings;

    protected function setUp(): void {
        $this->verifiedEmailGateway = $this->createMock(VerifiedEmailGateway::clazz());
        $this->heskSettings = array();
        $this->verifiedEmailChecker = new VerifiedEmailChecker($this->verifiedEmailGateway);
    }

    function testItGetsTheValidationStateFromTheGatewayWhenItItTrue() {
        //-- Arrange
        $this->verifiedEmailGateway->method('isEmailVerified')
            ->with('some email', $this->heskSettings)
            ->willReturn(true);

        //-- Act
        $actual = $this->verifiedEmailChecker->isEmailVerified('some email', $this->heskSettings);

        //-- Assert
        self::assertThat($actual, self::isTrue());
    }

    function testItGetsTheValidationStateFromTheGatewayWhenItItFalse() {
        //-- Arrange
        $this->verifiedEmailGateway->method('isEmailVerified')
            ->with('some email', $this->heskSettings)
            ->willReturn(false);

        //-- Act
        $actual = $this->verifiedEmailChecker->isEmailVerified('some email', $this->heskSettings);

        //-- Assert
        self::assertThat($actual, self::isFalse());
    }
}
