<?php


namespace BusinessLogic\Categories;


use BusinessLogic\Security\PermissionChecker;
use BusinessLogic\Security\UserContext;
use Core\Constants\Priority;
use DataAccess\Categories\CategoryGateway;
use DataAccess\Settings\ModsForHeskSettingsGateway;
use DataAccess\Tickets\TicketGateway;
use PHPUnit\Framework\TestCase;

class CategoryHandlerTest extends TestCase {
    /* @var $categoryGateway CategoryGateway|\PHPUnit_Framework_MockObject_MockObject */
    private $categoryGateway;

    /* @var $categoryHandler CategoryHandler */
    private $categoryHandler;

    /* @var $ticketGateway TicketGateway|\PHPUnit_Framework_MockObject_MockObject */
    private $ticketGateway;

    /* @var $permissionChecker PermissionChecker|\PHPUnit_Framework_MockObject_MockObject */
    private $permissionChecker;

    /* @var $modsForHeskSettingsGateway ModsForHeskSettingsGateway|\PHPUnit_Framework_MockObject_MockObject */
    private $modsForHeskSettingsGateway;

    /* @var $heskSettings array */
    private $heskSettings;

    protected function setUp() {
        $this->categoryGateway = $this->createMock(CategoryGateway::clazz());
        $this->ticketGateway = $this->createMock(TicketGateway::clazz());
        $this->permissionChecker = $this->createMock(PermissionChecker::clazz());
        $this->modsForHeskSettingsGateway = $this->createMock(ModsForHeskSettingsGateway::clazz());

        $this->categoryHandler = new CategoryHandler($this->categoryGateway,
            $this->ticketGateway,
            $this->permissionChecker,
            $this->modsForHeskSettingsGateway);
        $this->heskSettings = array();

        //TODO write proper tests!
        $this->permissionChecker->method('doesUserHavePermission')->willReturn(true);
    }

    function testCreateCallsTheGatewayWithTheCategory() {
        //-- Arrange
        $category = new Category();
        $category->autoAssign = true;
        $category->backgroundColor = 'a';
        $category->foregroundColor = 'a';
        $category->catOrder = 1000;
        $category->description = 'd';
        $category->displayBorder = false;
        $category->name = 'n';
        $category->priority = Priority::LOW;
        $category->usage = 0;
        $category->type = 0;
        $category->id = 1;
        $this->categoryGateway->method('getAllCategories')->willReturn([$category]);

        //-- Assert
        $this->categoryGateway->expects($this->once())->method('createCategory')
            ->willReturn(1)
            ->with($category, $this->heskSettings);

        //-- Act
        $this->categoryHandler->createCategory($category, new UserContext(), $this->heskSettings);
    }
}
