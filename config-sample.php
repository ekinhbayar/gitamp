<?php

use Monolog\Logger;
use ekinhbayar\GitAmp\Github\Token;

return [
    'logLevel' => Logger::INFO,
    'hostname' => 'localhost',
    /**
     * Assign the IP and port on which to listen.
     * Use :
        "0.0.0.0" for all IPv4 interfaces
        "::"      for all IPv6 interfaces
        "*"       for all IPv4 & IPv6
     *
     */
    'expose' => [
        'ip' => '*',
        'port' => 1337
    ],
    /**
     * Uncomment to use ssl
    'ssl' => [
        'ip' => '*',
        'port' => 443,
        'certificate' => '/path/to/certificate.pem',
        'key' => '/path/to/key.pem',
    ],
     */
    /**
     * Use a personal access token for authentication
     * https://github.com/settings/tokens
     */
    'github' => new Token('your-token'),
];
