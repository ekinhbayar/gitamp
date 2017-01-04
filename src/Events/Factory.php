<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Events;

class Factory
{
    public function build(array $event): Event
    {
        $eventType = 'ekinhbayar\GitAmp\Events\Type\\' . $event['type'];

        if (!$this->isValidType($eventType)) {
            throw new UnknownEventException($event['type']);
        }

        return new $eventType($event);
    }

    private function isValidType(string $type): bool
    {
        return class_exists($type);
    }
}
