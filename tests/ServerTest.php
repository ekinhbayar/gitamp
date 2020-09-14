<?php declare(strict_types=1);

namespace ekinhbayar\GitAmpTests;

use Amp\Loop;
use ekinhbayar\GitAmp\Configuration;
use ekinhbayar\GitAmp\Github\Token;
use ekinhbayar\GitAmp\Provider\RequestFailedException;
use ekinhbayar\GitAmp\Server;
use ekinhbayar\GitAmp\ServerAddress;
use League\Uri\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ServerTest extends TestCase
{
    public function testStartStartsServer(): void
    {
        $this->expectException(RequestFailedException::class);
        $this->expectExceptionMessage('A non-200 response status (401 - Unauthorized) was encountered');

        $configuration = (new Configuration(new Token('12345')))
            ->addWebsocketAddress(Uri::createFromString('https://gitamp.audio'))
            ->bind(new ServerAddress('127.0.0.1', 1337))
        ;

        Loop::run(function () use ($configuration) {
            $server = new Server($this->createMock(LoggerInterface::class), $configuration);

            yield $server->start();
        });
    }
}
