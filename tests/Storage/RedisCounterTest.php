<?php declare(strict_types=1);


namespace ekinhbayar\GitAmpTests\Storage;


use Amp\Promise;
use Amp\Redis\Client;
use ekinhbayar\GitAmp\Storage\RedisCounter;
use PHPUnit\Framework\TestCase;
use function Amp\wait;

class RedisCounterTest extends TestCase
{
    private $redis;

    public function setUp()
    {
        $this->redis = new RedisCounter(new Client('tcp://localhost:6379'));
    }

    public function tearDown()
    {
        $this->redis = null;
    }

    public function testSet()
    {
        $this->assertInstanceOf(Promise::class, $this->redis->set('test', 0));
        $this->assertEquals(0, wait($this->redis->get('test')));
    }

    public function testIncrement()
    {
        $this->assertEquals(0, wait($this->redis->get('test')));

        $this->redis->increment('test');

        $this->assertEquals(1, wait($this->redis->get('test')));
    }

    public function testDecrement()
    {
        $this->assertEquals(1, wait($this->redis->get('test')));

        $this->redis->decrement('test');

        $this->assertEquals(0, wait($this->redis->get('test')));
    }
}