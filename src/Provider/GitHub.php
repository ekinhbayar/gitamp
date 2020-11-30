<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Provider;

use Amp\Http\Client\DelegateHttpClient;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Amp\NullCancellationToken;
use Amp\Promise;
use ekinhbayar\GitAmp\Exception\RequestFailed;
use ekinhbayar\GitAmp\Github\Credentials;
use ekinhbayar\GitAmp\Response\Factory;
use Psr\Log\LoggerInterface;
use function Amp\call;

class GitHub implements Listener
{
    private const EVENT_NAMESPACE = 'ekinhbayar\GitAmp\Event\GitHub';

    private const API_ENDPOINT = 'https://api.github.com/events';
    private const API_VERSION  = 'v3';

    private DelegateHttpClient $client;

    private Credentials $credentials;

    private Factory $resultFactory;

    private LoggerInterface $logger;

    public function __construct(
        DelegateHttpClient $client,
        Credentials $credentials,
        Factory $resultFactory,
        LoggerInterface $logger
    ) {
        $this->client        = $client;
        $this->credentials   = $credentials;
        $this->resultFactory = $resultFactory;
        $this->logger        = $logger;
    }

    private function request(): Promise
    {
        return call(function () {
            try {
                $request = new Request(self::API_ENDPOINT, 'GET');

                $request->setHeaders($this->getAuthHeader());

                $response = yield $this->client->request($request, new NullCancellationToken());
            } catch (\Throwable $e) {
                $this->logger->error('Failed to send GET request to API endpoint', ['exception' => $e]);

                throw new RequestFailed('Failed to send GET request to API endpoint', $e->getCode(), $e);
            }

            /** @var Response $result */
            if ($response->getStatus() !== 200) {
                $message = \sprintf(
                    'A non-200 response status (%s - %s) was encountered',
                    $response->getStatus(),
                    $response->getReason(),
                );

                $this->logger->critical($message, ['response' => $response]);

                throw new RequestFailed($message);
            }

            return $response;
        });
    }

    public function listen(): Promise
    {
        return call(function () {
            $response = yield $this->request();

            return yield $this->resultFactory->buildFromResponse(self::EVENT_NAMESPACE, $response);
        });
    }

    private function getAuthHeader(): array
    {
        return [
            'Accept'        => \sprintf('application/vnd.github.%s+json', self::API_VERSION),
            'Authorization' => \sprintf('Bearer %s', $this->credentials->getAuthenticationString()),
        ];
    }
}
