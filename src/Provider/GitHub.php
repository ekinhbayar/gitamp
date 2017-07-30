<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Provider;

use Amp\Artax\Response;
use Amp\Promise;
use Amp\Artax\Client;
use Amp\Artax\HttpException;
use Amp\Artax\Request;
use ekinhbayar\GitAmp\Response\Factory;
use ekinhbayar\GitAmp\Github\Credentials;
use Psr\Log\LoggerInterface;

class GitHub implements Listener
{
    const EVENT_NAMESPACE = 'ekinhbayar\GitAmp\Event\GitHub';

    const API_ENDPOINT = 'https://api.github.com/events';

    private $client;

    private $credentials;

    private $resultFactory;

    private $logger;

    public function __construct(
        Client $client,
        Credentials $credentials,
        Factory $resultFactory,
        LoggerInterface $logger
    )
    {
        $this->client        = $client;
        $this->credentials   = $credentials;
        $this->resultFactory = $resultFactory;
        $this->logger        = $logger;
    }

    private function request(): \Generator
    {
        try {
            $request = (new Request(self::API_ENDPOINT, 'GET'))
                ->withHeaders($this->getAuthHeader());

            $response = yield $this->client->request($request);
        } catch (HttpException $e) {
            $this->logger->error('Failed to send GET request to API endpoint', ['exception' => $e]);

            throw new RequestFailedException('Failed to send GET request to API endpoint', $e->getCode(), $e);
        }

        /** @var Response $result */
        if ($response->getStatus() !== 200) {
            $message = \sprintf(
                'A non-200 response status (%s - %s) was encountered',
                $response->getStatus(),
                $response->getReason()
            );

            $this->logger->critical($message, ['response' => $response]);

            throw new RequestFailedException($message);
        }

        return $response;
    }

    public function listen(): Promise
    {
        return \Amp\call(function() {
            $response = yield from $this->request();

            return yield $this->resultFactory->build(self::EVENT_NAMESPACE, $response);
        });
    }

    private function getAuthHeader(): array
    {
        return ['Authorization' => \sprintf('Basic %s', $this->credentials->getAuthenticationString())];
    }
}
