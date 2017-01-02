<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Events;


class EventsIterator implements \Iterator
{
    private $events = [];

    public function __construct(array $events) {
        $this->events = $events;
    }

    function rewind() {
        reset($this->events);
    }

    function current() {
        return current($this->events);
    }

    function key() {
        return key($this->events);
    }

    function next() {
        return next($this->events);
    }

    function valid() {
        return key($this->events) !== null;
    }

    function last() {
        return $this->events[count($this->events)-1];
    }
}