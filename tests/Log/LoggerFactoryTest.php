<?php declare(strict_types=1);

namespace ekinhbayar\GitAmpTests\Log;

use ekinhbayar\GitAmp\Log\LoggerFactory;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class LoggerFactoryTest extends TestCase
{
    private Logger $logger;

    public function setUp(): void
    {
        $this->logger = (new LoggerFactory())->build(Logger::INFO);
    }

    public function testBuildSetsName(): void
    {
        $this->assertSame('gitamp', $this->logger->getName());
    }

    public function testBuildSetsLogLevel(): void
    {
        $this->assertTrue($this->logger->isHandling(Logger::INFO));
    }
}
