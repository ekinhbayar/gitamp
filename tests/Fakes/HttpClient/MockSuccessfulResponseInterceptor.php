<?php declare(strict_types=1);

namespace ekinhbayar\GitAmpTests\Fakes\HttpClient;

use Amp\ByteStream\InMemoryStream;
use Amp\CancellationToken;
use Amp\Http\Client\ApplicationInterceptor;
use Amp\Http\Client\DelegateHttpClient;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Amp\Promise;
use Amp\Success;

final class MockSuccessfulResponseInterceptor implements ApplicationInterceptor
{
    private string $body;

    public function __construct(string $body)
    {
        $this->body = $body;
    }

    /**
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     *
     * @return Promise<Response>
     */
    public function request(Request $request, CancellationToken $cancellation, DelegateHttpClient $client): Promise
    {
        // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        $body = new InMemoryStream($this->body);

        return new Success(new Response('2', 200, 'OK', [], $body, $request));
    }
}
