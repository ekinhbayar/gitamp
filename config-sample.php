<?php

use ekinhbayar\GitAmp\Github\Token;
use ekinhbayar\GitAmp\Github\User;



return [
    'origins' => [
        'websocket' => 'localhost:1337',
        'server' => 'localhost',
    ],
    'redis' => [
        'hostname' => 'localhost',
        'port'     => 6379,
    ],
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
     * Either use a token or a username + password login.
     * Note for 2fa users, you can only use tokens.
     * https://github.com/settings/tokens
     */
    'github' => new Token('your-token'),
    //'github' => new User('your-username', 'your-password'),
];