<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmpTests\Client;

use Amp\Artax\Client;
use Amp\Promise;
use ekinhbayar\GitAmp\Github\Token;
use ekinhbayar\GitAmp\Client\GitAmp;
use ekinhbayar\GitAmp\Events\Factory;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    private $gitamp;

    public function setUp()
    {
        $this->gitamp = new GitAmp(new Client, new Token('token'), new Factory());
    }

    public function tearDown()
    {
        $this->gitamp = null;
    }

    public function testRequestReturnsPromise()
    {
        $promise = $this->gitamp->request();

        $this->assertInstanceOf(Promise::class, $promise);
    }

    public function testListenReturnsPromise()
    {
        $promise = $this->gitamp->listen();

        $this->assertInstanceOf(Promise::class, $promise);
    }
}
