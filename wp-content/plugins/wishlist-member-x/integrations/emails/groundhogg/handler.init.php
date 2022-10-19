<?php
/**
 * Groundhogg handler init
 *
 * @package WishListMember/Autoresponders
 */

require_once __DIR__ . '/handler.php';

$class_name = '\WishListMember\Autoresponders\Groundhogg';

add_action( 'wishlistmember_user_registered', array( $class_name, 'new_user_tags_hook' ), 99, 2 );
add_action( 'wishlistmember_add_user_levels', array( $class_name, 'add_user_tags_hook' ), 10, 3 );

add_action( 'wishlistmember_confirm_user_levels', array( $class_name, 'confirm_approve_levels_tags_hook' ), 99, 2 );
add_action( 'wishlistmember_approve_user_levels', array( $class_name, 'confirm_approve_levels_tags_hook' ), 99, 2 );

add_action( 'wishlistmember_pre_remove_user_levels', array( $class_name, 'remove_user_tags_hook' ), 99, 2 );
add_action( 'wishlistmember_cancel_user_levels', array( $class_name, 'cancel_user_tags_hook' ), 99, 2 );
add_action( 'wishlistmember_uncancel_user_levels', array( $class_name, 'rereg_user_tags_hook' ), 99, 2 );

add_action( 'groundhogg/contact/tag_applied', array( $class_name, 'tags_added_hook' ), 99, 2 );
add_action( 'groundhogg/contact/tag_removed', array( $class_name, 'tags_removed_hook' ), 99, 2 );

add_action( 'wp_ajax_wishlistmember_groundhogg_delete_tag_action', array( $class_name, 'delete_tag_action' ) );
