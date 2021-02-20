<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LoadClient {

//	public function __construct() {
//
//	}

	public function send_hints() {
		include_once PPRH_ABS_DIR . 'includes/SendHints.php';
		$send_hints = new SendHints();
		$send_hints->init();
	}


	public function verify_to_load_fp() {
		$load_flying_pages = ( 'true' !== get_option( 'pprh_prefetch_enabled' ) );
		$disable_for_logged_in_users = get_option( 'pprh_prefetch_disableForLoggedInUsers' );
		$disabled = ('true' === $disable_for_logged_in_users && is_user_logged_in() );

		if ( $load_flying_pages || $disabled ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'load_flying_pages' ) );
	}

	public function load_flying_pages() {
		$debug = ( defined( 'PPRH_DEBUG' ) && PPRH_DEBUG );
		$js_script_path = ($debug) ? 'js/flying-pages.js' : 'js/flying-pages.min.js';

		$fp_data = array(
			'debug'          => ( $debug ) ? 'true' : 'false',
			'delay'          => get_option( 'pprh_prefetch_delay', 0 ),
			'maxRPS'         => get_option( 'pprh_prefetch_maxRPS', 3 ),
			'hoverDelay'     => get_option( 'pprh_prefetch_hoverDelay', 50 ),
			'ignoreKeywords' => get_option( 'pprh_prefetch_ignoreKeywords', '' ),
		);

		wp_register_script( 'pprh_prefetch_flying_pages', PPRH_REL_DIR . $js_script_path, null, PPRH_VERSION, true );
		wp_localize_script( 'pprh_prefetch_flying_pages', 'pprh_fp_data', $fp_data );
		wp_enqueue_script( 'pprh_prefetch_flying_pages' );
	}

}
