<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use Monnify\MonnifyLaravel\Support\BuildsCoreClient;
use Monnify\MonnifyLaravel\Support\LaravelHttpClient;
use Monnify\MonnifyLaravel\Support\MapsSdkResponses;
use Monnify\Services\OtherService as CoreOtherService;

class OtherService extends BaseService
{
    use BuildsCoreClient;
    use MapsSdkResponses;

    private LaravelHttpClient $laravelHttpClient;
    private CoreOtherService $coreService;

    public function __construct(
        Client $client,
        ?CoreOtherService $coreService = null,
        ?LaravelHttpClient $laravelHttpClient = null,
    ) {
        parent::__construct($client);
        $this->laravelHttpClient = $laravelHttpClient ?? new LaravelHttpClient($client);
        $this->coreService = $coreService ?? new CoreOtherService($this->buildCoreClient($client, $this->laravelHttpClient));
    }
    
    public function banks(): array
    {
        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->banks(),
        );
    }

    public function banksWithUSSD(): array
    {
        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->banksWithUSSD(),
        );
    }
}
