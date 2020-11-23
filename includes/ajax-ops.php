<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_admin() ) {
	new Ajax_Ops();
}

class Ajax_Ops {

	public $results = array(
		'query'     => array(),
		'new_hints' => array(),
	);

	public function __construct() {
		add_action( 'wp_ajax_pprh_update_hints', array( $this, 'pprh_update_hints' ) );
    }

	public function pprh_update_hints() {
		if ( isset( $_POST['pprh_data'] ) && wp_doing_ajax() ) {

			check_ajax_referer( 'pprh_table_nonce', 'val' );
			$data = json_decode( wp_unslash( $_POST['pprh_data'] ), false );

			if ( is_object( $data ) ) {
				$action = $data->action;

				include_once PPRH_ABS_DIR . '/includes/utils.php';
				include_once PPRH_ABS_DIR . '/includes/create-hints.php';
				include_once PPRH_ABS_DIR . '/includes/display-hints.php';

				$this->results['query'] = $this->handle_action( $data, $action );
				$display_hints = new Display_Hints();
				$display_hints->ajax_response( $this->results );
			}

			wp_die();
		}
	}

	private function handle_action( $data, $action ) {
		$wp_db = null;
		$dao = new DAO();
		if ( preg_match( '/create|update|delete/', $action ) ) {
			$wp_db = $dao->{$action . '_hint'}( $data );
		} elseif ( preg_match( '/enable|disable/', $action ) ) {
			$wp_db = $dao->bulk_update( $data, $action );
		}
		return $wp_db;
	}

	private function create_hint( $data ) {
		define( 'CREATING_HINT', true );
		$create_hint = new Create_Hints();
		$new_hint = $create_hint->init( $data );
		$dao = new DAO();

		return $dao->insert_hint( $new_hint );
	}


}
