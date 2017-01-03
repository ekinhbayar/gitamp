<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Storage;

use Amp\Promise;
use Amp\Redis\Client;
use Amp\Redis\RedisException;
use function Amp\resolve;

class RedisCounter implements Counter {
    const SCRIPT_DECREMENT = <<<SCRIPT
local count = redis.call('decr', KEYS[1])

if count == 0 then
    redis.call('del', KEYS[1])
    return 0
else
    return count
end
SCRIPT;

    private $redis;

    public function __construct(Client $redis) {
        $this->redis = $redis;
    }

    public function increment(string $key): Promise {
        return resolve(function () use ($key) {
            try {
                return yield $this->redis->incr($key);
            } catch (RedisException $e) {
                throw new StorageFailed("Failed to increment counter.", 0, $e);
            }
        });
    }

    public function decrement(string $key): Promise {
        return resolve(function () use ($key) {
            try {
                return yield $this->redis->eval(self::SCRIPT_DECREMENT, [$key], []);
            } catch (RedisException $e) {
                throw new StorageFailed("Failed to decrement counter.", 0, $e);
            }
        });
    }

    public function get(string $key): Promise {
        return resolve(function () use ($key) {
            try {
                $result = yield $this->redis->get($key);

                return empty($result) ? 0 : (int) $result;
            } catch (RedisException $e) {
                throw new StorageFailed("Failed to get counter.", 0, $e);
            }
        });
    }
}
