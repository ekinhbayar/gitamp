<?php declare(strict_types=1);

namespace ekinhbayar\GitAmpTests\Github;

use ekinhbayar\GitAmp\Github\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetAuthenticationString()
    {
        $this->assertSame(
            'Z2l0YW1wdXNlcm5hbWU6Z2l0YW1wcGFzc3dvcmQ=',
            (new User('gitampusername', 'gitamppassword'))->getAuthenticationString()
        );
    }
}
