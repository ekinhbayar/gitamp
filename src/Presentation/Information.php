<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Presentation;

class Information
{
    private string $url;

    private string $payload;

    private string $message;

    public function __construct(string $url, string $payload, string $message)
    {
        $this->url     = $url;
        $this->payload = $payload;
        $this->message = \ucfirst($message);
    }

    public function getAsArray(): array
    {
        return [
            'url'     => $this->url,
            'payload' => $this->payload,
            'message' => $this->message,
        ];
    }
}
