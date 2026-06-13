<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use Monnify\MonnifyLaravel\Support\BuildsCoreClient;
use Monnify\MonnifyLaravel\Support\LaravelHttpClient;
use Monnify\MonnifyLaravel\Support\MapsSdkResponses;
use Monnify\MonnifyLaravel\Validators\RecurringPaymentValidator;
use Monnify\Services\RecurringPaymentService as CoreRecurringPaymentService;

class RecurringPaymentService
{
    use BuildsCoreClient;
    use MapsSdkResponses;

    private RecurringPaymentValidator $validator;
    private LaravelHttpClient $laravelHttpClient;
    private CoreRecurringPaymentService $coreService;

    public function __construct(
        Client $client,
        ?RecurringPaymentValidator $validator = null,
        ?CoreRecurringPaymentService $coreService = null,
        ?LaravelHttpClient $laravelHttpClient = null,
    ) {
        $this->validator = $validator ?? new RecurringPaymentValidator();
        $this->laravelHttpClient = $laravelHttpClient ?? new LaravelHttpClient($client);
        $this->coreService = $coreService ?? new CoreRecurringPaymentService($this->buildCoreClient($client, $this->laravelHttpClient));
    }

    public function chargeCardToken(array $data): array
    {
        $this->validator->validateChargeCardToken($data);

        return $this->mapSdkResponse(
            $this->laravelHttpClient,
            fn () => $this->coreService->chargeCardToken($data),
        );
    }
}
