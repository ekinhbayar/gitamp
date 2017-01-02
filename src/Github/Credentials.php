<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Github;

class Credentials
{
    private $username;
    private $password;
    private $token;

    public function __construct(
        string $username = null,
        string $password = null,
        string $token = null
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->token = $token;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
