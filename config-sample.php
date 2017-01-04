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
    'expose' => 1337,
    /**
     * Either use a token or a username + password login.
     * Note for 2fa users, you can only use tokens.
     * https://github.com/settings/tokens
     */
    'github' => new Token('your-token'),
    //'github' => new User('your-username', 'your-password'),
];