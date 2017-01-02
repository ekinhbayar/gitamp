<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Github;

interface Credentials
{
    public function getAuthenticationString(): string;
}
