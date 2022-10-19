<?php

namespace WLMStripe\Exception\OAuth;

/**
 * Implements properties and methods common to all (non-SPL) Stripe OAuth
 * exceptions.
 */
abstract class OAuthErrorException extends \WLMStripe\Exception\ApiErrorException {

	protected function constructErrorObject() {
		if (null === $this->jsonBody) {
			return null;
		}

		return \WLMStripe\OAuthErrorObject::constructFrom($this->jsonBody);
	}
}
