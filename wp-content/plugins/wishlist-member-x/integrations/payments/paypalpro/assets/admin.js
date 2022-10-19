$.getScript(WLM3VARS.pluginurl + '/integrations/payments/paypal/assets/common.js?build=8261', function() {
	paypal_common.products = $.extend({}, WLM3ThirdPartyIntegration['paypalpro']['paypalproproducts']),
	paypal_common.new_product = {
		amount: 10,

		recurring: 0,
		recur_amount: 10,
		recur_billing_frequency: 1,
		recur_billing_period: wlm.translate( 'Month' ),

		trial: 0,
		trial_amount: 10,
		trial_recur_billing_frequency: 7,
		trial_recur_billing_period: wlm.translate( 'Day' ),

		max_failed_payments: 3
	}
	integration_modal_save['paypalpro'] = paypal_common.fxn.after_modal_save;
	paypal_common.prefix = 'paypalpro';
	paypal_common.products_option = 'paypalproproducts';

});

integration_before_open['paypalpro'] = function(obj) {
	var interval_id = setInterval(function() {
		if(typeof paypal_common == 'object' && paypal_common.prefix == 'paypalpro') {
			clearInterval(interval_id);
			paypal_common.fxn.init(obj);
		}
	}, 100);
}
