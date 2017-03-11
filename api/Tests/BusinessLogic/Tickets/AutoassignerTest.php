<?php
/**
 * Created by PhpStorm.
 * User: mkoch
 * Date: 3/10/2017
 * Time: 7:59 PM
 */

namespace BusinessLogic\Tickets;


use BusinessLogic\Security\UserContext;
use DataAccess\Categories\CategoryGateway;
use DataAccess\Security\UserGateway;
use PHPUnit\Framework\TestCase;

class AutoassignerTest extends TestCase {
    /* @var $categoryGateway \PHPUnit_Framework_MockObject_MockObject */
    private $categoryGateway;

    /* @var $userGateway \PHPUnit_Framework_MockObject_MockObject */
    private $userGateway;

    /* @var $autoassigner Autoassigner */
    private $autoassigner;

    /* @var $heskSettings array */
    private $heskSettings;

    protected function setUp() {
        $this->categoryGateway = $this->createMock(CategoryGateway::class);
        $this->userGateway = $this->createMock(UserGateway::class);
        $this->autoassigner = new Autoassigner($this->categoryGateway, $this->userGateway);
        $this->heskSettings = array(
            'autoassign' => 1
        );
    }

    function testItReturnsNullWhenAutoassignIsDisabled() {
        //-- Arrange
        $this->heskSettings['autoassign'] = 0;

        //-- Act
        $owner = $this->autoassigner->getNextUserForTicket(0, $this->heskSettings);

        //-- Assert
        self::assertThat($owner, self::isNull());
    }

    function testItReturnsTheUsersWithLeastOpenTickets() {
        //-- Arrange
        $userWithNoOpenTickets = new UserContext();
        $userWithNoOpenTickets->id = 1;
        $userWithNoOpenTickets->categories = array(1);
        $userWithOneOpenTicket = new UserContext();
        $userWithOneOpenTicket->id = 2;
        $userWithOneOpenTicket->categories = array(1);
        $usersToReturn = array(
            $userWithNoOpenTickets,
            $userWithOneOpenTicket
        );

        $this->userGateway->method('getUsersByNumberOfOpenTickets')
            ->with($this->heskSettings)
            ->willReturn($usersToReturn);

        //-- Act
        $actual = $this->autoassigner->getNextUserForTicket(1, $this->heskSettings);

        //-- Assert
        self::assertThat($actual, self::equalTo($userWithNoOpenTickets));
    }
}
