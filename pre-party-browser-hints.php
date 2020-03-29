<?php
/**
 * Plugin Name:       Pre* Party Resource Hints
 * Plugin URI:        https://wordpress.org/plugins/pre-party-browser-hints/
 * Description:       Take advantage of the browser resource hints DNS-Prefetch, Prerender, Preconnect, Prefetch, and Preload to improve page load time.
 * Version:           1.7.0
 * Requires at least: 4.4
 * Requires PHP:      5.3
 * Author:            Sam Perrow
 * Author URI:        https://www.linkedin.com/in/sam-perrow
 * License:           GPL3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       pprh
 * Domain Path:       /languages
 *
 * Copyright 2016  Sam Perrow  (email : sam.perrow399@gmail.com)
 *
*/

namespace PPRH;

// prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

new Init();

final class Init {

	public function __construct() {
		add_action( 'init', array( $this, 'initialize' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_files' ) );
		add_filter( 'set-screen-option', array( $this, 'apply_wp_screen_options' ), 10, 3 );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'set_admin_links' ) );
        register_activation_hook( __FILE__, array( $this, 'activate_plugin' ) );
        add_action( 'wpmu_new_blog', array( $this, 'activate_plugin' ) );
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_upgrade' ) );
    }

    public function check_for_upgrade( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $plugin_slug = 'pre-party-browser-hints/admin.php';
        $upgrade_data = get_transient( 'pprh_upgrade' );
        $pprh_upgrade_transient = get_transient( 'pprh_upgrade_info' );

        if ( is_array( $upgrade_data ) ) {

            if ( ! $pprh_upgrade_transient ) {
                $resp = $this->call_api( $upgrade_data['api_endpoint'] );
                set_transient( 'pprh_upgrade_info', $resp, 86400 );
            } else {
                $resp = get_transient( 'pprh_upgrade_info' );
            }

            $transient->response[ $plugin_slug ] = (object) $resp;
            $new_version = $transient->response[ $plugin_slug ]->new_version;

            if ( version_compare( $new_version, PPRH_VERSION ) > 0 ) {
//                unset( $transient->no_update[ $plugin_slug ] );
                return $transient;
            }
        }
    }

    private function call_api( $api_enpoint ) {

        $response = wp_remote_get( $api_enpoint );
        if ( is_wp_error( $response ) ) {
            return false;
        }

        $response_body = wp_remote_retrieve_body( $response );
        $result = json_decode( $response_body, true );

        if ( null !== $result ) {
            return $result;
        }
    }

	public function initialize() {

		$this->create_constants();

		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'load_admin_page' ) );
		} else {
			$this->pprh_disable_wp_hints();
			include_once PPRH_PLUGIN_DIR . '/class-pprh-send-hints.php';
			// new PPRH_Send_Hints();
		}

		// this needs to be loaded front end and back end bc Ajax needs to be able to communicate between the two.
		if ( 'true' === get_option( 'pprh_autoload_preconnects' ) ) {
			include_once PPRH_PLUGIN_DIR . '/class-pprh-ajax.php';
			new Ajax();
		}
	}

	public function load_admin_page() {

		$settings_page = add_menu_page(
			'Pre* Party Settings',
			'Pre* Party',
			'manage_options',
			'pprh-plugin-settings',
			array( $this, 'show_tabs' ),
			plugins_url( PPRH_PLUGIN_FILENAME . '/images/lightning.png' )
		);

		add_action( "load-{$settings_page}", array( $this, 'screen_option' ) );
		add_action( "load-{$settings_page}", array( $this, 'load_admin_files' ) );
	}

	public function create_constants() {
	    global $wpdb;
	    $prefix = $wpdb->prefix . 'pprh_table';
		if ( ! defined( 'PPRH_VERSION' ) ) {
			define( 'PPRH_VERSION', '1.6.45' );
		}
		if ( ! defined( 'PPRH_PLUGIN_FILENAME' ) ) {
			define( 'PPRH_PLUGIN_FILENAME', '/pre-party-browser-hints' );
		}
		if ( ! defined( 'PPRH_PLUGIN_DIR' ) ) {
			define( 'PPRH_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) . '/includes' );
		}
		if ( ! defined( 'PPRH_CHECK_PAGE' ) ) {
			define( 'PPRH_CHECK_PAGE', $this->check_page() );
		}
        if ( ! defined( 'PPRH_DB_TABLE' ) ) {
            define( 'PPRH_DB_TABLE', $prefix );
        }
	}

	public function load_admin_files() {
		include_once PPRH_PLUGIN_DIR . '/class-pprh-utils.php';
		include_once PPRH_PLUGIN_DIR . '/class-pprh-create-hints.php';
		include_once PPRH_PLUGIN_DIR . '/class-pprh-display-hints.php';
	}

	public function show_tabs() {
		include_once PPRH_PLUGIN_DIR . '/class-pprh-admin-tabs.php';

		if ( 'pprhAdmin' === PPRH_CHECK_PAGE ) {
			include_once PPRH_PLUGIN_DIR . '/class-pprh-admin-tabs.php';
		}
	}

	public function apply_wp_screen_options( $status, $option, $value ) {
		return ( 'pprh_screen_options' === $option ) ? $value : $status;
	}

	public function screen_option() {
		$option = 'per_page';
		$args   = array(
			'label'   => 'URLs',
			'default' => 10,
			'option'  => 'pprh_screen_options',
		);

		add_screen_option( $option, $args );
	}

	// Register and call the CSS and JS we need only on the needed page.
	public function register_admin_files( $hook ) {

		wp_register_script( 'pprh_admin_js', plugin_dir_url( __FILE__ ) . 'js/admin.js', null, PPRH_VERSION, true );
		wp_register_style( 'pprh_styles_css', plugin_dir_url( __FILE__ ) . 'css/styles.css', null, PPRH_VERSION, 'all' );

		if ( 'toplevel_page_pprh-plugin-settings' === $hook ) {
			wp_enqueue_script( 'pprh_admin_js' );
			wp_enqueue_style( 'pprh_styles_css' );
		}
	}

	public function set_admin_links( $links ) {
		$pprh_links = array(
			'<a href="https://github.com/samperrow/pre-party-browser-hints">View on GitHub</a>'
		);
		return array_merge( $links, $pprh_links );
	}

	// Implement option to disable automatically generated resource hints.
	public function pprh_disable_wp_hints() {
		if ( 'true' === get_option( 'pprh_disable_wp_hints' ) ) {
			return remove_action( 'wp_head', 'wp_resource_hints', 2 );
		}
	}

	public function update_option( $old_option_name, $new_option_name, $prev_value ) {
		$new_value = ( $prev_value === get_option( $old_option_name ) ) ? 'true' : 'false';
		add_option( $new_option_name, $new_value, '', 'yes' );
		delete_option( $old_option_name );
	}

	// Upgrade db table from version 1.5.8.
	public function upgrade_db( $new_table, $old_table ) {
        global $wpdb;
        $wpdb->query("RENAME TABLE $old_table TO $new_table");
        $wpdb->query("ALTER TABLE $new_table ADD created_by varchar(55)");
        $wpdb->query("ALTER TABLE $new_table DROP COLUMN header_string");
        $wpdb->query("ALTER TABLE $new_table DROP COLUMN head_string");
    }

	public function activate_plugin() {
		$this->create_constants();
		$this->set_options();
		$this->setup_tables();
	}

	public function set_options() {
        add_option( 'pprh_autoload_preconnects', 'true', '', 'yes' );
		add_option( 'pprh_allow_unauth', 'true', '', 'yes' );
		add_option( 'pprh_disable_wp_hints', 'true', '', 'yes' );
		add_option( 'pprh_html_head', 'true', '', 'yes' );
	}

    // Multisite install/delete db table.
    public function setup_tables() {
        global $wpdb;

        if ( ! function_exists( 'dbDelta' ) ) {
            include_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        $pprh_tables = array( PPRH_DB_TABLE );

        if ( is_multisite() ) {
            $pprh_tables[] = $this->get_multisite_tables();
        }

        foreach ( $pprh_tables as $pprh_table ) {
            $this->table_sql( $pprh_table );
        }
    }


	private function table_sql( $table_name ) {
		global $wpdb;
		$charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id INT(9) NOT NULL AUTO_INCREMENT,
            url VARCHAR(255) DEFAULT '' NOT NULL,
            hint_type VARCHAR(55) DEFAULT '' NOT NULL,
            status VARCHAR(55) DEFAULT 'enabled' NOT NULL,
            as_attr VARCHAR(55) DEFAULT '',
            type_attr VARCHAR(55) DEFAULT '',
            crossorigin VARCHAR(55) DEFAULT '',
            created_by VARCHAR(55) DEFAULT '' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset;";

        dbDelta( $sql, true );
	}

    public function check_page() {
        global $pagenow;
        $page = '';

        if ( 'admin.php' === $pagenow && isset( $_GET['page'] ) ) {
            $page = sanitize_text_field( wp_unslash( $_GET['page'] ) );
            if ( 'pprh-plugin-settings' === $page ) {
                $page = 'pprhAdmin';
            }
        }
        return $page;
    }

}
