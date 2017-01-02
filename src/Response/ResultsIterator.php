<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Response;


class ResultsIterator implements \Iterator
{
    private $results = [];

    public function __construct(array $results) {
        $this->results = $results;
    }

    function rewind() {
        reset($this->results);
    }

    function current() {
        return current($this->results);
    }

    function key() {
        return key($this->results);
    }

    function next() {
        return next($this->results);
    }

    function valid() {
        return key($this->results) !== null;
    }

    function last() {
        return $this->results[count($this->results)-1];
    }
}