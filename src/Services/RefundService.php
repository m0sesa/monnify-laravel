<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Support\BuildsCoreClient;
use Monnify\MonnifyLaravel\Support\LaravelHttpClient;
use Monnify\MonnifyLaravel\Support\MapsSdkResponses;
use Monnify\MonnifyLaravel\Validators\RefundValidator;
use Monnify\Services\RefundService as CoreRefundService;

class RefundService
{
    use BuildsCoreClient;
    use MapsSdkResponses;

    private RefundValidator $validator;
    private LaravelHttpClient $laravelHttpClient;
    private CoreRefundService $coreService;

    public function __construct(
        Client $client,
        ?RefundValidator $validator = null,
        ?CoreRefundService $coreService = null,
        ?LaravelHttpClient $laravelHttpClient = null,
    ) {
        $this->validator = $validator ?? new RefundValidator();
        $this->laravelHttpClient = $laravelHttpClient ?? new LaravelHttpClient($client);
        $this->coreService = $coreService ?? new CoreRefundService($this->buildCoreClient($client, $this->laravelHttpClient));
    }

    public function initialise(array $data): array
    {
        $this->validator->validateRefund($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->initialise($data),
        );
    }

    public function all(int $pageSize = 10, int $pageNumber = 0): array
    {
        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->all($pageSize, $pageNumber),
        );
    }

    public function status(string $refundReference): array
    {
        if (empty($refundReference)) {
            throw new InvalidArgumentException('Refund Reference must be provided.');
        }

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->status($refundReference),
        );
    }
}
