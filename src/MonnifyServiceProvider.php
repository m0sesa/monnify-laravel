<?php

namespace Monnify\MonnifyLaravel;

use Error;
use GuzzleHttp\Client;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\ServiceProvider;
use Monnify\Auth\TokenCacheInterface;
use Monnify\Http\MonnifyApiClient;
use Monnify\MonnifyConfig;
use Monnify\MonnifyLaravel\Services\{
    BillsPaymentService,
    CustomerReservedAccountService,
    DirectDebitService,
    DisbursementService,
    InvoiceService,
    LimitProfileService,
    OtherService,
    PayCodeService,
    RecurringPaymentService,
    RefundService,
    SettlementService,
    SubAccountService,
    TransactionService,
    VerificationService,
    WalletService
};
use Monnify\MonnifyLaravel\Validators\{
    BillsPaymentValidator,
    CustomerReservedAccountValidator,
    DirectDebitValidator,
    DisbursementValidator,
    InvoiceValidator,
    LimitProfileValidator,
    PayCodeValidator,
    RecurringPaymentValidator,
    RefundValidator,
    SubAccountValidator,
    TransactionValidator,
    VerificationValidator,
    WalletValidator
};
use Monnify\MonnifyLaravel\Support\{
    LaravelHttpClient,
    LaravelTokenCache,
    MonnifyConfigFactory
};
use Monnify\Services\BillsPaymentService as CoreBillsPaymentService;
use Monnify\Services\CustomerReservedAccountService as CoreCustomerReservedAccountService;
use Monnify\Services\DirectDebitService as CoreDirectDebitService;
use Monnify\Services\DisbursementService as CoreDisbursementService;
use Monnify\Services\InvoiceService as CoreInvoiceService;
use Monnify\Services\LimitProfileService as CoreLimitProfileService;
use Monnify\Services\OtherService as CoreOtherService;
use Monnify\Services\PayCodeService as CorePayCodeService;
use Monnify\Services\RecurringPaymentService as CoreRecurringPaymentService;
use Monnify\Services\RefundService as CoreRefundService;
use Monnify\Services\SettlementService as CoreSettlementService;
use Monnify\Services\SubAccountService as CoreSubAccountService;
use Monnify\Services\TransactionService as CoreTransactionService;
use Monnify\Services\VerificationService as CoreVerificationService;
use Monnify\Services\WalletService as CoreWalletService;

/**
 * Class MonnifyServiceProvider
 */
class MonnifyServiceProvider extends ServiceProvider
{
    public const BINDING = 'monnify';
    public const HTTP_CLIENT_BINDING = 'monnify.http_client';
    private const SERVICE_CLASSES = [
        TransactionService::class,
        BillsPaymentService::class,
        CustomerReservedAccountService::class,
        InvoiceService::class,
        RecurringPaymentService::class,
        DirectDebitService::class,
        SubAccountService::class,
        DisbursementService::class,
        WalletService::class,
        LimitProfileService::class,
        RefundService::class,
        SettlementService::class,
        VerificationService::class,
        PayCodeService::class,
        OtherService::class,
    ];
    private const VALIDATOR_CLASSES = [
        TransactionValidator::class,
        BillsPaymentValidator::class,
        CustomerReservedAccountValidator::class,
        InvoiceValidator::class,
        RecurringPaymentValidator::class,
        DirectDebitValidator::class,
        SubAccountValidator::class,
        DisbursementValidator::class,
        WalletValidator::class,
        LimitProfileValidator::class,
        RefundValidator::class,
        VerificationValidator::class,
        PayCodeValidator::class,
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/Config/monnify.php',
            'monnify'
        );

        $this->app->singleton(self::HTTP_CLIENT_BINDING, function () {
            return new Client([
                'base_uri' => $this->resolveBaseUri(),
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);
        });

        $this->registerCoreBindings();
        $this->registerValidatorSingletons();
        $this->registerServiceContextualBindings();
        $this->registerServiceSingletons();

        $this->app->singleton(self::BINDING, function ($app) {
            return $app->make(Monnify::class);
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/Config/monnify.php' => config_path('monnify.php'),
        ], 'config');
    }

    protected function registerServiceContextualBindings(): void
    {
        $this->app->when(self::SERVICE_CLASSES)
            ->needs(Client::class)
            ->give(fn ($app) => $app->make(self::HTTP_CLIENT_BINDING));
    }

    protected function registerCoreBindings(): void
    {
        $this->app->singleton(MonnifyConfig::class, fn () => MonnifyConfigFactory::make());

        $this->app->singleton(LaravelHttpClient::class, function ($app) {
            return new LaravelHttpClient($app->make(self::HTTP_CLIENT_BINDING));
        });

        $this->app->singleton(TokenCacheInterface::class, function ($app) {
            return new LaravelTokenCache($app->make(Repository::class));
        });

        $this->app->singleton(MonnifyApiClient::class, function ($app) {
            return new MonnifyApiClient(
                $app->make(MonnifyConfig::class),
                $app->make(LaravelHttpClient::class),
                $app->make(TokenCacheInterface::class),
            );
        });

        $this->app->singleton(CoreTransactionService::class, function ($app) {
            return new CoreTransactionService($app->make(MonnifyApiClient::class));
        });

        $this->app->singleton(CoreBillsPaymentService::class, function ($app) {
            return new CoreBillsPaymentService($app->make(MonnifyApiClient::class));
        });

        $this->app->singleton(CoreOtherService::class, function ($app) {
            return new CoreOtherService($app->make(MonnifyApiClient::class));
        });

        $this->app->singleton(CoreCustomerReservedAccountService::class, function ($app) {
            return new CoreCustomerReservedAccountService($app->make(MonnifyApiClient::class));
        });

        $this->app->singleton(CoreDisbursementService::class, function ($app) {
            return new CoreDisbursementService($app->make(MonnifyApiClient::class));
        });

        $this->app->singleton(CoreDirectDebitService::class, function ($app) {
            return new CoreDirectDebitService($app->make(MonnifyApiClient::class));
        });

        $this->app->singleton(CoreInvoiceService::class, function ($app) {
            return new CoreInvoiceService($app->make(MonnifyApiClient::class));
        });

        $this->app->singleton(CoreLimitProfileService::class, function ($app) {
            return new CoreLimitProfileService($app->make(MonnifyApiClient::class));
        });

        $this->app->singleton(CorePayCodeService::class, function ($app) {
            return new CorePayCodeService($app->make(MonnifyApiClient::class));
        });

        $this->app->singleton(CoreRecurringPaymentService::class, function ($app) {
            return new CoreRecurringPaymentService($app->make(MonnifyApiClient::class));
        });

        $this->app->singleton(CoreRefundService::class, function ($app) {
            return new CoreRefundService($app->make(MonnifyApiClient::class));
        });

        $this->app->singleton(CoreSettlementService::class, function ($app) {
            return new CoreSettlementService($app->make(MonnifyApiClient::class));
        });

        $this->app->singleton(CoreSubAccountService::class, function ($app) {
            return new CoreSubAccountService($app->make(MonnifyApiClient::class));
        });

        $this->app->singleton(CoreVerificationService::class, function ($app) {
            return new CoreVerificationService($app->make(MonnifyApiClient::class));
        });

        $this->app->singleton(CoreWalletService::class, function ($app) {
            return new CoreWalletService($app->make(MonnifyApiClient::class));
        });
    }

    protected function registerServiceSingletons(): void
    {
        foreach (self::SERVICE_CLASSES as $serviceClass) {
            $this->app->singleton($serviceClass);
        }
    }

    protected function registerValidatorSingletons(): void
    {
        foreach (self::VALIDATOR_CLASSES as $validatorClass) {
            $this->app->singleton($validatorClass);
        }
    }

    protected function resolveBaseUri(): string
    {
        $environment = config('monnify.environment');

        return match ($environment) {
            'SANDBOX' => config('monnify.sandbox_url'),
            'LIVE' => config('monnify.live_url'),
            default => throw new Error(
                "Unknown environment passed: $environment, Please specify between SANDBOX or LIVE"
            ),
        };
    }
}
