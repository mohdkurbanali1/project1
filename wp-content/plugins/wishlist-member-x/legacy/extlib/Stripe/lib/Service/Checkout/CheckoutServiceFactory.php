<?php

namespace WLMStripe\Service\Checkout;

/**
 * Service factory class for API resources in the Checkout namespace.
 *
 * @property SessionService $sessions
 */
class CheckoutServiceFactory extends \WLMStripe\Service\AbstractServiceFactory {

	/**
	 * @var array<string, string>
	 */
	private static $classMap = [
		'sessions' => SessionService::class,
	];

	protected function getServiceClass( $name) {
		return \array_key_exists($name, self::$classMap) ? self::$classMap[$name] : null;
	}
}
