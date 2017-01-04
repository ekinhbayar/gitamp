<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Events\Type;

class ForkEvent extends BaseEvent
{
    public function __construct(array $event)
    {
        parent::__construct(
            (int) $event['id'],
            self::class,
            'forked',
            $event['repo']['name'],
            $event['actor']['login'],
            $this->buildUrl($event),
            $this->buildMessage()
        );
    }

    private function buildUrl(array $event): string
    {
        return 'https://github.com/' . $event['repo']['name'];
    }

    private function buildMessage(): string
    {
        return 'not sure if stupid but works anyway';
    }
}
