<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Support\BuildsCoreClient;
use Monnify\MonnifyLaravel\Support\LaravelHttpClient;
use Monnify\MonnifyLaravel\Support\MapsSdkResponses;
use Monnify\Services\SettlementService as CoreSettlementService;

class SettlementService extends BaseService
{
    use BuildsCoreClient;
    use MapsSdkResponses;

    private LaravelHttpClient $laravelHttpClient;
    private CoreSettlementService $coreService;

    public function __construct(
        Client $client,
        ?CoreSettlementService $coreService = null,
        ?LaravelHttpClient $laravelHttpClient = null,
    ) {
        parent::__construct($client);
        $this->laravelHttpClient = $laravelHttpClient ?? new LaravelHttpClient($client);
        $this->coreService = $coreService ?? new CoreSettlementService($this->buildCoreClient($client, $this->laravelHttpClient));
    }

    public function transactions(string $settlementReference, int $pageSize = 10, int $pageNumber = 0): array
    {
        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->transactions($settlementReference, $pageSize, $pageNumber),
        );
    }

    public function getByTransaction(string $transactionReference): array
    {
        if (empty($transactionReference)) {
            throw new InvalidArgumentException('Transaction Reference must be provided.');
        }

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->getByTransaction($transactionReference),
        );
    }
}
