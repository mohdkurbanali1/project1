<?php
if ( apply_filters( 'wishlistmember_disable_postpage_options', false, $post ) ) { 
	return;
}
$ptype        = $post->post_type;
$ptype_object = get_post_type_object( $ptype );

// Tooltips.
if ( function_exists( 'wp_add_inline_script' ) ) {
	wp_enqueue_style( 'wishlist-member-postpage-tooltip', plugin_dir_url( __FILE__ ) . 'tooltip.css', array(), WLM_PLUGIN_VERSION );
	wp_enqueue_script( 'jquery-ui-tooltip' );
	wp_add_inline_script( 'jquery-ui-tooltip', "jQuery( document ).tooltip( { items: 'a.wlm-help-icon[data-tooltip]', position: { my: 'left+10 center', at: 'right center', within: '#TB_ajaxContent' }, show: 250, hide: false, content: function(e) {return this.dataset.tooltip;}, classes: { 'ui-tooltip': 'wishlist-member-tooltip' } } );" );
}

if ( ! $ptype ) {
	$ptype = 'post';
}
$hide_options_style = '';
if ( ! $this->post_type_enabled( $ptype ) ) : ?>
<div style="padding: 12px" class="wlm-custom-post-type-disabled">
	<div class="wlm-sp-container clearfix">
		<div class="sp-container-left">
			<p><?php esc_html_e('Content Protection is disabled for this Post Type.', 'wishlist-member'); ?></p>
		</div>
		<div class="sp-container-right">
			<a name="<?php esc_html_e('Enable Content Protection', 'wishlist-member'); ?>" href="#" class="wlm-btn -with-icons" id="wlm3_enable_custom_post_type">
				<i class="wlm-icons"><img src="<?php echo esc_url( $this->pluginURL3 ); ?>/ui/images/outline-power_settings_new-24px.svg"></i>
				<span><?php esc_html_e('Enable Content Protection', 'wishlist-member'); ?></span>
			</a>
		</div>
	</div>
	<br clear="both">
</div>
<?php
$hide_options_style = 'display:none;';
endif;
require 'js.php';
?>

<div class="wlm-plugin-inside" style="<?php echo esc_attr( $hide_options_style ); ?>">
	<input type="hidden" name="wlm_old_post_parent" value="<?php echo esc_attr( $post->post_parent ? $post->post_parent : -1 ); ?>">
	<!-- Sidebar: Start -->
	<div class="wlm-plugin-sidebar">
		<li class="active"><a href="#" data-target=".wlm-inside01" class="wlm-inside-toggle"><?php esc_html_e('Protection and Access', 'wishlist-member'); ?></a></li>
		<?php if ( 'attachment' != $post->post_type ) : ?>
			<li><a href="#" data-target=".wlm-inside02" class="wlm-inside-toggle"><?php esc_html_e('Pay Per Post Access', 'wishlist-member'); ?></a></li>
			<li><a href="#" data-target=".wlm-inside03" class="wlm-inside-toggle"><?php esc_html_e('System Pages', 'wishlist-member'); ?></a></li>
		<?php endif; ?>
		
		<?php
			do_action_deprecated( 'wishlistmember3_post_page_options_menu', array(), '3.10', 'wishlistmember_post_page_options_menu' );
			do_action( 'wishlistmember_post_page_options_menu' );
		?>

	</div>
	<!-- Sidebar: End -->
	<div class="wlm-plugin-content">
		<?php
			require 'protection.php';
		if ( 'attachment' != $post->post_type ) {
			include 'payperpost.php';
			include 'systempages.php';
		}
			do_action_deprecated( 'wishlistmember3_post_page_options_content', array(), '3.10', 'wishlistmember_post_page_options_content' );
			do_action( 'wishlistmember_post_page_options_content' );
		?>
	</div>
</div>
