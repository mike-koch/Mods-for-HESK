<?php


namespace BusinessLogic\Security;


use BusinessLogic\Tickets\Ticket;
use PHPUnit\Framework\TestCase;

class UserToTicketCheckerTest extends TestCase {

    /* @var $userToTicketChecker UserToTicketChecker */
    private $userToTicketChecker;

    /* @var $heskSettings array */
    private $heskSettings;

    protected function setUp() {
        $this->userToTicketChecker = new UserToTicketChecker();
    }

    function testItReturnsTrueWhenTheUserIsAnAdmin() {
        //-- Arrange
        $user = new UserContext();
        $user->admin = true;

        $ticket = new Ticket();

        //-- Act
        $result = $this->userToTicketChecker->isTicketWritableToUser($user, $ticket, false, $this->heskSettings);

        //-- Assert
        self::assertThat($result, self::isTrue());
    }

    function testItReturnsTrueWhenTheUserHasAccessToTheCategory() {
        //-- Arrange
        $user = new UserContext();
        $user->admin = false;
        $user->categories = array(1);
        $user->permissions = array(UserPrivilege::CAN_VIEW_TICKETS);

        $ticket = new Ticket();
        $ticket->categoryId = 1;

        //-- Act
        $result = $this->userToTicketChecker->isTicketWritableToUser($user, $ticket, false, $this->heskSettings);

        //-- Assert
        self::assertThat($result, self::isTrue());
    }

    function testItReturnsFalseWhenTheUserCannotViewTickets() {
        //-- Arrange
        $user = new UserContext();
        $user->admin = false;
        $user->categories = array(1);
        $user->permissions = array();

        $ticket = new Ticket();
        $ticket->categoryId = 1;

        //-- Act
        $result = $this->userToTicketChecker->isTicketWritableToUser($user, $ticket, false, $this->heskSettings);

        //-- Assert
        self::assertThat($result, self::isFalse());
    }

    function testItReturnsFalseWhenTheUserCannotViewAndEditTicketsWhenEditFlagIsTrue() {
        //-- Arrange
        $user = new UserContext();
        $user->admin = false;
        $user->categories = array(1);
        $user->permissions = array(UserPrivilege::CAN_VIEW_TICKETS, 'something else');

        $ticket = new Ticket();
        $ticket->categoryId = 1;

        //-- Act
        $result = $this->userToTicketChecker->isTicketWritableToUser($user, $ticket, true, $this->heskSettings);

        //-- Assert
        self::assertThat($result, self::isFalse());
    }

    //-- TODO Category Manager
}
