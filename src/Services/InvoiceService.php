<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Support\BuildsCoreClient;
use Monnify\MonnifyLaravel\Support\LaravelHttpClient;
use Monnify\MonnifyLaravel\Support\MapsSdkResponses;
use Monnify\MonnifyLaravel\Validators\InvoiceValidator;
use Monnify\Services\InvoiceService as CoreInvoiceService;

class InvoiceService extends BaseService
{
    use BuildsCoreClient;
    use MapsSdkResponses;

    private InvoiceValidator $validator;
    private LaravelHttpClient $laravelHttpClient;
    private CoreInvoiceService $coreService;

    public function __construct(
        Client $client,
        ?InvoiceValidator $validator = null,
        ?CoreInvoiceService $coreService = null,
        ?LaravelHttpClient $laravelHttpClient = null,
    ) {
        parent::__construct($client);
        $this->validator = $validator ?? new InvoiceValidator();
        $this->laravelHttpClient = $laravelHttpClient ?? new LaravelHttpClient($client);
        $this->coreService = $coreService ?? new CoreInvoiceService($this->buildCoreClient($client, $this->laravelHttpClient));
    }

    public function create(array $data): array
    {
        $this->validator->validateAccount($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->create($data),
        );
    }

    public function get(string $invoiceReference): array
    {
        if (empty($invoiceReference)) {
            throw new InvalidArgumentException('Invoice Reference must be provided.');
        }

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->get($invoiceReference),
        );
    }

    public function all(): array
    {
        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->all(),
        );
    }

    public function cancel(string $invoiceReference): array
    {
        if (empty($invoiceReference)) {
            throw new InvalidArgumentException('Invoice Reference must be provided.');
        }

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->cancel($invoiceReference),
        );
    }

    public function attachReservedAccount(array $data): array
    {
        $this->validator->validateAccount($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->attachReservedAccount($data),
        );
    }
}
