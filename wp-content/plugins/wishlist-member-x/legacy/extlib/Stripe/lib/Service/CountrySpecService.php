<?php

namespace WLMStripe\Service;

class CountrySpecService extends \WLMStripe\Service\AbstractService {

	/**
	 * Lists all Country Spec objects available in the API.
	 *
	 * @param null|array $params
	 * @param null|array|\WLMStripe\Util\RequestOptions $opts
	 *
	 * @throws \WLMStripe\Exception\ApiErrorException if the request fails
	 *
	 * @return \WLMStripe\Collection
	 */
	public function all( $params = null, $opts = null) {
		return $this->requestCollection('get', '/v1/country_specs', $params, $opts);
	}

	/**
	 * Returns a Country Spec for a given Country code.
	 *
	 * @param string $id
	 * @param null|array $params
	 * @param null|array|\WLMStripe\Util\RequestOptions $opts
	 *
	 * @throws \WLMStripe\Exception\ApiErrorException if the request fails
	 *
	 * @return \WLMStripe\CountrySpec
	 */
	public function retrieve( $id, $params = null, $opts = null) {
		return $this->request('get', $this->buildPath('/v1/country_specs/%s', $id), $params, $opts);
	}
}
