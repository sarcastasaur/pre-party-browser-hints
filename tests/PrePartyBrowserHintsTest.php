<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class PrePartyBrowserHintsTest extends TestCase {

	public function test_Create_constants():void {
		global $wpdb;
		$table = $wpdb->prefix . 'pprh_table';
		$abs_dir = WP_PLUGIN_DIR . '/pre-party-browser-hints/';
		$rel_dir = plugins_url() . '/pre-party-browser-hints/';
		$home_url = admin_url() . 'admin.php?page=pprh-plugin-setttings';
		$version = get_option( 'pprh_version' );

		$this->assertEquals( PPRH_VERSION, $version );
		$this->assertEquals( PPRH_DB_TABLE, $table );
		$this->assertEquals( PPRH_ABS_DIR, $abs_dir );
		$this->assertEquals( PPRH_REL_DIR, $rel_dir );
		$this->assertEquals( PPRH_HOME_URL, $home_url );
	}

	public function test_Load_common_files():void {
		$utils = class_exists(\PPRH\Utils::class);
		$dao = class_exists( \PPRH\DAO::class );
		$create_hints = class_exists( \PPRH\CreateHints::class );
		$new_hint = class_exists( \PPRH\NewHint::class );

		$this->assertEquals( true, $utils );
		$this->assertEquals( true, $dao );
		$this->assertEquals( true, $create_hints );
		$this->assertEquals( true, $new_hint );
	}



	public function test_Load_dashboard():void {
		$load_admin = class_exists( \PPRH\LoadAdmin::class );
		$bool = current_user_can( 'manage_options' );
		$this->assertEquals( $load_admin, $bool );
	}

	public function test_check_to_upgrade():void {
		$desired_version = '1.8.0';
		$option_name = 'pprh_version';
		$current_version = get_option( $option_name );
		update_option( $option_name, $desired_version );


		$activate_plugin = class_exists(\PPRH\ActivatePlugin::class );

		if ($activate_plugin) {
			$activate_plugin = new \PPRH\ActivatePlugin();
			$activate_plugin->plugin_activated;
			$activate_plugin = ( $activate_plugin->plugin_activated );
		}


		$expected_true = version_compare( $current_version, $desired_version ) < 0;
		$expected_false = version_compare( $current_version, $desired_version ) === 0;

		$this->assertEquals( $expected_true, $activate_plugin);
		$this->assertEquals( $expected_false, !$activate_plugin);
		update_option( $option_name, $desired_version );
	}

	public function test_upgrade_notice():void {

	}

	public function test_register_admin_files():void {
		if ( ! PPRH_IS_ADMIN ) return;

		global $wp_scripts;
		$pprh = new \PPRH\Pre_Party_Browser_Hints();
		$pprh->register_admin_files( 'toplevel_page_pprh-plugin-settings' );
		$actual_scripts = array();

		foreach( $wp_scripts->queue as $script ) {
			$actual_scripts[] =  $wp_scripts->registered[$script]->handle;
		}

		$expected_scripts = array( 'thickbox', 'pprh_admin_js' );
		$this->assertEquals( $expected_scripts, $actual_scripts);
	}

	public function test_():void {

	}
}