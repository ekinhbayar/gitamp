<?php declare(strict_types=1);

namespace ekinhbayar\GitAmpTests\Client;

use Amp\Artax\Client;
use ekinhbayar\GitAmp\Client\GitAmp;
use ekinhbayar\GitAmp\Events\Factory;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    private $gitamp;

    public function setUp()
    {
        $this->gitamp = new GitAmp(new Client, new \ekinhbayar\GitAmp\Github\Token('token'), new Factory());
    }

    public function tearDown()
    {
        $this->gitamp = null;
    }

    public function testAuthHeader()
    {
        $header = yield $this->gitamp->getAuthHeader();

        $this->assertSame('Basic token', $header);
    }

    public function testRequestReturnsPromise()
    {
        $promise = yield $this->gitamp->request();

        $this->assertSame('Promise', get_class($promise));
    }

    public function testListenReturnsPromise()
    {
        $promise = yield $this->gitamp->listen();

        $this->assertSame('Promise', get_class($promise));
    }
}