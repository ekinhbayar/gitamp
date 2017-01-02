<?php

namespace ekinhbayar\GitAmp\Events;

interface Event
{
    public function getType(): string;
    public function getId(): string ;
}