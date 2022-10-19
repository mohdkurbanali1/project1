<?php

namespace WLMStripe\Service\BillingPortal;

class SessionService extends \WLMStripe\Service\AbstractService {

	/**
	 * Creates a session of the customer portal.
	 *
	 * @param null|array $params
	 * @param null|array|\WLMStripe\Util\RequestOptions $opts
	 *
	 * @throws \WLMStripe\Exception\ApiErrorException if the request fails
	 *
	 * @return \WLMStripe\BillingPortal\Session
	 */
	public function create( $params = null, $opts = null) {
		return $this->request('post', '/v1/billing_portal/sessions', $params, $opts);
	}
}
