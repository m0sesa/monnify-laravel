<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Support\BuildsCoreClient;
use Monnify\MonnifyLaravel\Support\LaravelHttpClient;
use Monnify\MonnifyLaravel\Support\MapsSdkResponses;
use Monnify\MonnifyLaravel\Validators\PayCodeValidator;
use Monnify\Services\PayCodeService as CorePayCodeService;

class PayCodeService extends BaseService
{
    use BuildsCoreClient;
    use MapsSdkResponses;

    private PayCodeValidator $validator;
    private LaravelHttpClient $laravelHttpClient;
    private CorePayCodeService $coreService;

    public function __construct(
        Client $client,
        ?PayCodeValidator $validator = null,
        ?CorePayCodeService $coreService = null,
        ?LaravelHttpClient $laravelHttpClient = null,
    ) {
        parent::__construct($client);
        $this->validator = $validator ?? new PayCodeValidator();
        $this->laravelHttpClient = $laravelHttpClient ?? new LaravelHttpClient($client);
        $this->coreService = $coreService ?? new CorePayCodeService($this->buildCoreClient($client, $this->laravelHttpClient));
    }

    public function create(array $data): array
    {
        $this->validator->validate($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->create($data),
        );
    }

    public function get(string $payCodeReference): array
    {
        if (empty($payCodeReference)) {
            throw new InvalidArgumentException('PayCode Reference must be provided.');
        }

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->get($payCodeReference),
        );
    }

    public function getUnMasked(string $payCodeReference): array
    {
        if (empty($payCodeReference)) {
            throw new InvalidArgumentException('PayCode Reference must be provided.');
        }

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->getUnMasked($payCodeReference),
        );
    }

    public function history(array $parameters): array
    {
        $this->validator->validateHistoryParameters($parameters);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->history($parameters),
        );
    }

    public function delete(string $payCodeReference): array
    {
        if (empty($payCodeReference)) {
            throw new InvalidArgumentException('PayCode Reference must be provided.');
        }

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->delete($payCodeReference),
        );
    }
}
