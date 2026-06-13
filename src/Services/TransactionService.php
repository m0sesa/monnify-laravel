<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Support\BuildsCoreClient;
use Monnify\MonnifyLaravel\Support\LaravelHttpClient;
use Monnify\MonnifyLaravel\Support\MapsSdkResponses;
use Monnify\MonnifyLaravel\Validators\TransactionValidator;
use Monnify\Services\TransactionService as CoreTransactionService;

class TransactionService
{
    use BuildsCoreClient;
    use MapsSdkResponses;

    private TransactionValidator $validator;
    private LaravelHttpClient $laravelHttpClient;
    private CoreTransactionService $coreService;

    public function __construct(
        Client $client,
        ?TransactionValidator $validator = null,
        ?CoreTransactionService $coreService = null,
        ?LaravelHttpClient $laravelHttpClient = null,
    ) {
        $this->validator = $validator ?? new TransactionValidator();
        $this->laravelHttpClient = $laravelHttpClient ?? new LaravelHttpClient($client);
        $this->coreService = $coreService ?? new CoreTransactionService($this->buildCoreClient($client, $this->laravelHttpClient));
    }

    public function initialise(array $data): array
    {
        $this->validator->validateInitialize($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->initialise($data),
        );
    }

    public function payWithBankTransfer(array $data): array
    {
        $this->validator->validatePayWithBankTransfer($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->payWithBankTransfer($data),
        );
    }

    public function chargeCard(array $data): array
    {
        $this->validator->validateChargeCard($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->chargeCard($data),
        );
    }

    public function authorizeOTP(array $data): array
    {
        $this->validator->validateAuthorizeOTP($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->authorizeOTP($data),
        );
    }

    public function authorizeThreeDSCard(array $data): array
    {
        $this->validator->validateAuthorizeThreeDSCard($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->authorizeThreeDSCard($data),
        );
    }

    public function all(array $parameters = []): array
    {
        $this->validator->validateGetAllTransactions($parameters);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->all($parameters),
        );
    }

    public function status(string $transactionReference): array
    {
        if (empty($transactionReference)) {
            throw new InvalidArgumentException('Transaction Reference must be provided');
        }

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->status($transactionReference),
        );
    }
    /**
     * @param string $referenceType referenceType have only two types which is 'payment' or 'transaction'
     */
    public function statusByReference(string $reference, string $referenceType = 'transaction'): array
    {
        if ($referenceType !== 'transaction' && $referenceType !== 'payment') {
            throw new InvalidArgumentException('Either transaction or payment must be provided as referenceType');
        }

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->statusByReference($reference, $referenceType),
        );
    }
}
