<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Github;

class User implements Credentials
{
    private $username;

    private $password;

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function getAuthenticationString(): string
    {
        return base64_encode($this->username . ':' . $this->password);
    }
}
