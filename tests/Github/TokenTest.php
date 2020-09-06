<?php declare(strict_types=1);

namespace ekinhbayar\GitAmpTests\Github;

use ekinhbayar\GitAmp\Github\Token;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
    public function testGetAuthenticationString(): void
    {
        $this->assertSame('gitamptoken', (new Token('gitamptoken'))->getAuthenticationString());
    }
}
