<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Storage;

use Amp\Promise;
use Amp\Redis\Client;
use Amp\Redis\RedisException;
use function Amp\resolve;

class RedisCounter implements Counter {

    private $redis;

    public function __construct(Client $redis) {
        $this->redis = $redis;
    }

    public function increment(string $key): Promise {
        return resolve(function() use ($key) {
            try {
                return yield $this->redis->incr($key);
            } catch (RedisException $e) {
                throw new StorageFailedException('Failed to increment counter.', 0, $e);
            }
        });
    }

    public function decrement(string $key): Promise {
        return resolve(function() use ($key) {
            try {
                return yield $this->redis->decr($key);
            } catch (RedisException $e) {
                throw new StorageFailedException('Failed to decrement counter.', 0, $e);
            }
        });
    }

    public function get(string $key): Promise {
        return resolve(function() use ($key) {
            try {
                $result = yield $this->redis->get($key);

                return empty($result) ? 0 : (int) $result;
            } catch (RedisException $e) {
                throw new StorageFailedException('Failed to get counter.', 0, $e);
            }
        });
    }

    public function set(string $key, int $val): Promise {
        return resolve(function() use ($key, $val) {
            try {
                return yield $this->redis->set($key, $val);
            } catch (RedisException $e) {
                throw new StorageFailedException('Failed to set counter value.', 0, $e);
            }
        });
    }
}

