<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Support\BuildsCoreClient;
use Monnify\MonnifyLaravel\Support\LaravelHttpClient;
use Monnify\MonnifyLaravel\Support\MapsSdkResponses;
use Monnify\MonnifyLaravel\Validators\VerificationValidator;
use Monnify\Services\VerificationService as CoreVerificationService;

class VerificationService
{
    use BuildsCoreClient;
    use MapsSdkResponses;

    private VerificationValidator $validator;
    private LaravelHttpClient $laravelHttpClient;
    private CoreVerificationService $coreService;

    public function __construct(
        Client $client,
        ?VerificationValidator $validator = null,
        ?CoreVerificationService $coreService = null,
        ?LaravelHttpClient $laravelHttpClient = null,
    ) {
        $this->validator = $validator ?? new VerificationValidator();
        $this->laravelHttpClient = $laravelHttpClient ?? new LaravelHttpClient($client);
        $this->coreService = $coreService ?? new CoreVerificationService($this->buildCoreClient($client, $this->laravelHttpClient));
    }

    public function bankAccount(string $accountNumber, string $bankCode): array
    {
        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->bankAccount($accountNumber, $bankCode),
        );
    }

    public function bvnInformation(array $data): array
    {
        $this->validator->validateBVNInformation($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->bvnInformation($data),
        );
    }

    public function matchBVNAndBankAccount(string $bvn, string $bankCode, string $accountNumber): array
    {
        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->matchBVNAndBankAccount($bvn, $bankCode, $accountNumber),
        );
    }

    public function nin(string $nin): array
    {
        if (empty($nin)) {
            throw new InvalidArgumentException('NIN must be provided.');
        }

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->nin($nin),
        );
    }
}
