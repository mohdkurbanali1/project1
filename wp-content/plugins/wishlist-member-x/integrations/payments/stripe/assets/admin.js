WLM3ThirdPartyIntegration.stripe.fxn = {
	test_keys : function(x) {
		var c = $( '#thirdparty-provider-container-stripe' );
		c.find( '.api-status' ).html( '<div class="text-warning"><p><em>Checking...</em></p></div>' );
		var b = c.find( '.save-keys' ).first();
		if (x.save) {
			b.text( b.data( 'saving' ) );
		}
		b.addClass( 'disabled' );
		$.post(
			WLM3VARS.ajaxurl,
			{
				action: 'wlm3_stripe_test_keys',
				data: x
			},
			function(result) {
				if (result.status) {
					c.removeClass( 'api-fail' );
					c.find( '.api-status' ).html( '<div class="text-success"><p>' + get_integration_api_message( 1, 'Stripe' ) + '</p></div>' );
					WLM3ThirdPartyIntegration.stripe = $.extend( {}, WLM3ThirdPartyIntegration.stripe, result.data );
					var plans                        = result.data.plan_options;
					$( 'select.stripe-products' ).html(''); // clear the select dropdown so that empty optgroup are not created by select2
					$( 'select.stripe-products' ).select2( {data : plans}, true );
					$( '#thirdparty-provider-container-stripe' ).set_form_data( WLM3ThirdPartyIntegration.stripe );
					$( 'select.stripe-products' ).trigger( 'change' );
					wlm3_stripe_load_table();
				} else {
					c.addClass( 'api-fail' );
					var msg = (x.stripeapikey.trim() && x.stripepublishablekey.trim()) ? get_integration_api_message( 2, result.message ) : get_integration_api_message( 3 );
					c.find( '.api-status' ).html( '<div class="text-danger"><p>' + msg + '</p></div>' );
				}
				if (x.save) {
					b.text( b.data( 'saved' ) );
				}
				b.removeClass( 'disabled' );
			},
			'json'
		);
	},
	get_keys : function(obj) {
		var $me = $( '#thirdparty-provider-container-stripe' );
		// if(!$me.hasClass('api-fail')) {
		// obj.find('.-integration-keys :input').val('');
		// }
		var x = {};
		obj.find( '.-integration-keys :input' ).each(
			function(i,v) {
				x[v.name] = v.value;
			}
		);
		return x;
	}
}
integration_before_open['stripe']    = function(obj) {
	var fxn = this;
	obj     = $( obj );
	var $me = $( '#thirdparty-provider-container-stripe' );

	fxn.save_keys = function(){
		var x = $.extend( {save : true},WLM3ThirdPartyIntegration.stripe.fxn.get_keys( obj ) );
		WLM3ThirdPartyIntegration.stripe.fxn.test_keys( x );
	};

	$me.on( 'click', '.save-keys', fxn.save_keys );

	$( 'body' ).on(
		'change',
		'.stripe-plan-toggle',
		function() {
			var target = $( $( this ).data( 'target' ) );
			if ($( this ).is( ':checked' )) {
				target.show();
			} else {
				target.hide();
			}
		}
	);

	$( 'body' ).on(
		'change',
		'select.stripe-products',
		function() {
			var $modal = $( this ).closest( '.modal' );
			var plans  = $( this ).val();
			if (plans.length) {
				var $first_option = $( this ).find( 'option[value="' + plans[0] + '"]' ).first();
				var $optgroup     = $first_option.closest( 'optgroup' );
				$( this ).find( 'option:not(:selected)' ).prop( 'disabled', true );
				$optgroup.find( 'option' ).prop( 'disabled', false );
				$modal.find( ':input.stripe-plan' ).val( plans.shift() );
				$modal.find( ':input.stripe-plans' ).val( JSON.stringify( plans ) );
			} else {
				$( this ).find( 'option' ).prop( 'disabled', false );
				$modal.find( ':input.stripe-plan' ).val( '' );
				$modal.find( ':input.stripe-plans' ).val( '' );
			}
			$( this ).select2();
		}
	);
	
	$( 'body' ).on( 'click', '.stripe-price a', function( e ) {
		e.preventDefault();
		$(this).closest('.stripe-price').toggleClass( 'expanded' );
	} );

	$me.addClass( 'api-fail' );
}

integration_after_open['stripe']     = function(obj) {
	var fxn = this;
	obj     = $( obj );
	$( '#stripe-products-table .stripe-plan-toggle' ).trigger( 'change' );
	WLM3ThirdPartyIntegration.stripe.fxn.test_keys( WLM3ThirdPartyIntegration.stripe.fxn.get_keys( obj ) );
}

// @since 3.6 correct the value of the products dropdown after saving
integration_modal_save['stripe'] = function(me, settings_data, result, textStatus) {
	var plans = [];
	plans.push(me.find(':input.stripe-plan').val());
	plans = plans.concat(wlm.json_parse(me.find(':input.stripe-plans').val() || "[]"));
	me.find('select.stripe-products').val(plans).trigger('change');
	wlm3_stripe_load_table();
}

function wlm3_stripe_load_table() {
	$( '#stripe-products-table' ).empty();
	$.each(
		all_levels,
		function(k, v) {
			if ( ! Object.keys( v ).length) {
				return true;
			}
			var data = {
				type : k,
				label : post_types[k].labels.name,
				levels : v
			}
			var tmpl = _.template( $( 'script#stripe-products-template' ).html(), {variable: 'data'} );
			var html = tmpl( data );
			$( '#stripe-products-table' ).append( html );
		}
	);
}

function wlm3_stripe_display_plans( level_plans ) {
	if(typeof level_plans != 'object' || !('plan' in level_plans)) {
		return;
	}
	if( level_plans.subscription == '1') {
		if(!this.plans) {
			this.plans = {};
			WLM3ThirdPartyIntegration.stripe.plan_options.forEach( function( item ) {
				item.children.forEach( function( item ) {
					this.plans[item.id] = item;
				} );
			} );
		}
		
		try {
				
			if(typeof level_plans.plan == 'string') {
				level_plans.plan = [ level_plans.plan ].concat( wlm.json_parse( level_plans.plans.replace(/\\/g, '') ) );
			}

			var output_details = '';
			if( level_plans.plan.length == 1 ) {
				output_summary = this.plans[level_plans.plan].text;
			} else {
				var output_summary = [];
				level_plans.plan.forEach( function ( item ) {
					var text = plans[item].text;
					var price = text.match( /\((.+?)\)$/ )[1];
					output_summary.push( '<span>' + price + '</span>' );
					output_details += '<li>' + text + '</li>';
				} );
				output_summary = '<a href="#"><span class="wlm-icons -down">arrow_drop_down</span><span class="wlm-icons -right">arrow_right</span>' + output_summary.join( ',&nbsp; &nbsp;' ) + '</a>';
				output_details = '<ul>' + output_details + '</ul>';
			}
			return output_summary + output_details;		
		} catch(err) {
			return '';
		}
		

	} else {
		return isNaN(level_plans.amount) ? '' : '<span>' + Number(level_plans.amount).toFixed(2) + '</span>';
	}
}
