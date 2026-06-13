<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Support\BuildsCoreClient;
use Monnify\MonnifyLaravel\Support\LaravelHttpClient;
use Monnify\MonnifyLaravel\Support\MapsSdkResponses;
use Monnify\MonnifyLaravel\Validators\CustomerReservedAccountValidator;
use Monnify\Services\CustomerReservedAccountService as CoreCustomerReservedAccountService;

class CustomerReservedAccountService
{
    use BuildsCoreClient;
    use MapsSdkResponses;

    private CustomerReservedAccountValidator $validator;
    private LaravelHttpClient $laravelHttpClient;
    private CoreCustomerReservedAccountService $coreService;

    public function __construct(
        Client $client,
        ?CustomerReservedAccountValidator $validator = null,
        ?CoreCustomerReservedAccountService $coreService = null,
        ?LaravelHttpClient $laravelHttpClient = null,
    ) {
        $this->validator = $validator ?? new CustomerReservedAccountValidator();
        $this->laravelHttpClient = $laravelHttpClient ?? new LaravelHttpClient($client);
        $this->coreService = $coreService ?? new CoreCustomerReservedAccountService($this->buildCoreClient($client, $this->laravelHttpClient));
    }

    public function createGeneralAccount(array $data): array
    {
        $this->validator->validateCreateGeneralAccount($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->createGeneralAccount($data),
        );
    }

    public function createInvoiceAccount(array $data): array
    {
        $this->validator->validateCreateInvoiceAccount($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->createInvoiceAccount($data),
        );
    }

    public function get(string $accountReference): array
    {
        if (empty($accountReference)) {
            throw new InvalidArgumentException('Account Reference must be provided');
        }

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->get($accountReference),
        );
    }

    public function addLinkedAccounts(string $accountReference, array $data = []): array
    {
        if (empty($accountReference)) {
            throw new InvalidArgumentException('Account Reference must be provided');
        }

        $this->validator->validateAddLinkedAccounts($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->addLinkedAccounts($accountReference, $data),
        );
    }

    public function updateBVN(string $accountReference, string $bvn): array
    {
        if (empty($accountReference)) {
            throw new InvalidArgumentException('Account Reference must be provided');
        }

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->updateBVN($accountReference, $bvn),
        );
    }

    public function allowedPaymentSource(string $accountReference, array $data): array
    {
        if (empty($accountReference)) {
            throw new InvalidArgumentException('Account Reference must be provided');
        }

        $this->validator->validateAllowedPaymentSource($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->allowedPaymentSource($accountReference, $data),
        );
    }

    public function updateSplitConfig(string $accountReference, array $data): array
    {
        if (empty($accountReference)) {
            throw new InvalidArgumentException('Account Reference must be provided');
        }

        $this->validator->validateUpdateSplitConfig($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->updateSplitConfig($accountReference, $data),
        );
    }

    public function deallocateAccount(string $accountReference): array
    {
        if (empty($accountReference)) {
            throw new InvalidArgumentException('Account Reference must be provided');
        }

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->deallocateAccount($accountReference),
        );
    }

    public function transactions(string $accountReference, array $parameters = []): array
    {
        if (empty($accountReference)) {
            throw new InvalidArgumentException('Account Reference must be provided');
        }

        $this->validator->validateGetReservedAccountTransactions($parameters);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->transactions($accountReference, $parameters),
        );
    }

    public function updateKYCInfo(string $accountReference, array $data): array
    {
        if (empty($accountReference)) {
            throw new InvalidArgumentException('Account Reference must be provided');
        }
        
        $this->validator->validateUpdateKYCInfo($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->updateKYCInfo($accountReference, $data),
        );
    }
}
