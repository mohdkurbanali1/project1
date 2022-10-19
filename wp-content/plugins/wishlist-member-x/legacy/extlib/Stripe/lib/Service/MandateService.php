<?php

namespace WLMStripe\Service;

class MandateService extends \WLMStripe\Service\AbstractService {

	/**
	 * Retrieves a Mandate object.
	 *
	 * @param string $id
	 * @param null|array $params
	 * @param null|array|\WLMStripe\Util\RequestOptions $opts
	 *
	 * @throws \WLMStripe\Exception\ApiErrorException if the request fails
	 *
	 * @return \WLMStripe\Mandate
	 */
	public function retrieve( $id, $params = null, $opts = null) {
		return $this->request('get', $this->buildPath('/v1/mandates/%s', $id), $params, $opts);
	}
}
