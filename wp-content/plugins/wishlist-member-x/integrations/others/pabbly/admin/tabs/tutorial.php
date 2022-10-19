<?php
/**
 * Pabbly tutorial
 *
 * @package WishListMember\Integrations\Others\Pabbly
 */

?>
<?php
if ( empty( $config['video_id'] ) ) {
	return;}
?>
	<div class="integration-video"><iframe src="https://player.vimeo.com/video/<?php echo esc_attr( $config['video_id'] ); ?>" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div>
