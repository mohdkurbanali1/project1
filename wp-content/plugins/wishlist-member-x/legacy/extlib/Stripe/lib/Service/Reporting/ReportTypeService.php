<?php

namespace WLMStripe\Service\Reporting;

class ReportTypeService extends \WLMStripe\Service\AbstractService {

	/**
	 * Returns a full list of Report Types. (Requires a <a
	 * href="https://stripe.com/docs/keys#test-live-modes">live-mode API key</a>.).
	 *
	 * @param null|array $params
	 * @param null|array|\WLMStripe\Util\RequestOptions $opts
	 *
	 * @throws \WLMStripe\Exception\ApiErrorException if the request fails
	 *
	 * @return \WLMStripe\Collection
	 */
	public function all( $params = null, $opts = null) {
		return $this->requestCollection('get', '/v1/reporting/report_types', $params, $opts);
	}

	/**
	 * Retrieves the details of a Report Type. (Requires a <a
	 * href="https://stripe.com/docs/keys#test-live-modes">live-mode API key</a>.).
	 *
	 * @param string $id
	 * @param null|array $params
	 * @param null|array|\WLMStripe\Util\RequestOptions $opts
	 *
	 * @throws \WLMStripe\Exception\ApiErrorException if the request fails
	 *
	 * @return \WLMStripe\Reporting\ReportType
	 */
	public function retrieve( $id, $params = null, $opts = null) {
		return $this->request('get', $this->buildPath('/v1/reporting/report_types/%s', $id), $params, $opts);
	}
}
