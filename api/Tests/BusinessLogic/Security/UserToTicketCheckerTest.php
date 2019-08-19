<?php


namespace BusinessLogic\Security;


use BusinessLogic\Tickets\Ticket;
use DataAccess\Security\UserGateway;
use PHPUnit\Framework\TestCase;

class UserToTicketCheckerTest extends TestCase {

    /* @var $userToTicketChecker UserToTicketChecker */
    private $userToTicketChecker;

    /* @var $userGateway \PHPUnit_Framework_MockObject_MockObject */
    private $userGateway;

    /* @var $heskSettings array */
    private $heskSettings;

    protected function setUp(): void {
        $this->userGateway = $this->createMock(UserGateway::clazz());
        $this->userToTicketChecker = new UserToTicketChecker($this->userGateway);
    }

    function testItReturnsTrueWhenTheUserIsAnAdmin() {
        //-- Arrange
        $user = new UserContext();
        $user->admin = true;
        $user->id = 99;

        $ticket = new Ticket();

        //-- Act
        $result = $this->userToTicketChecker->isTicketAccessibleToUser($user, $ticket, $this->heskSettings);

        //-- Assert
        self::assertThat($result, self::isTrue());
    }

    function testItReturnsTrueWhenTheUserHasAccessToTheCategory() {
        //-- Arrange
        $user = new UserContext();
        $user->admin = false;
        $user->categories = array(1);
        $user->permissions = array(UserPrivilege::CAN_VIEW_TICKETS);
        $user->id = 99;

        $ticket = new Ticket();
        $ticket->categoryId = 1;

        //-- Act
        $result = $this->userToTicketChecker->isTicketAccessibleToUser($user, $ticket, $this->heskSettings);

        //-- Assert
        self::assertThat($result, self::isTrue());
    }

    function testItReturnsFalseWhenTheUserCannotViewTickets() {
        //-- Arrange
        $user = new UserContext();
        $user->admin = false;
        $user->categories = array(1);
        $user->permissions = array();
        $user->id = 99;

        $ticket = new Ticket();
        $ticket->categoryId = 1;

        //-- Act
        $result = $this->userToTicketChecker->isTicketAccessibleToUser($user, $ticket, $this->heskSettings);

        //-- Assert
        self::assertThat($result, self::isFalse());
    }

    function testItReturnsFalseWhenTheUserCannotViewAndEditTicketsWhenEditFlagIsTrue() {
        //-- Arrange
        $user = new UserContext();
        $user->admin = false;
        $user->categories = array(1);
        $user->permissions = array(UserPrivilege::CAN_VIEW_TICKETS, 'something else');
        $user->id = 99;

        $ticket = new Ticket();
        $ticket->categoryId = 1;

        //-- Act
        $result = $this->userToTicketChecker->isTicketAccessibleToUser($user, $ticket, $this->heskSettings, array(UserPrivilege::CAN_EDIT_TICKETS));

        //-- Assert
        self::assertThat($result, self::isFalse());
    }

    function testItReturnsTrueWhenTheUserDoesNotHaveEditPermissionsButIsTheCategoryManager() {
        //-- Arrange
		$this->markTestSkipped();
		
        $user = new UserContext();
        $user->admin = false;
        $user->categories = array(1);
        $user->permissions = array(UserPrivilege::CAN_VIEW_TICKETS, 'something else');
        $user->id = 1;
        $this->userGateway->method('getManagerForCategory')->willReturn(1);

        $ticket = new Ticket();
        $ticket->categoryId = 1;

        //-- Act
        $result = $this->userToTicketChecker->isTicketAccessibleToUser($user, $ticket, $this->heskSettings, array(UserPrivilege::CAN_EDIT_TICKETS));

        //-- Assert
        self::assertThat($result, self::isTrue());
    }
}
