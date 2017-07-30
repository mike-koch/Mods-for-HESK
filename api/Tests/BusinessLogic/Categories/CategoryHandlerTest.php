<?php


namespace BusinessLogic\Categories;


use DataAccess\Categories\CategoryGateway;
use PHPUnit\Framework\TestCase;

class CategoryHandlerTest extends TestCase {
    /* @var $categoryGateway \PHPUnit_Framework_MockObject_MockObject */
    private $categoryGateway;

    /* @var $categoryHandler CategoryHandler */
    private $categoryHandler;

    /* @var $heskSettings array */
    private $heskSettings;

    protected function setUp() {
        $this->categoryGateway = $this->createMock(CategoryGateway::class);

        $this->categoryHandler = new CategoryHandler($this->categoryGateway);
        $this->heskSettings = array();
    }

    function testCreateCallsTheGatewayWithTheCategory() {
        //-- Arrange
        $category = new Category();

        //-- Assert
        $this->categoryGateway->expects($this->once())->method('createCategory')->with($category, $this->heskSettings);

        //-- Act
        $this->categoryHandler->createCategory($category, $this->heskSettings);
    }
}
