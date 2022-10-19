<?php

namespace WLMStripe;

/**
 * Client used to send requests to Stripe's API.
 *
 * @property \WLMStripe\Service\AccountLinkService $accountLinks
 * @property \WLMStripe\Service\AccountService $accounts
 * @property \WLMStripe\Service\ApplePayDomainService $applePayDomains
 * @property \WLMStripe\Service\ApplicationFeeService $applicationFees
 * @property \WLMStripe\Service\BalanceService $balance
 * @property \WLMStripe\Service\BalanceTransactionService $balanceTransactions
 * @property \WLMStripe\Service\BillingPortal\BillingPortalServiceFactory $billingPortal
 * @property \WLMStripe\Service\ChargeService $charges
 * @property \WLMStripe\Service\Checkout\CheckoutServiceFactory $checkout
 * @property \WLMStripe\Service\CountrySpecService $countrySpecs
 * @property \WLMStripe\Service\CouponService $coupons
 * @property \WLMStripe\Service\CreditNoteService $creditNotes
 * @property \WLMStripe\Service\CustomerService $customers
 * @property \WLMStripe\Service\DisputeService $disputes
 * @property \WLMStripe\Service\EphemeralKeyService $ephemeralKeys
 * @property \WLMStripe\Service\EventService $events
 * @property \WLMStripe\Service\ExchangeRateService $exchangeRates
 * @property \WLMStripe\Service\FileLinkService $fileLinks
 * @property \WLMStripe\Service\FileService $files
 * @property \WLMStripe\Service\InvoiceItemService $invoiceItems
 * @property \WLMStripe\Service\InvoiceService $invoices
 * @property \WLMStripe\Service\Issuing\IssuingServiceFactory $issuing
 * @property \WLMStripe\Service\MandateService $mandates
 * @property \WLMStripe\Service\OAuthService $oauth
 * @property \WLMStripe\Service\OrderReturnService $orderReturns
 * @property \WLMStripe\Service\OrderService $orders
 * @property \WLMStripe\Service\PaymentIntentService $paymentIntents
 * @property \WLMStripe\Service\PaymentMethodService $paymentMethods
 * @property \WLMStripe\Service\PayoutService $payouts
 * @property \WLMStripe\Service\PlanService $plans
 * @property \WLMStripe\Service\PriceService $prices
 * @property \WLMStripe\Service\ProductService $products
 * @property \WLMStripe\Service\Radar\RadarServiceFactory $radar
 * @property \WLMStripe\Service\RefundService $refunds
 * @property \WLMStripe\Service\Reporting\ReportingServiceFactory $reporting
 * @property \WLMStripe\Service\ReviewService $reviews
 * @property \WLMStripe\Service\SetupIntentService $setupIntents
 * @property \WLMStripe\Service\Sigma\SigmaServiceFactory $sigma
 * @property \WLMStripe\Service\SkuService $skus
 * @property \WLMStripe\Service\SourceService $sources
 * @property \WLMStripe\Service\SubscriptionItemService $subscriptionItems
 * @property \WLMStripe\Service\SubscriptionScheduleService $subscriptionSchedules
 * @property \WLMStripe\Service\SubscriptionService $subscriptions
 * @property \WLMStripe\Service\TaxRateService $taxRates
 * @property \WLMStripe\Service\Terminal\TerminalServiceFactory $terminal
 * @property \WLMStripe\Service\TokenService $tokens
 * @property \WLMStripe\Service\TopupService $topups
 * @property \WLMStripe\Service\TransferService $transfers
 * @property \WLMStripe\Service\WebhookEndpointService $webhookEndpoints
 */
class StripeClient extends BaseStripeClient {

	/**
	 * @var \WLMStripe\Service\CoreServiceFactory
	 */
	private $coreServiceFactory;

	public function __get( $name) {
		if (null === $this->coreServiceFactory) {
			$this->coreServiceFactory = new \WLMStripe\Service\CoreServiceFactory($this);
		}

		return $this->coreServiceFactory->__get($name);
	}
}
