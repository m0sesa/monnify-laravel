<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use Monnify\MonnifyLaravel\Support\BuildsCoreClient;
use Monnify\MonnifyLaravel\Support\LaravelHttpClient;
use Monnify\MonnifyLaravel\Support\MapsSdkResponses;
use Monnify\MonnifyLaravel\Validators\BillsPaymentValidator;
use Monnify\Services\BillsPaymentService as CoreBillsPaymentService;

class BillsPaymentService extends BaseService
{
    use BuildsCoreClient;
    use MapsSdkResponses;

    private BillsPaymentValidator $validator;
    private LaravelHttpClient $laravelHttpClient;
    private CoreBillsPaymentService $coreService;

    public function __construct(
        Client $client,
        ?BillsPaymentValidator $validator = null,
        ?CoreBillsPaymentService $coreService = null,
        ?LaravelHttpClient $laravelHttpClient = null,
    ) {
        parent::__construct($client);
        $this->validator = $validator ?? new BillsPaymentValidator();
        $this->laravelHttpClient = $laravelHttpClient ?? new LaravelHttpClient($client);
        $this->coreService = $coreService ?? new CoreBillsPaymentService($this->buildCoreClient($client, $this->laravelHttpClient));
    }

    /**
     * Get all biller categories.
     */
    public function categories(int $pageSize = 10, int $pageNumber = 0): array
    {
        $parameters = [
            'size' => $pageSize,
            'page' => $pageNumber,
        ];

        $this->validator->validatePagination($parameters);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->categories($pageSize, $pageNumber),
        );
    }

    /**
     * List billers, optionally filtered by category code.
     */
    public function billers(string $categoryCode = '', int $pageSize = 10, int $pageNumber = 0): array
    {
        $parameters = [
            'size' => $pageSize,
            'page' => $pageNumber,
        ];

        if (! empty($categoryCode)) {
            $parameters['category_code'] = $categoryCode;
        }

        $this->validator->validateBillers($parameters);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->billers($categoryCode, $pageSize, $pageNumber),
        );
    }

    /**
     * Get products for a specific biller.
     */
    public function products(string $billerCode, int $pageSize = 10, int $pageNumber = 0): array
    {
        $parameters = [
            'biller_code' => $billerCode,
            'size' => $pageSize,
            'page' => $pageNumber,
        ];

        $this->validator->validateProducts($parameters);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->products($billerCode, $pageSize, $pageNumber),
        );
    }

    public function validateCustomer(array $data): array
    {
        $this->validator->validateCustomer($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->validateCustomer($data),
        );
    }

    /**
     * Process (vend) a bill payment.
     */
    public function vend(array $data): array
    {
        $this->validator->validateVend($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->vend($data),
        );
    }

    /**
     * Check the status of a bill payment transaction.
     */
    public function requery(string $vendReference): array
    {
        $this->validator->validateRequery(['vendReference' => $vendReference]);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->requery($vendReference),
        );
    }
}
