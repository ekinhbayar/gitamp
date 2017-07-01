<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Event;

class Factory
{
    public function build(string $namespace, array $event): Event
    {
        $eventType = $namespace . '\\' . $event['type'];

        if (!$this->isValidType($eventType)) {
            throw new UnknownEventException($event['type']);
        }

        return new $eventType($event);
    }

    private function isValidType(string $type): bool
    {
        return \class_exists($type);
    }
}
