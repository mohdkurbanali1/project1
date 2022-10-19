<?php

namespace WLMStripe\Service\Radar;

class EarlyFraudWarningService extends \WLMStripe\Service\AbstractService {

	/**
	 * Returns a list of early fraud warnings.
	 *
	 * @param null|array $params
	 * @param null|array|\WLMStripe\Util\RequestOptions $opts
	 *
	 * @throws \WLMStripe\Exception\ApiErrorException if the request fails
	 *
	 * @return \WLMStripe\Collection
	 */
	public function all( $params = null, $opts = null) {
		return $this->requestCollection('get', '/v1/radar/early_fraud_warnings', $params, $opts);
	}

	/**
	 * Retrieves the details of an early fraud warning that has previously been
	 * created.
	 *
	 * Please refer to the <a href="#early_fraud_warning_object">early fraud
	 * warning</a> object reference for more details.
	 *
	 * @param string $id
	 * @param null|array $params
	 * @param null|array|\WLMStripe\Util\RequestOptions $opts
	 *
	 * @throws \WLMStripe\Exception\ApiErrorException if the request fails
	 *
	 * @return \WLMStripe\Radar\EarlyFraudWarning
	 */
	public function retrieve( $id, $params = null, $opts = null) {
		return $this->request('get', $this->buildPath('/v1/radar/early_fraud_warnings/%s', $id), $params, $opts);
	}
}
