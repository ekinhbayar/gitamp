<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Events\Type;

class PushEvent extends BaseEvent
{
    public function __construct(array $event)
    {
        parent::__construct(
            (int) $event['id'],
            'PushEvent',
            'pushed to',
            $event['repo']['name'],
            $event['actor']['login'],
            $this->buildUrl($event),
            $this->buildMessage($event)
        );
    }

    private function buildUrl(array $event): string
    {
        return 'https://github.com/' . $event['repo']['name'];
    }

    private function buildMessage(array $event): string
    {
        if (isset($event['payload']['commits'][0]['message'])) {
            return $event['payload']['commits'][0]['message'];
        }

        return 'https://github.com/' . $event['actor']['login'];
    }
}
