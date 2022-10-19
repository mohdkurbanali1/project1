<?php

if ( ! class_exists( \MailPoet\API\API::class ) ) {
	return;
}

/* migrate old mailpoet settings to new */
add_action(
	'wishlistmember_version_changed',
	function( $old, $new ) {
		$ar = wishlistmember_instance()->get_option( 'Autoresponders' );
		if ( empty( $ar['mailpoet'] ) || empty( $ar['mailpoet']['lists'] ) ) {
			return;
		}
		$mp = $ar['mailpoet'];
		if ( is_array( $mp['lists'] ) ) {
			foreach ( $mp['lists'] as $level => $data ) {
				if ( $data && ! is_array( $data ) ) {
					$listid                         = $data;
					$mp['lists'][ $level ]          = array();
					$mp['lists'][ $level ]['added'] = array( 'add' => array( $listid ) );
					if ( is_array( $mp['unsubscribe'] ) && ! empty( $mp['unsubscribe'][ $level ] ) ) {
						$mp['lists'][ $level ]['removed'] = array( 'remove' => array( $listid ) );
					}
				}
			}

			$mp['lists'] = array_diff( $mp['lists'], array( '', false, null, 0 ) );
			unset( $mp['unsubscribe'] );
		} else {
			$mp['lists'] = array();
		}
		$ar['mailpoet'] = $mp;
		wishlistmember_instance()->save_option( 'Autoresponders', $ar );
	},
	10,
	2
);

$class_name = '\WishListMember\Autoresponders\MailPoet';

add_action( 'wishlistmember_user_registered', array( $class_name, 'user_registered' ), 99, 2 );
add_action( 'wishlistmember_add_user_levels_shutdown', array( $class_name, 'added_to_level' ), 99, 2 );
add_action( 'wishlistmember_confirm_user_levels', array( $class_name, 'added_to_level' ), 99, 2 );
add_action( 'wishlistmember_approve_user_levels', array( $class_name, 'added_to_level' ), 99, 2 );

add_action( 'wishlistmember_remove_user_levels', array( $class_name, 'removed_from_level' ), 99, 2 );
add_action( 'wishlistmember_cancel_user_levels', array( $class_name, 'cancelled_from_level' ), 99, 2 );
add_action( 'wishlistmember_uncancel_user_levels', array( $class_name, 'uncancelled_from_level' ), 99, 2 );
