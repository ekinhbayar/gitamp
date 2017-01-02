<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Github;

class Token implements Credentials
{
    private $token;

    public function __construct(string $token)
    {
        $this->token= $token;
    }

    public function getAuthenticationString(): string
    {
        return $this->token;
    }
}
