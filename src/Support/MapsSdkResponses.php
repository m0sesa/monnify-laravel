<?php

namespace Monnify\MonnifyLaravel\Support;

use Monnify\MonnifyException;

trait MapsSdkResponses
{
    /**
     * @param callable(): array<array-key, mixed> $request
     * @return array{status: int|null, body?: array<array-key, mixed>, error?: mixed}
     */
    private function mapSdkResponse(LaravelHttpClient $client, callable $request): array
    {
        try {
            $body = $request();

            return [
                'status' => $client->lastStatusCode() ?? 200,
                'body' => $body,
            ];
        } catch (MonnifyException $e) {
            return [
                'status' => $e->statusCode(),
                'error' => $this->mapCoreError($e),
            ];
        }
    }

    private function mapCoreError(MonnifyException $e): mixed
    {
        $rawBody = $e->rawResponseBody();
        if ($rawBody !== null && $rawBody !== '') {
            $decoded = json_decode($rawBody);

            if ($decoded !== null) {
                return $decoded;
            }
        }

        $responseBody = $e->responseBody();
        if ($responseBody !== null) {
            return json_decode(json_encode($responseBody, JSON_THROW_ON_ERROR));
        }

        if ($rawBody !== null && $rawBody !== '') {
            return (object) ['message' => $rawBody];
        }

        return (object) ['message' => $e->getMessage()];
    }
}
