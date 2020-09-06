<?php declare(strict_types=1);

namespace ekinhbayar\GitAmpTests\Response;

use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Amp\ByteStream\InputStream;
use Amp\ByteStream\Payload;
use function Amp\Promise\wait;
use Amp\Success;
use ekinhbayar\GitAmp\Response\Factory;
use ekinhbayar\GitAmp\Response\Results;
use ekinhbayar\GitAmp\Event\Factory as EventFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class FactoryTest extends TestCase
{
    public function testBuildResultsUnknownEventDoesNotBubbleUp(): void
    {
        $events = json_encode([
            [
                'id'        => 1,
                'type'      => 'InvalidEvent',
                'action'    => 'created',
                'repoName'  => 'test/repo',
                'actorName' => 'PeeHaa',
                'eventUrl'  => 'https://github.com/test/repo',
                'message'   => 'The description',
            ],
        ]);

        $inputStream = $this->createMock(InputStream::class);
        $inputStream
            ->expects($this->exactly(2))
            ->method('read')
            ->willReturnOnConsecutiveCalls(new Success($events), new Success(null))
        ;

        $message = new Payload($inputStream);

        $response = new Response('2', 200, 'OK', [], $message, new Request('foo'));

        $logger = $this->createMock(LoggerInterface::class);

        $factory = new Factory(new EventFactory(), $logger);

        $results = wait($factory->build('ekinhbayar\GitAmp\Event\GitHub', $response));

        $this->assertInstanceOf(Results::class, $results);
    }

    public function testBuildReturnsResult(): void
    {
        $events = json_encode([
            [
                'id'       => 1,
                'type'     => 'CreateEvent',
                'action'   => 'created',
                'repo'     => ['name' => 'test/repo'],
                'actor'    => ['login' => 'PeeHaa'],
                'eventUrl' => 'https://github.com/test/repo',
                'message'  => 'The description',
            ],
        ]);

        $inputStream = $this->createMock(InputStream::class);
        $inputStream
            ->expects($this->exactly(2))
            ->method('read')
            ->willReturnOnConsecutiveCalls(new Success($events), new Success(null))
        ;

        $message = new Payload($inputStream);

        $response = new Response('2', 200, 'OK', [], $message, new Request('foo'));

        $logger = $this->createMock(LoggerInterface::class);

        $results = (new Factory(new EventFactory(), $logger))->build('ekinhbayar\GitAmp\Event\GitHub', $response);

        $this->assertInstanceOf(Results::class, wait($results));
    }
}
