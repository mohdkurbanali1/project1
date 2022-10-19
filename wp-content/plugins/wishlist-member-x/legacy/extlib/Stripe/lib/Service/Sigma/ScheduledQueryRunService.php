<?php

namespace WLMStripe\Service\Sigma;

class ScheduledQueryRunService extends \WLMStripe\Service\AbstractService {

	/**
	 * Returns a list of scheduled query runs.
	 *
	 * @param null|array $params
	 * @param null|array|\WLMStripe\Util\RequestOptions $opts
	 *
	 * @throws \WLMStripe\Exception\ApiErrorException if the request fails
	 *
	 * @return \WLMStripe\Collection
	 */
	public function all( $params = null, $opts = null) {
		return $this->requestCollection('get', '/v1/sigma/scheduled_query_runs', $params, $opts);
	}

	/**
	 * Retrieves the details of an scheduled query run.
	 *
	 * @param string $id
	 * @param null|array $params
	 * @param null|array|\WLMStripe\Util\RequestOptions $opts
	 *
	 * @throws \WLMStripe\Exception\ApiErrorException if the request fails
	 *
	 * @return \WLMStripe\Sigma\ScheduledQueryRun
	 */
	public function retrieve( $id, $params = null, $opts = null) {
		return $this->request('get', $this->buildPath('/v1/sigma/scheduled_query_runs/%s', $id), $params, $opts);
	}
}
