<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmpTests\Response;

use Amp\Artax\Response;
use PHPUnit\Framework\TestCase;
use ekinhbayar\GitAmp\Events\Factory;
use ekinhbayar\GitAmp\Response\Results;
use ekinhbayar\GitAmp\Response\DecodingFailedException;

class ResultsTest extends TestCase
{
    public function testAppendResponseThrowsOnInvalidJSON()
    {
        $response = $this->createMock(Response::class);

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue('[{"message"}]'))
        ;

        $this->expectException(DecodingFailedException::class);
        $this->expectExceptionMessage('Failed to decode response body as JSON');

        (new Results(new Factory()))->appendResponse($response);
    }
}
