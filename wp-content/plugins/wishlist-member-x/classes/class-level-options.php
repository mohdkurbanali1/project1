<?php
/**
 * Level Option Class file
 *
 * @package wishlistmember
 */

namespace WishListMember;

defined( 'ABSPATH' ) || die();

/**
 * Level Options class
 */
class Level_Options {
	/**
	 * Table name
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * Constructor
	 *
	 * @param string $table_prefix Table prefix.
	 */
	public function __construct( $table_prefix ) {
		$this->table_name = $table_prefix . 'level_options';
	}

	/**
	 * Save level option
	 *
	 * @param  string $level_id Level ID.
	 * @param  string $name     Option name.
	 * @param  mixed  $data     Option data.
	 * @return int|false
	 */
	public function save_option( $level_id, $name, $data ) {
		global $wpdb;
		$data = array(
			'level_id'     => $level_id,
			'option_name'  => $name,
			'option_value' => wlm_maybe_serialize( $data ),
		);
		return $wpdb->insert( $this->table_name, $data );
	}

	/**
	 * Update level option by option ID
	 *
	 * @param  int   $id   Option ID.
	 * @param  mixed $data Option Data.
	 * @return int|false
	 */
	public function update_option( $id, $data ) {
		global $wpdb;
		$data = array(
			'option_value' => wlm_maybe_serialize( $data ),
		);
		return $wpdb->update( $this->table_name, $data, array( 'ID' => $id ) );
	}

	/**
	 * Delete level option by option ID
	 *
	 * @param  int $id Option ID.
	 * @return int|false
	 */
	public function delete_option( $id ) {
		global $wpdb;
		return $wpdb->delete( $this->table_name, array( 'ID' => $id ) );
	}

	/**
	 * Get multiple level options
	 *
	 * @param  string $level_id level ID.
	 * @param  string $name     Option name.
	 * @param  int    $limit    Number of rows to return.
	 * @return array
	 */
	public function get_options( $level_id = null, $name = null, $limit = null ) {
		global $wpdb;

		$limit = (int) $limit;
		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM `' . esc_sql( $this->table_name ) . '` WHERE `level_id` LIKE %s AND `option_name` LIKE %s ORDER BY ID ASC LIMIT 0,%d',
				$level_id ? $wpdb->esc_like( $level_id ) : '%',
				$name ? $wpdb->esc_like( $name ) : '%',
				$limit ? $limit : PHP_INT_MAX
			)
		);
	}

	/**
	 * Get single level option by option ID.
	 *
	 * @param  int $id Option ID.
	 * @return object
	 */
	public function get_option( $id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM %0s WHERE ID=%d', $this->table_name, $id ) );
	}
}
