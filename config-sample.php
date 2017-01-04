<?php

use ekinhbayar\GitAmp\Github\Token;
use ekinhbayar\GitAmp\Github\User;

return [
    'origins' => [
        'websocket' => [
            'hostname' => 'localhost',
            'port'     => 1337
        ],
        'server' => [
            'hostname' => 'localhost',
            'port'     => 1337
        ],
    ],
    'redis' => [
        'hostname' => 'localhost',
        'port'     => 6379,
    ],
    # port to expose the virtual host
    'expose' => 1337,
    /**
     * Either use a token or a username + password login.
     * Note for 2fa users, you can only use tokens.
     * https://github.com/settings/tokens
     */
    'github' => new Token('your-token')
    //'github' => new User('your-username', 'your-password'),
];