<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Services;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Cache;
use Monnify\MonnifyLaravel\Services\OtherService;
use Monnify\MonnifyLaravel\Tests\Support\CreatesMockClient;
use Monnify\MonnifyLaravel\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class OtherServiceTest extends TestCase
{
    use CreatesMockClient;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::put('monnify_access_token', 'cached-token', 300);
    }

    protected function tearDown(): void
    {
        Cache::forget('monnify_access_token');

        parent::tearDown();
    }

    #[Test]
    public function banks_uses_the_expected_endpoint(): void
    {
        $history = [];
        $service = new OtherService($this->makeClient([
            new Response(200, [], json_encode(['responseBody' => []])),
        ], $history));

        $service->banks();

        $this->assertSame('/api/v1/banks', $history[0]['request']->getUri()->getPath());
    }

    #[Test]
    public function banks_preserves_non_json_http_error_status(): void
    {
        $service = new OtherService($this->makeClient([
            new Response(502, [], 'Bad Gateway'),
        ]));

        $result = $service->banks();

        $this->assertSame(502, $result['status']);
        $this->assertSame('Bad Gateway', $result['error']->message);
    }

    #[Test]
    public function banks_preserves_network_error_envelope(): void
    {
        $service = new OtherService($this->makeClient([
            new ConnectException('Connection failed', new Request('GET', '/api/v1/banks')),
        ]));

        $result = $service->banks();

        $this->assertNull($result['status']);
        $this->assertSame('network_error', $result['error']->type);
        $this->assertSame('Connection failed', $result['error']->message);
    }

    #[Test]
    public function banks_with_ussd_uses_the_expected_endpoint(): void
    {
        $history = [];
        $service = new OtherService($this->makeClient([
            new Response(200, [], json_encode(['responseBody' => []])),
        ], $history));

        $service->banksWithUSSD();

        $this->assertSame('/api/v1/sdk/transactions/banks', $history[0]['request']->getUri()->getPath());
    }
}
