<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Event;

class Factory
{
    /** @var array<string> */
    private array $specialRepositories = [];

    /**
     * @param array<string> $specialRepositories
     */
    public function __construct(array $specialRepositories)
    {
        $this->specialRepositories = $specialRepositories;
    }

    public function build(string $namespace, array $event): Event
    {
        $eventType = $namespace . '\\' . $event['type'];

        if (!$this->isValidType($eventType)) {
            throw new UnknownEventException($event['type']);
        }

        return new $eventType($event, $this->specialRepositories);
    }

    private function isValidType(string $type): bool
    {
        return \class_exists($type);
    }
}
