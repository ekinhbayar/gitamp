<?php declare(strict_types=1);


namespace ekinhbayar\GitAmpTests\Storage;


use Amp\Redis\Client;
use PHPUnit\Framework\TestCase;

class RedisCounterTest extends TestCase
{
    private $redis;

    public function setUp()
    {
        $this->redis = new Client('tcp://localhost:6379');
    }

    public function tearDown()
    {
        $this->redis = null;
    }

    public function testIncrement()
    {
        yield $this->redis->set('test', 0);
        yield $this->redis->increment('test');
        $count = yield $this->redis->get('test');

        $this->assertEquals(1, $count);
    }

    public function testDecrement()
    {
        yield $this->redis->set('test', 3);
        yield $this->redis->decrement('test');
        $count = yield $this->redis->get('test');

        $this->assertEquals(2, $count);
    }
}