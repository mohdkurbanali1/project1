<?php

namespace WLMStripe\Service;

class EphemeralKeyService extends \WLMStripe\Service\AbstractService {

	/**
	 * Invalidates a short-lived API key for a given resource.
	 *
	 * @param string $id
	 * @param null|array $params
	 * @param null|array|\WLMStripe\Util\RequestOptions $opts
	 *
	 * @throws \WLMStripe\Exception\ApiErrorException if the request fails
	 *
	 * @return \WLMStripe\EphemeralKey
	 */
	public function delete( $id, $params = null, $opts = null) {
		return $this->request('delete', $this->buildPath('/v1/ephemeral_keys/%s', $id), $params, $opts);
	}

	/**
	 * Creates a short-lived API key for a given resource.
	 *
	 * @param null|array $params
	 * @param null|array|\WLMStripe\Util\RequestOptions $opts
	 *
	 * @throws \WLMStripe\Exception\ApiErrorException if the request fails
	 *
	 * @return \WLMStripe\EphemeralKey
	 */
	public function create( $params = null, $opts = null) {
		if (!$opts || !isset($opts['stripe_version'])) {
			throw new \WLMStripe\Exception\InvalidArgumentException('stripe_version must be specified to create an ephemeral key');
		}

		return $this->request('post', '/v1/ephemeral_keys', $params, $opts);
	}
}
