<?php

namespace WLMStripe\Service;

class BalanceService extends \WLMStripe\Service\AbstractService {

	/**
	 * Retrieves the current account balance, based on the authentication that was used
	 * to make the request.  For a sample request, see <a
	 * href="/docs/connect/account-balances#accounting-for-negative-balances">Accounting
	 * for negative balances</a>.
	 *
	 * @param null|array $params
	 * @param null|array|\WLMStripe\Util\RequestOptions $opts
	 *
	 * @throws \WLMStripe\Exception\ApiErrorException if the request fails
	 *
	 * @return \WLMStripe\Balance
	 */
	public function retrieve( $params = null, $opts = null) {
		return $this->request('get', '/v1/balance', $params, $opts);
	}
}
