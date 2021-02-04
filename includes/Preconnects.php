<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Preconnects {

//	public $post_id = '';

	public function __construct() {
		add_action( 'wp_loaded', array( $this, 'initialize' ) );
	}

	public function initialize() {
		add_action( 'wp_ajax_pprh_post_domain_names', array( $this, 'pprh_post_domain_names' ) );				// for logged in users
		$do_reset = $this->do_reset();

		if ( $do_reset ) {

			if ( 'true' === get_option( 'pprh_preconnect_allow_unauth' ) ) {										// not logged in
				$this->load();
				add_action( 'wp_ajax_nopriv_pprh_post_domain_names', array( $this, 'pprh_post_domain_names' ) );
			} elseif ( is_user_logged_in() ) {
				$this->load();
			}
		}
	}

	protected function do_reset() {
		$do_reset = false;

		if ( defined( 'PPRH_PRO_ABS_DIR' ) ) {
			$reset_data = apply_filters( 'pprh_perform_reset', array() );
			if ( 'true' === $reset_data['reset'] ) {
				$do_reset = true;
			}
		} elseif ( 'true' === get_option( 'pprh_preconnect_autoload' ) && 'false' === get_option( 'pprh_preconnect_set' ) ) {
			$do_reset = true;
		}
		return $do_reset;
	}

	public function load() {
		if ( ! is_admin() ) {
			$js_object = $this->set_js_object();
			wp_register_script( 'pprh-find-domain-names', PPRH_REL_DIR . 'js/find-external-domains.js', null, PPRH_VERSION, true );
			wp_localize_script( 'pprh-find-domain-names', 'pprh_data', $js_object );
			wp_enqueue_script( 'pprh-find-domain-names' );
		}
	}

	public function set_js_object() {
		$arr = array(
			'hints'      => array(),
			'nonce'      => wp_create_nonce( 'pprh_ajax_nonce' ),
			'admin_url'  => admin_url() . 'admin-ajax.php',
			'start_time' => time()
		);
		return apply_filters( 'pprh_append_js_object', $arr );
	}

	public function pprh_post_domain_names() {
		if ( isset( $_POST['pprh_data'] ) && wp_doing_ajax() ) {
			check_ajax_referer( 'pprh_ajax_nonce', 'nonce' );

			$raw_hint_data = json_decode( wp_unslash( $_POST['pprh_data'] ), false );

			if ( count( $raw_hint_data->hints ) > 0 ) {
				$this->process_hints( $raw_hint_data );
			}

			if ( defined( 'PPRH_PRO_ABS_DIR' ) ) {
				apply_filters( 'pprh_update_options', $raw_hint_data );
			} else {
				$this->update_options();
			}


//			if ( defined( 'PPRH_TESTING' ) && PPRH_TESTING ) {
//				return $json;
//			} else {
//				wp_die();
//			}
		}
		wp_die();
	}

	public function process_hints( $hint_data ) {
		$dao = new DAO();
		$dao->remove_prev_auto_preconnects();
		$results = array();

		foreach ( $hint_data->hints as $url ) {
			$hint_arr = array(
				'url'          => $url,
				'hint_type'    => 'preconnect',
				'auto_created' => 1
			);

			$hint_arr['post_url'] = ( ! empty( $hint_data->post_url ) ? $hint_data->post_url : '' );
			$hint_arr['post_id'] = ( ! empty( $hint_data->post_id ) ? $hint_data->post_id : '' );

			$hint = CreateHints::create_pprh_hint( $hint_arr );

			if ( is_array( $hint ) ) {
				$res = $dao->create_hint( $hint );
				$results[] = $res;
			}
		}
		return $results;
	}

	private function update_options() {
		update_option( 'pprh_preconnect_set', 'true' );
	}
}