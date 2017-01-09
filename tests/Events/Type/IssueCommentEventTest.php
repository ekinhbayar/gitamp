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
            'type'      => 'IssueCommentEvent',
            'action'    => 'The action',
            'repoName'  => 'test/repo',
            'actorName' => 'PeeHaa',
            'eventUrl'  => 'http://example.com',
            'message'   => 'Comment body'
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

        $this->assertEvent['message'] = 'Issue title';

        $event = new IssueCommentEvent($this->event);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }
}
