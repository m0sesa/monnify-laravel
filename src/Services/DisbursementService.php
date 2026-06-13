<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Support\BuildsCoreClient;
use Monnify\MonnifyLaravel\Support\LaravelHttpClient;
use Monnify\MonnifyLaravel\Support\MapsSdkResponses;
use Monnify\MonnifyLaravel\Validators\DisbursementValidator;
use Monnify\Services\DisbursementService as CoreDisbursementService;

class DisbursementService
{
    use BuildsCoreClient;
    use MapsSdkResponses;

    private DisbursementValidator $validator;
    private LaravelHttpClient $laravelHttpClient;
    private CoreDisbursementService $coreService;

    public function __construct(
        Client $client,
        ?DisbursementValidator $validator = null,
        ?CoreDisbursementService $coreService = null,
        ?LaravelHttpClient $laravelHttpClient = null,
    ) {
        $this->validator = $validator ?? new DisbursementValidator();
        $this->laravelHttpClient = $laravelHttpClient ?? new LaravelHttpClient($client);
        $this->coreService = $coreService ?? new CoreDisbursementService($this->buildCoreClient($client, $this->laravelHttpClient));
    }

    public function single(array $data, bool $asynchronous = false): array
    {
        $this->validator->validateSingleTransfer($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->single($data, $asynchronous),
        );
    }

    public function bulk(array $data): array
    {
        $this->validator->validateBulkTransfer($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->bulk($data),
        );
    }

    public function authoriseSingle(array $data): array
    {
        $this->validator->validateAuthorization($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->authoriseSingle($data),
        );
    }

    public function authoriseBulk(array $data): array
    {
        $this->validator->validateAuthorization($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->authoriseBulk($data),
        );
    }

    public function resendOTP(string $reference): array
    {
        if (empty($reference)) {
            throw new InvalidArgumentException("Reference must be provided");
        }
        
        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->resendOTP($reference),
        );
    }

    public function bulkResendOTP(string $reference): array
    {
        if (empty($reference)) {
            throw new InvalidArgumentException("Reference must be provided");
        }

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->bulkResendOTP($reference),
        );
    }

    public function bulkBatchSummary(string $batchReference): array
    {
        if (empty($batchReference)) {
            throw new InvalidArgumentException("Batch Reference must be provided");
        }

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->bulkBatchSummary($batchReference),
        );
    }

    public function singleStatus(string $reference): array
    {
        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->singleStatus($reference),
        );
    }

    public function bulkStatus(string $batchReference, int $pageSize = 10, int $pageNumber = 0): array
    {
        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->bulkStatus($batchReference, $pageSize, $pageNumber),
        );
    }
    /**
     * @param 'single'|'bulk' $type
     *
     * Note: bulk listing requires the disbursement feature to be enabled on your Monnify merchant account.
     * Calling with $type = 'bulk' on an account without this feature returns 404.
     */
    public function all(string $type = 'single', int $pageSize = 10, int $pageNumber = 0): array
    {
        if (!in_array($type, ['single', 'bulk'], true)) {
            throw new InvalidArgumentException("Type must be 'single' or 'bulk'.");
        }

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->all($type, $pageSize, $pageNumber),
        );
    }

    public function bulkTransaction(string $batchReference, int $pageSize = 10, int $pageNumber = 0): array
    {
        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->bulkTransaction($batchReference, $pageSize, $pageNumber),
        );
    }

    public function search(string $sourceAccountNumber,  int $pageSize = 10, int $pageNumber = 0): array
    {
        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->search($sourceAccountNumber, $pageSize, $pageNumber),
        );
    }

    public function walletBalance(string $accountNumber): array
    {
        if (empty($accountNumber)) {
            throw new InvalidArgumentException('Account Number must be provided.');
        }

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->walletBalance($accountNumber),
        );
    }
}
