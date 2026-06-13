<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Support\BuildsCoreClient;
use Monnify\MonnifyLaravel\Support\LaravelHttpClient;
use Monnify\MonnifyLaravel\Support\MapsSdkResponses;
use Monnify\MonnifyLaravel\Validators\LimitProfileValidator;
use Monnify\Services\LimitProfileService as CoreLimitProfileService;

class LimitProfileService extends BaseService
{
    use BuildsCoreClient;
    use MapsSdkResponses;

    private LimitProfileValidator $validator;
    private LaravelHttpClient $laravelHttpClient;
    private CoreLimitProfileService $coreService;

    public function __construct(
        Client $client,
        ?LimitProfileValidator $validator = null,
        ?CoreLimitProfileService $coreService = null,
        ?LaravelHttpClient $laravelHttpClient = null,
    ) {
        parent::__construct($client);
        $this->validator = $validator ?? new LimitProfileValidator();
        $this->laravelHttpClient = $laravelHttpClient ?? new LaravelHttpClient($client);
        $this->coreService = $coreService ?? new CoreLimitProfileService($this->buildCoreClient($client, $this->laravelHttpClient));
    }

    public function all(): array
    {
        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->all(),
        );
    }

    public function create(array $data): array
    {
        $this->validator->validateLimitProfile($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->create($data),
        );
    }

    public function update(string $limitProfileCode, array $data): array
    {
        if (empty($limitProfileCode)) {
            throw new InvalidArgumentException('Limit Profile Code must be provided.');
        }

        $this->validator->validateLimitProfile($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->update($limitProfileCode, $data),
        );
    }

    public function reserveAccount(array $data): array
    {
        $this->validator->validateReserveAccount($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->reserveAccount($data),
        );
    }

    public function updateReserveAccount(string $accountReference, string $limitProfileCode): array
    {
        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->updateReserveAccount($accountReference, $limitProfileCode),
        );
    }
}
