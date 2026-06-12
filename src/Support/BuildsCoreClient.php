<?php

namespace Monnify\MonnifyLaravel\Support;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Monnify\Http\MonnifyApiClient;

trait BuildsCoreClient
{
    private function buildCoreClient(Client $client, ?LaravelHttpClient $laravelHttpClient = null): MonnifyApiClient
    {
        return new MonnifyApiClient(
            MonnifyConfigFactory::make(),
            $laravelHttpClient ?? new LaravelHttpClient($client),
            new LaravelTokenCache(Cache::store()),
        );
    }
}
