<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp;

use Amp\Loop;
use ekinhbayar\GitAmp\Log\LoggerFactory;

require __DIR__ . '/vendor/autoload.php';

/** @var Configuration $configuration */
$configuration = require_once __DIR__ . '/config.php';

$logger = (new LoggerFactory())->build($configuration->getLogLevel());

Loop::setErrorHandler(function (\Throwable $e) use ($logger): void {
    $logger->emergency('GitAmp blew up', ['exception' => $e]);
});

$server = new Server($logger, $configuration);

Loop::run(function () use ($server) {
    yield $server->start();
});
