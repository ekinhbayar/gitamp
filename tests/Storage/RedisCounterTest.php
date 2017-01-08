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

    public function testGet()
    {
        $redisClient =  $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $redisClient
            ->expects($this->exactly(2))
            ->method('get')
            ->with($this->equalTo('test'))
            ->will($this->returnValue(new Success(10)))
        ;

        $redisCounter = new RedisCounter($redisClient);

        $this->assertInstanceOf(Promise::class, $redisCounter->get('test'));

        $this->assertSame(
            10, wait($redisCounter->get('test'))
        );
    }

    public function testSet()
    {
        $redisClient =  $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['set','get'])
            ->getMock();

        $redisClient
            ->expects($this->once())
            ->method('set')
            ->with($this->equalTo('test'), $this->equalTo(5))
        ;

        $redisCounter = new RedisCounter($redisClient);

        wait($redisCounter->set('test', 5));
    }

    public function testIncrement()
    {
        $redisClient =  $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $redisClient
            ->expects($this->once())
            ->method('incr')
            ->with($this->equalTo('test'))
        ;

        $redisCounter = new RedisCounter($redisClient);

        wait($redisCounter->increment('test'));
    }

    public function testDecrement()
    {
        $redisClient =  $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $redisClient
            ->expects($this->once())
            ->method('decr')
            ->with($this->equalTo('test'))
        ;

        $redisCounter = new RedisCounter($redisClient);

        wait($redisCounter->decrement('test'));
    }

    public function testSetThrowsOnFailedStorage()
    {
        $redisClient =  $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $redisClient
            ->expects($this->once())
            ->method('set')
            ->will($this->throwException(new RedisException()))
        ;

        $redisCounter = new RedisCounter($redisClient);

        $this->expectException(StorageFailedException::class);
        $this->expectExceptionMessage('Failed to set counter value.');

        wait($redisCounter->set('foo', 2));
    }

    public function testGetThrowsOnFailedStorage()
    {
        $redisClient =  $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $redisClient
            ->expects($this->once())
            ->method('get')
            ->will($this->throwException(new RedisException()))
        ;

        $redisCounter = new RedisCounter($redisClient);

        $this->expectException(StorageFailedException::class);
        $this->expectExceptionMessage('Failed to get counter.');

        wait($redisCounter->get('foo'));
    }

    public function testIncrementThrowsOnFailedStorage()
    {
        $redisClient =  $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $redisClient
            ->expects($this->once())
            ->method('incr')
            ->will($this->throwException(new RedisException()))
        ;

        $redisCounter = new RedisCounter($redisClient);

        $this->expectException(StorageFailedException::class);
        $this->expectExceptionMessage('Failed to increment counter.');

        wait($redisCounter->increment('foo'));
    }

    public function testDecrementThrowsOnFailedStorage()
    {
        $redisClient =  $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $redisClient
            ->expects($this->once())
            ->method('decr')
            ->will($this->throwException(new RedisException()))
        ;

        $redisCounter = new RedisCounter($redisClient);

        $this->expectException(StorageFailedException::class);
        $this->expectExceptionMessage('Failed to decrement counter.');

        wait($redisCounter->decrement('foo'));
    }
}

