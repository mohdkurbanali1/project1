<?php

/**
 * Content Control Class for WishList Member
 *
 * @package wishlistmember
 */

if (!defined('ABSPATH')) {
die();
}

require_once dirname(__FILE__) . '/content-control/scheduler.php';
require_once dirname(__FILE__) . '/content-control/archiver.php';
require_once dirname(__FILE__) . '/content-control/manager.php';

if (!class_exists('WLM3_ContentControl')) {
	/**
	 * WishList Member Level Class
	 *
	 * @package wishlistmember
	 * @subpackage classes
	 */
	class WLM3_ContentControl {

		public $scheduler                 = null;
		public $archiver                  = null;
		public $manager                   = null;
		public $old_contentcontrol_active = false;

		public function __construct( $that) {
			if ( is_plugin_active( 'wishlist-content-control/wishlist-content-control.php' )  || isset($WishListContentControl) ) {
				$this->old_contentcontrol_active = true;
				return;
			}

			if ( $that->get_option('enable_content_scheduler') ) {
$this->scheduler = new WLM3_ContentScheduler();
			}
			if ( $that->get_option('enable_content_archiver') ) {
$this->archiver = new WLM3_ContentArchiver();
			}
			if ( $that->get_option('enable_content_manager') ) {
$this->manager = new WLM3_ContentManager();
			}
		}

		public function activate() {
			if ( $this->scheduler ) {
$this->scheduler->Activate();
			}
		}

		public function load_hooks() {
			if ( $this->scheduler ) {
$this->scheduler->load_hooks();
			}
			if ( $this->archiver ) {
$this->archiver->load_hooks();
			}
			if ( $this->manager ) {
$this->manager->load_hooks();
			}
		}
	}
}

