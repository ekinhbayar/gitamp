<?php

use ekinhbayar\GitAmp\Github\Token;
use ekinhbayar\GitAmp\Github\User;

return [
    'server' => [
        'hostname' => 'localhost',
        'port'     => 1337,
    ],
    'redis' => [
        'hostname' => 'localhost',
        'port'     => 6379,
    ],
    // either use a tokenn or a username + password login
    // note for 2fa users. you can only use tokens
    // https://github.com/settings/tokens
    'github' => new Token('your-token'),
    //'github' => new User('your-username', 'your-password'),
];
