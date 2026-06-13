<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Support\BuildsCoreClient;
use Monnify\MonnifyLaravel\Support\LaravelHttpClient;
use Monnify\MonnifyLaravel\Support\MapsSdkResponses;
use Monnify\MonnifyLaravel\Validators\SubAccountValidator;
use Monnify\Services\SubAccountService as CoreSubAccountService;

class SubAccountService extends BaseService
{
    use BuildsCoreClient;
    use MapsSdkResponses;

    private SubAccountValidator $validator;
    private LaravelHttpClient $laravelHttpClient;
    private CoreSubAccountService $coreService;

    public function __construct(
        Client $client,
        ?SubAccountValidator $validator = null,
        ?CoreSubAccountService $coreService = null,
        ?LaravelHttpClient $laravelHttpClient = null,
    ) {
        parent::__construct($client);
        $this->validator = $validator ?? new SubAccountValidator();
        $this->laravelHttpClient = $laravelHttpClient ?? new LaravelHttpClient($client);
        $this->coreService = $coreService ?? new CoreSubAccountService($this->buildCoreClient($client, $this->laravelHttpClient));
    }

    public function create(array $data): array
    {
        $this->validator->validateAccount($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->create($data),
        );
    }

    public function all(): array
    {
        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->all(),
        );
    }

    public function update(array $data): array
    {
        $this->validator->validateAccount([$data]);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->update($data),
        );
    }

    public function delete(string $subAccountCode): array
    {
        if (empty($subAccountCode)) {
            throw new InvalidArgumentException('Sub Account Code must be provided');
        }

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->delete($subAccountCode),
        );
    }
}
