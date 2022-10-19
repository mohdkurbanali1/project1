<?php

namespace WLMStripe\Service;

class TaxRateService extends \WLMStripe\Service\AbstractService {

	/**
	 * Returns a list of your tax rates. Tax rates are returned sorted by creation
	 * date, with the most recently created tax rates appearing first.
	 *
	 * @param null|array $params
	 * @param null|array|\WLMStripe\Util\RequestOptions $opts
	 *
	 * @throws \WLMStripe\Exception\ApiErrorException if the request fails
	 *
	 * @return \WLMStripe\Collection
	 */
	public function all( $params = null, $opts = null) {
		return $this->requestCollection('get', '/v1/tax_rates', $params, $opts);
	}

	/**
	 * Creates a new tax rate.
	 *
	 * @param null|array $params
	 * @param null|array|\WLMStripe\Util\RequestOptions $opts
	 *
	 * @throws \WLMStripe\Exception\ApiErrorException if the request fails
	 *
	 * @return \WLMStripe\TaxRate
	 */
	public function create( $params = null, $opts = null) {
		return $this->request('post', '/v1/tax_rates', $params, $opts);
	}

	/**
	 * Retrieves a tax rate with the given ID.
	 *
	 * @param string $id
	 * @param null|array $params
	 * @param null|array|\WLMStripe\Util\RequestOptions $opts
	 *
	 * @throws \WLMStripe\Exception\ApiErrorException if the request fails
	 *
	 * @return \WLMStripe\TaxRate
	 */
	public function retrieve( $id, $params = null, $opts = null) {
		return $this->request('get', $this->buildPath('/v1/tax_rates/%s', $id), $params, $opts);
	}

	/**
	 * Updates an existing tax rate.
	 *
	 * @param string $id
	 * @param null|array $params
	 * @param null|array|\WLMStripe\Util\RequestOptions $opts
	 *
	 * @throws \WLMStripe\Exception\ApiErrorException if the request fails
	 *
	 * @return \WLMStripe\TaxRate
	 */
	public function update( $id, $params = null, $opts = null) {
		return $this->request('post', $this->buildPath('/v1/tax_rates/%s', $id), $params, $opts);
	}
}
