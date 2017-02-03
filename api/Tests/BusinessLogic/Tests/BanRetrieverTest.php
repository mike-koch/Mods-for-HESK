<?php
/**
 * Created by PhpStorm.
 * User: mkoch
 * Date: 2/2/2017
 * Time: 9:57 PM
 */

namespace Tests;


use BusinessLogic\Security\BannedEmail;
use BusinessLogic\Security\BanRetriever;
use DataAccess\Security\BanGateway;
use PHPUnit\Framework\TestCase;


class BanRetrieverTests extends TestCase {
    function testItReturnsTrueWhenTheIpIsBanned() {
        //-- Arrange
        $banGateway = $this->createMock(BanGateway::class);
        $banRetriever = new BanRetriever($banGateway);
        $bannedEmail = new BannedEmail();
        $bannedEmail->email = 'my@email.address';
        $banGateway->method('getEmailBans')
                    ->willReturn([$bannedEmail]);

        //-- Act
        $result = $banRetriever->isEmailBanned('my@email.address', null);

        //-- Assert
        $this->assertThat($result, $this->isTrue());
    }
}
