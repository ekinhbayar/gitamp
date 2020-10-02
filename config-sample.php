<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp;

use League\Uri\Uri;
use ekinhbayar\GitAmp\Github\Token;

/**
 * Use a personal access token for authentication
 * https://github.com/settings/tokens
 */
return (new Configuration(new Token('123456')))
    ->addWebsocketAddress(Uri::createFromString('https://gitamp.audio'))
    ->bind(new ServerAddress('127.0.0.1', 1337))
    ->addSpecialRepository('ekinhbayar/gitamp')
    ->addSpecialRepository('amphp/amp')
;
