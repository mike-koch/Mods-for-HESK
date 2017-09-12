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
        $this->categoryGateway = $this->createMock(CategoryGateway::clazz());
        $this->userGateway = $this->createMock(UserGateway::clazz());
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

    function getPermissionsForUser() {
        return array('can_view_tickets', 'can_reply_tickets');
    }

    function testItReturnsTheUsersWithLeastOpenTickets() {
        //-- Arrange
        $userWithNoOpenTickets = new UserContext();
        $userWithNoOpenTickets->id = 1;
        $userWithNoOpenTickets->categories = array(1);
        $userWithNoOpenTickets->permissions = $this->getPermissionsForUser();
        $userWithOneOpenTicket = new UserContext();
        $userWithOneOpenTicket->id = 2;
        $userWithOneOpenTicket->categories = array(1);
        $userWithOneOpenTicket->permissions = $this->getPermissionsForUser();
        $usersToReturn = array(
            $userWithNoOpenTickets,
            $userWithOneOpenTicket
        );

        $this->userGateway->method('getUsersByNumberOfOpenTicketsForAutoassign')
            ->with($this->heskSettings)
            ->willReturn($usersToReturn);

        //-- Act
        $actual = $this->autoassigner->getNextUserForTicket(1, $this->heskSettings);

        //-- Assert
        self::assertThat($actual, self::equalTo($userWithNoOpenTickets));
    }

    function testItOnlyReturnsUsersWhoCanAccessTheCategory() {
        //-- Arrange
        $userWithNoOpenTickets = new UserContext();
        $userWithNoOpenTickets->id = 1;
        $userWithNoOpenTickets->categories = array(1);
        $userWithNoOpenTickets->permissions = $this->getPermissionsForUser();
        $userWithOneOpenTicket = new UserContext();
        $userWithOneOpenTicket->id = 2;
        $userWithOneOpenTicket->categories = array(2);
        $userWithOneOpenTicket->permissions = $this->getPermissionsForUser();
        $usersToReturn = array(
            $userWithNoOpenTickets,
            $userWithOneOpenTicket
        );

        $this->userGateway->method('getUsersByNumberOfOpenTicketsForAutoassign')
            ->with($this->heskSettings)
            ->willReturn($usersToReturn);

        //-- Act
        $actual = $this->autoassigner->getNextUserForTicket(2, $this->heskSettings);

        //-- Assert
        self::assertThat($actual, self::equalTo($userWithOneOpenTicket));
    }

    function testItReturnsAdminUsers() {
        //-- Arrange
        $userWithNoOpenTickets = new UserContext();
        $userWithNoOpenTickets->id = 1;
        $userWithNoOpenTickets->categories = array(1);
        $userWithNoOpenTickets->permissions = $this->getPermissionsForUser();
        $userWithNoOpenTickets->admin = true;
        $userWithOneOpenTicket = new UserContext();
        $userWithOneOpenTicket->id = 2;
        $userWithOneOpenTicket->categories = array(2);
        $userWithOneOpenTicket->permissions = $this->getPermissionsForUser();
        $usersToReturn = array(
            $userWithNoOpenTickets,
            $userWithOneOpenTicket
        );

        $this->userGateway->method('getUsersByNumberOfOpenTicketsForAutoassign')
            ->with($this->heskSettings)
            ->willReturn($usersToReturn);

        //-- Act
        $actual = $this->autoassigner->getNextUserForTicket(2, $this->heskSettings);

        //-- Assert
        self::assertThat($actual, self::equalTo($userWithNoOpenTickets));
    }

    function testItOnlyReturnsUsersWhoCanViewAndRespondToTickets() {
        //-- Arrange
        $userWithNoOpenTickets = new UserContext();
        $userWithNoOpenTickets->id = 1;
        $userWithNoOpenTickets->categories = array(1);
        $userWithNoOpenTickets->permissions = array();
        $userWithOneOpenTicket = new UserContext();
        $userWithOneOpenTicket->id = 2;
        $userWithOneOpenTicket->categories = array(1);
        $userWithOneOpenTicket->permissions = $this->getPermissionsForUser();
        $usersToReturn = array(
            $userWithNoOpenTickets,
            $userWithOneOpenTicket
        );

        $this->userGateway->method('getUsersByNumberOfOpenTicketsForAutoassign')
            ->with($this->heskSettings)
            ->willReturn($usersToReturn);

        //-- Act
        $actual = $this->autoassigner->getNextUserForTicket(1, $this->heskSettings);

        //-- Assert
        self::assertThat($actual, self::equalTo($userWithOneOpenTicket));
    }

    function testItReturnsNullWhenNoOneCanHandleTheTicket() {
        //-- Arrange
        $userWithNoOpenTickets = new UserContext();
        $userWithNoOpenTickets->id = 1;
        $userWithNoOpenTickets->categories = array(1);
        $userWithNoOpenTickets->permissions = $this->getPermissionsForUser();
        $userWithOneOpenTicket = new UserContext();
        $userWithOneOpenTicket->id = 2;
        $userWithOneOpenTicket->categories = array(1);
        $userWithOneOpenTicket->permissions = $this->getPermissionsForUser();
        $usersToReturn = array(
            $userWithNoOpenTickets,
            $userWithOneOpenTicket
        );

        $this->userGateway->method('getUsersByNumberOfOpenTicketsForAutoassign')
            ->with($this->heskSettings)
            ->willReturn($usersToReturn);

        //-- Act
        $actual = $this->autoassigner->getNextUserForTicket(2, $this->heskSettings);

        //-- Assert
        self::assertThat($actual, self::isNull());
    }
}
