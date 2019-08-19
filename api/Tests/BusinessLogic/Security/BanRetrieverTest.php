<?php

namespace Tests;


use BusinessLogic\Security\BannedEmail;
use BusinessLogic\Security\BannedIp;
use BusinessLogic\Security\BanRetriever;
use DataAccess\Security\BanGateway;
use PHPUnit\Framework\TestCase;


class BanRetrieverTests extends TestCase {
    /**
     * @var $banGateway \PHPUnit_Framework_MockObject_MockObject;
     */
    private $banGateway;

    /**
     * @var $banRetriever BanRetriever;
     */
    private $banRetriever;

    protected function setUp(): void {
        $this->banGateway = $this->createMock(BanGateway::clazz());
        $this->banRetriever = new BanRetriever($this->banGateway);
    }

    function testItReturnsTrueWhenTheEmailIsBanned() {
        //-- Arrange
        $bannedEmail = new BannedEmail();
        $bannedEmail->email = 'my@email.address';
        $this->banGateway->method('getEmailBans')
                    ->willReturn([$bannedEmail]);

        //-- Act
        $result = $this->banRetriever->isEmailBanned('my@email.address', null);

        //-- Assert
        $this->assertThat($result, $this->isTrue());
    }

    function testItReturnsFalseWhenTheEmailIsNotBanned() {
        //-- Arrange
        $bannedEmail = new BannedEmail();
        $bannedEmail->email = 'my@other.address';
        $this->banGateway->method('getEmailBans')
                    ->willReturn([$bannedEmail]);

        //-- Act
        $result = $this->banRetriever->isEmailBanned('my@email.address', null);

        //-- Assert
        $this->assertThat($result, $this->isFalse());
    }

    function testItReturnsTrueWhenTheIpIsBanned() {
        //-- Arrange
        $bannedIp = new BannedIp();
        $bannedIp->ipFrom = ip2long('1.0.0.0');
        $bannedIp->ipTo = ip2long('1.0.0.5');
        $this->banGateway->method('getIpBans')
                    ->willReturn([$bannedIp]);

        //-- Act
        $result = $this->banRetriever->isIpAddressBanned(ip2long('1.0.0.3'), null);

        //-- Assert
        $this->assertThat($result, $this->isTrue());
    }

    function testItReturnsFalseWhenTheIpIsNotBanned() {
        //-- Arrange
        $bannedIp = new BannedIp();
        $bannedIp->ipFrom = ip2long('1.0.0.0');
        $bannedIp->ipTo = ip2long('1.0.0.5');
        $this->banGateway->method('getIpBans')
            ->willReturn([$bannedIp]);

        //-- Act
        $result = $this->banRetriever->isIpAddressBanned(ip2long('2.0.0.3'), null);

        //-- Assert
        $this->assertThat($result, $this->isFalse());
    }
}
