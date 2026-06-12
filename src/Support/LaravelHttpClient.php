<?php

namespace Monnify\MonnifyLaravel\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use JsonException;
use Monnify\Contracts\HttpClientInterface;
use Monnify\MonnifyException;

final class LaravelHttpClient implements HttpClientInterface
{
    private ?int $lastStatusCode = null;

    public function __construct(private Client $client)
    {
    }

    /**
     * @param array<string, mixed> $options
     * @return array<array-key, mixed>
     */
    public function request(string $method, string $uri, array $options = []): array
    {
        $this->lastStatusCode = null;

        try {
            $response = $this->client->request($method, $uri, $options);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $rawBody = $response !== null ? (string) $response->getBody() : null;

            throw new MonnifyException(
                message: 'Monnify HTTP request failed: ' . $e->getMessage(),
                code: (int) $e->getCode(),
                previous: $e,
                statusCode: $response?->getStatusCode(),
                responseBody: $rawBody !== null ? $this->tryDecodeBody($rawBody) : null,
                rawResponseBody: $rawBody,
            );
        } catch (ConnectException $e) {
            throw new MonnifyException(
                message: 'Monnify HTTP request failed: ' . $e->getMessage(),
                code: (int) $e->getCode(),
                previous: $e,
                responseBody: [
                    'type' => 'network_error',
                    'message' => $e->getMessage(),
                ],
            );
        } catch (TransferException $e) {
            throw new MonnifyException(
                message: 'Monnify HTTP request failed: ' . $e->getMessage(),
                code: (int) $e->getCode(),
                previous: $e,
                responseBody: ['message' => $e->getMessage()],
            );
        }

        $this->lastStatusCode = $response->getStatusCode();

        return $this->decodeBody((string) $response->getBody());
    }

    public function lastStatusCode(): ?int
    {
        return $this->lastStatusCode;
    }

    /**
     * @return array<array-key, mixed>
     */
    private function decodeBody(string $body): array
    {
        if ($body === '') {
            return [];
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new MonnifyException('Invalid JSON response from Monnify.', 0, $e);
        }

        if (! is_array($data)) {
            throw new MonnifyException('Invalid JSON response from Monnify.');
        }

        return $data;
    }

    /**
     * @return array<array-key, mixed>|null
     */
    private function tryDecodeBody(string $body): ?array
    {
        if ($body === '') {
            return [];
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return is_array($data) ? $data : null;
    }
}
