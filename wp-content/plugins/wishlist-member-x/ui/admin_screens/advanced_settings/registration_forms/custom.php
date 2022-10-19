<style type="text/css">
	.regform-edit-accordion-placeholder {
		display: block;
		height: 60px;
		background: white;
		border: 1px dotted black;
		margin: 5px 0;
	}
	#regform-edit-accordion .regform-edit-accordion-placeholder:first-child {
		margin-top: 0;
	}
	.ui-draggable-dragging {
		width: 100%;
	}
	.ui-draggable-dragging i {
		display: none;
	}
	.chosen-fields .ui-draggable-dragging {
		width: calc(100% - 45px) !important;
		list-style: none;
		background: #DBE4EE;
		border: 1px dotted black;
		padding: 15px;
		border-radius: 3px;
	}
	.inputh {
		display: none;
	}
	.field_hidden .inputh {
		display: inline-block;
	}

	.reg-cookie-timeout .input-group {
		width: 150px;
	}

	#custom-registration-forms-list tbody:not(:empty) ~ tfoot {
		display: none;
	}
</style>
<?php
	$registration_forms = $this->get_custom_reg_forms();
	$wpm_levels         = $this->get_option( 'wpm_levels' );
	$used_forms         = array();
foreach ( $wpm_levels as $level ) {
	if ( ! empty( $level['custom_reg_form'] ) && ! empty( $level['enable_custom_reg_form'] ) ) {
		if ( empty( $used_forms[ $level['custom_reg_form'] ] ) ) {
			$used_forms[ $level['custom_reg_form'] ] = array();
		}
		$used_forms[ $level['custom_reg_form'] ][] = $level['name'];
	}
}
$countries = include wishlistmember_instance()->plugindir3 . '/helpers/countries.php';
foreach ( $registration_forms as &$rform ) {
	$rform->option_value['form'] = preg_replace( '/ style=".+?"/', '', $rform->option_value['form'] );
}
unset( $rform );
?>
<script type='text/javascript'>
	wpm_regforms = <?php echo wp_json_encode( $registration_forms ); ?>;
	wpm_regform_default = <?php echo wp_json_encode( $this->get_legacy_registration_form( $the_formid, '', true ) ); ?>;
	wpm_levels = <?php echo wp_json_encode( $wpm_levels ); ?>;
	wpm_used_forms = <?php echo wp_json_encode( $used_forms ); ?>;
	wpm_countries = <?php echo wp_json_encode( $countries ); ?>;
</script>
<?php
	require 'custom/list.php';
	require 'custom/edit.php';

