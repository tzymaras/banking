<?php
namespace App\Services;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class TransactionHttpClient
{

    private Client $guzzleClient;
    private string $externalTransfersUrl;

    /**
     * @param Client $guzzleClient
     * @param string $externalTransfersUrl
     */
    public function __construct(Client $guzzleClient, string $externalTransfersUrl)
    {
        $this->guzzleClient         = $guzzleClient;
        $this->externalTransfersUrl = $externalTransfersUrl;
    }

    /**
     * @param array $payload
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     * @return ResponseInterface
     */
    public function makeTransactionRequest(array $payload): ResponseInterface
    {
        $body = \json_encode($payload, \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR);

        return $this->guzzleClient->post($this->externalTransfersUrl, [
            'headers' => [
                'Accpet'         => 'application/json',
                'Content-Type'   => 'application/json',
                'Content-Length' => \strlen($body)
            ],
            'json'    => $body,
            'verify'  => false,
        ]);
    }
}