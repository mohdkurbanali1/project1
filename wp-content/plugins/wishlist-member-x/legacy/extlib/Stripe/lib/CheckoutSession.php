<?php

namespace WLMStripe;

/**
 * Class CheckoutSession
 *
 * @property string $id
 * @property string $object
 * @property bool $livemode
 *
 * @package WLMStripe
 */
class CheckoutSession extends ApiResource {


	const OBJECT_NAME = 'checkout_session';

	use ApiOperations\Create;
}
