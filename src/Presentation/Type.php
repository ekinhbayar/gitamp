<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Presentation;

class Type
{
    public const PUSH_TO_REPOSITORY = 1;
    public const PR_ACTION          = 2;
    public const ISSUE_ACTION       = 3;
    public const COMMENTED_ON_ISSUE = 4;
    public const REPOSITORY_FORKED  = 5;
    public const REPOSITORY_CREATED = 6;
    public const STARTED_WATCHING   = 7;

    private int $type;

    public function __construct(int $type)
    {
        $this->type = $type;
    }

    public function getValue(): int
    {
        return $this->type;
    }
}
