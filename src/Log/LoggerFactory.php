<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Log;

use Amp\Log\ConsoleFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use function Amp\ByteStream\getStdout;

final class LoggerFactory
{
    public function build(int $logLevel): Logger
    {
        $logHandler = new StreamHandler(getStdout()->getResource(), $logLevel);
        $logHandler->setFormatter(new ConsoleFormatter());

        $logger = new Logger('gitamp');
        $logger->pushHandler($logHandler);

        return $logger;
    }
}
