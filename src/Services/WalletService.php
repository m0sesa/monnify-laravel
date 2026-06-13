<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Support\BuildsCoreClient;
use Monnify\MonnifyLaravel\Support\LaravelHttpClient;
use Monnify\MonnifyLaravel\Support\MapsSdkResponses;
use Monnify\MonnifyLaravel\Validators\WalletValidator;
use Monnify\Services\WalletService as CoreWalletService;

class WalletService
{
    use BuildsCoreClient;
    use MapsSdkResponses;

    private WalletValidator $validator;
    private LaravelHttpClient $laravelHttpClient;
    private CoreWalletService $coreService;

    public function __construct(
        Client $client,
        ?WalletValidator $validator = null,
        ?CoreWalletService $coreService = null,
        ?LaravelHttpClient $laravelHttpClient = null,
    ) {
        $this->validator = $validator ?? new WalletValidator();
        $this->laravelHttpClient = $laravelHttpClient ?? new LaravelHttpClient($client);
        $this->coreService = $coreService ?? new CoreWalletService($this->buildCoreClient($client, $this->laravelHttpClient));
    }

    public function create(array $data): array
    {
        $this->validator->validateCreate($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->create($data),
        );
    }

    public function get(string $customerEmail, int $pageSize = 10, int $pageNumber = 0): array
    {
        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->get($customerEmail, $pageSize, $pageNumber),
        );
    }

    public function balance(string $accountNumber): array
    {
        if (empty($accountNumber)) {
            throw new InvalidArgumentException('Account Number must provided.');
        }

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->balance($accountNumber),
        );
    }

    public function transactions(string $accountNumber, int $pageSize = 10, int $pageNumber = 0): array
    {
        if (empty($accountNumber)) {
            throw new InvalidArgumentException('Account Number must provided.');
        }

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->transactions($accountNumber, $pageSize, $pageNumber),
        );
    }
}
