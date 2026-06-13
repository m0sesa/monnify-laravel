<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Support\BuildsCoreClient;
use Monnify\MonnifyLaravel\Support\LaravelHttpClient;
use Monnify\MonnifyLaravel\Support\MapsSdkResponses;
use Monnify\MonnifyLaravel\Validators\DirectDebitValidator;
use Monnify\Services\DirectDebitService as CoreDirectDebitService;

class DirectDebitService
{
    use BuildsCoreClient;
    use MapsSdkResponses;

    private DirectDebitValidator $validator;
    private LaravelHttpClient $laravelHttpClient;
    private CoreDirectDebitService $coreService;

    public function __construct(
        Client $client,
        ?DirectDebitValidator $validator = null,
        ?CoreDirectDebitService $coreService = null,
        ?LaravelHttpClient $laravelHttpClient = null,
    ) {
        $this->validator = $validator ?? new DirectDebitValidator();
        $this->laravelHttpClient = $laravelHttpClient ?? new LaravelHttpClient($client);
        $this->coreService = $coreService ?? new CoreDirectDebitService($this->buildCoreClient($client, $this->laravelHttpClient));
    }

    public function create(array $data): array
    {
        $this->validator->validateMandate($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->create($data),
        );
    }

    public function get(string $mandateReference): array
    {
        if (empty($mandateReference)) {
            throw new InvalidArgumentException('Mandate Reference must be provided.');
        }

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->get($mandateReference),
        );
    }

    public function debit(array $data): array
    {
        $this->validator->validateDebit($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->debit($data),
        );
    }

    public function status(string $paymentReference): array
    {
        if (empty($paymentReference)) {
            throw new InvalidArgumentException('Payment Reference must be provided.');
        }

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->status($paymentReference),
        );
    }

    public function cancel(string $mandateCode): array
    {
        if (empty($mandateCode)) {
            throw new InvalidArgumentException('Mandate Code must be provided.');
        }

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->cancel($mandateCode),
        );
    }
}
