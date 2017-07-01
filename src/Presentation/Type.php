<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Presentation;

class Type
{
    private $type;

    public function __construct(int $type) {
        $this->type = $type;
    }

    public function getValue(): int
    {
        return $this->type;
    }
}
