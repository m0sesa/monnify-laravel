<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Support;

use Monnify\MonnifyLaravel\Support\MonnifyConfigFactory;
use Monnify\MonnifyLaravel\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MonnifyConfigFactoryTest extends TestCase
{
    #[Test]
    public function it_allows_core_config_without_a_contract_code_for_helper_requests(): void
    {
        $this->app['config']->set('monnify.contract_code', null);

        $config = MonnifyConfigFactory::make();

        $this->assertSame('not-required', $config->contractCode);
    }

    #[Test]
    public function it_allows_core_config_without_credentials_until_a_request_is_made(): void
    {
        $this->app['config']->set('monnify.api_key', null);
        $this->app['config']->set('monnify.secret_key', null);

        $config = MonnifyConfigFactory::make();

        $this->assertSame('not-required', $config->apiKey);
        $this->assertSame('not-required', $config->secretKey);
    }
}
