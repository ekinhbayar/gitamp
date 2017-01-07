<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmpTests\Client;

use Amp\Artax\Client;
use Amp\Promise;
use ekinhbayar\GitAmp\Client\RequestFailedException;
use ekinhbayar\GitAmp\Github\Token;
use ekinhbayar\GitAmp\Client\GitAmp;
use ekinhbayar\GitAmp\Events\Factory;
use ekinhbayar\GitAmp\Response\Results;
use PHPUnit\Framework\TestCase;
use function Amp\wait;

class GitAmpTest extends TestCase
{
    private $gitamp;

    public function setUp()
    {
        $this->gitamp = new GitAmp(new Client, new Token('token'), new Factory());
    }

    public function testListenReturnsPromise()
    {
        $promise = $this->gitamp->listen();

        $this->assertInstanceOf(Promise::class, $promise);
    }

    public function testListenReturnsResults()
    {
        $promise = $this->gitamp->listen();

        $this->assertInstanceOf(Results::class, wait($promise));
    }

    public function testListenThrowsOnInvalidCredentials()
    {
        try {
            $promise = $this->gitamp->listen();
        } catch (RequestFailedException $e) {
            $this->expectException(RequestFailedException::class);
        }
    }
}

