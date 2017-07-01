<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmpTests\Events\Type;

use ekinhbayar\GitAmp\Events\Type\IssueCommentEvent;
use PHPUnit\Framework\TestCase;

class IssueCommentEventTest extends TestCase
{
    private $event;

    private $assertEvent;

    public function setUp()
    {
        $this->event = [
            'id'      => 1,
            'repo'    => ['name' => 'test/repo'],
            'actor'   => ['login' => 'PeeHaa'],
            'payload' => [
                'action' => 'The action',
                'issue'  => ['html_url' => 'http://example.com', 'title' => 'Issue title'],
            ],
            'comment' => ['body' => 'Comment body'],
        ];

        $this->assertEvent = [
            'id'        => 1,
            'type'      => 4,
            'information' => [
                'url' => 'http://example.com',
                'payload' => 'Comment body',
                'message' => 'PeeHaa commented in test/repo',
            ],
            'ring'  => [
                'animationDuration' => 3000,
                'radius'            => 80,
            ],
            'sound' => [
                'size' => strlen('Comment body') * 1.1,
                'type' => 'Clav',
            ],
        ];
    }

    public function testGetAsArrayWithCommentBody()
    {
        $event = new IssueCommentEvent($this->event);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }

    public function testGetAsArrayWithoutCommentBody()
    {
        unset($this->event['comment']['body']);

        $this->assertEvent['information']['payload'] = 'Issue title';
        $this->assertEvent['sound']['size']          = strlen('Issue title') * 1.1;

        $event = new IssueCommentEvent($this->event);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }

    public function testGetAsArrayWithCommentBodyEgg()
    {
        $this->event['repo']['name'] = 'ekinhbayar/gitamp';

        $this->assertEvent['information']['url']     = 'http://example.com';
        $this->assertEvent['information']['message'] = 'PeeHaa commented in ekinhbayar/gitamp';
        $this->assertEvent['sound']['type']          = 'ClavEgg';
        $this->assertEvent['sound']['size']          = 1.0;

        $event = new IssueCommentEvent($this->event);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }

    public function testGetAsArrayWithoutCommentBodyEgg()
    {
        $this->event['repo']['name'] = 'ekinhbayar/gitamp';

        $this->assertEvent['information']['url']     = 'http://example.com';
        $this->assertEvent['information']['message'] = 'PeeHaa commented in ekinhbayar/gitamp';
        $this->assertEvent['sound']['type']          = 'ClavEgg';
        $this->assertEvent['sound']['size']          = 1.0;

        unset($this->event['comment']['body']);

        $this->assertEvent['information']['payload'] = 'Issue title';

        $event = new IssueCommentEvent($this->event);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }
}
