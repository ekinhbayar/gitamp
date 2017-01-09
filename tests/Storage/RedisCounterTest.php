<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmpTests\Storage;

use Amp\Promise;
use Amp\Success;
use Amp\Redis\Client;
use Amp\Redis\RedisException;
use ekinhbayar\GitAmp\Storage\RedisCounter;
use ekinhbayar\GitAmp\Storage\StorageFailedException;
use PHPUnit\Framework\TestCase;
use function Amp\wait;

class RedisCounterTest extends TestCase
{
    private $client;

    public function setUp()
    {
        $this->client = $this->createMock(Client::class);
    }

    public function testGet()
    {
        $this->client
            ->expects($this->exactly(2))
            ->method('get')
            ->with($this->equalTo('test'))
            ->will($this->returnValue(new Success(10)))
        ;

        $redisCounter = new RedisCounter($this->client);

        $this->assertInstanceOf(Promise::class, $redisCounter->get('test'));

        $this->assertSame(10, wait($redisCounter->get('test')));
    }

    public function testSet()
    {
        $this->client
            ->expects($this->once())
            ->method('set')
            ->with($this->equalTo('test'), $this->equalTo(5))
        ;

        $redisCounter = new RedisCounter($this->client);

        wait($redisCounter->set('test', 5));
    }

    public function testIncrement()
    {
        $this->client
            ->expects($this->once())
            ->method('incr')
            ->with($this->equalTo('test'))
        ;

        $redisCounter = new RedisCounter($this->client);

        wait($redisCounter->increment('test'));
    }

    public function testDecrement()
    {
        $this->client
            ->expects($this->once())
            ->method('decr')
            ->with($this->equalTo('test'))
        ;

        $redisCounter = new RedisCounter($this->client);

        wait($redisCounter->decrement('test'));
    }

    public function testSetThrowsOnFailedStorage()
    {
        $this->client
            ->expects($this->once())
            ->method('set')
            ->will($this->throwException(new RedisException()))
        ;

        $redisCounter = new RedisCounter($this->client);

        $this->expectException(StorageFailedException::class);
        $this->expectExceptionMessage('Failed to set counter value.');

        wait($redisCounter->set('foo', 2));
    }

    public function testGetThrowsOnFailedStorage()
    {
        $this->client
            ->expects($this->once())
            ->method('get')
            ->will($this->throwException(new RedisException()))
        ;

        $redisCounter = new RedisCounter($this->client);

        $this->expectException(StorageFailedException::class);
        $this->expectExceptionMessage('Failed to get counter.');

        wait($redisCounter->get('foo'));
    }

    public function testIncrementThrowsOnFailedStorage()
    {
        $this->client
            ->expects($this->once())
            ->method('incr')
            ->will($this->throwException(new RedisException()))
        ;

        $redisCounter = new RedisCounter($this->client);

        $this->expectException(StorageFailedException::class);
        $this->expectExceptionMessage('Failed to increment counter.');

        wait($redisCounter->increment('foo'));
    }

    public function testDecrementThrowsOnFailedStorage()
    {
        $this->client
            ->expects($this->once())
            ->method('decr')
            ->will($this->throwException(new RedisException()))
        ;

        $redisCounter = new RedisCounter($this->client);

        $this->expectException(StorageFailedException::class);
        $this->expectExceptionMessage('Failed to decrement counter.');

        wait($redisCounter->decrement('foo'));
    }
}
