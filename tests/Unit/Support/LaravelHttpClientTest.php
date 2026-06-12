<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Support;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Monnify\MonnifyException;
use Monnify\MonnifyLaravel\Support\LaravelHttpClient;
use Monnify\MonnifyLaravel\Tests\Support\CreatesMockClient;
use Monnify\MonnifyLaravel\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LaravelHttpClientTest extends TestCase
{
    use CreatesMockClient;

    #[Test]
    public function it_preserves_status_and_raw_body_for_non_json_http_errors(): void
    {
        $client = new LaravelHttpClient($this->makeClient([
            new Response(502, [], 'Bad Gateway'),
        ]));

        try {
            $client->request('GET', '/api/v1/banks');
            $this->fail('Expected MonnifyException to be thrown.');
        } catch (MonnifyException $e) {
            $this->assertSame(502, $e->statusCode());
            $this->assertSame('Bad Gateway', $e->rawResponseBody());
            $this->assertNull($e->responseBody());
        }
    }

    #[Test]
    public function it_preserves_network_error_type_for_connection_failures(): void
    {
        $client = new LaravelHttpClient($this->makeClient([
            new ConnectException('Connection failed', new Request('GET', '/api/v1/banks')),
        ]));

        try {
            $client->request('GET', '/api/v1/banks');
            $this->fail('Expected MonnifyException to be thrown.');
        } catch (MonnifyException $e) {
            $this->assertSame(['type' => 'network_error', 'message' => 'Connection failed'], $e->responseBody());
        }
    }
}
