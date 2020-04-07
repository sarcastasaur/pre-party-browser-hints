<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin_Tabs {

	public $results;

	public function __construct() {
		$this->settings_page();
	}

	public function settings_page() {
		if ( ! is_admin() ) {
			exit;
		}
        $tab = $this->get_tab();
		
        echo '<div class="wrap pprh-wrap">';
		echo '<h2>Pre* Party Plugin Settings</h2>';
        $this->save_data($tab);

        $this->show_admin_tabs($tab);

        if ( ! empty( $this->results ) ) {
            Utils::pprh_show_update_result( $this->results );
        }

        include_once PPRH_PLUGIN_DIR . "/tabs/class-pprh-$tab.php";
        $this->show_footer();
		echo '</div>';
	}

	public function show_admin_tabs($current_tab) {

		$tabs = array(
			'insert-hints' => 'Insert Hints',
			'settings'     => 'Settings',
			'info'         => 'Resource Hint Information',
		);

		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $tabs as $tab => $name ) {
			$class = ( $current_tab === $tab ) ? 'nav-tab-active' : '';
			echo "<a class='nav-tab $class' href='?page=pprh-plugin-settings&tab=$tab'>" . esc_html( $name ) . '</a>';
		}
		echo '</h2>';
	}

	public function get_tab() {
		return ( isset( $_GET['tab'] ) ) ? $_GET['tab'] : 'insert-hints';
	}

	public function show_footer() {
		self::contact_author();
		echo '<br/>';
		echo sprintf( 'Tip: test your website on %sWebPageTest.org%s to know which resource hints and URLs to insert.', '<a href="https://www.webpagetest.org">', '</a>' );
	}

    public function save_data( $tab ) {
        if ( 'insert-hints' !== $tab || defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE) {
            return;
        }

        if ( isset( $_POST['pprh_data'] ) && check_admin_referer( 'pprh_nonce_action', 'pprh_nonce_val' ) ) {
            define( 'CREATING_HINT', true );
            $url_params    = new Create_Hints();
            $this->results = $url_params->results;
        }
    }

	public static function contact_author() {
		add_thickbox();
		?>

		<div id="pprhContactAuthor">
			<a style="margin: 20px 0;" href="#TB_inline?width=500&amp;height=300&amp;inlineId=pprhEmail" class="thickbox button button-primary">
				<span style="margin: 3px 5px 0 0;" class="dashicons dashicons-email"></span>
				Contact Support
			</a>

			<div style="display: none; text-align: center;" id="pprhEmail">
				<h2 style="font-size: 23px; text-align: center;"><?php esc_html_e( 'Request a New Feature or Report a Bug!' ); ?></h2>

				<form method="post" style="width: 350px; margin: 0 auto; text-align: center">
                    <label for="pprhEmailText"><?php wp_nonce_field( 'pprh_email_nonce_action', 'pprh_email_nonce_nonce' ); ?></label><textarea name="pprh_text" id="pprhEmailText" style="height: 100px;" class="widefat" placeholder="<?php esc_attr_e( 'Help make this plugin better!' ); ?>"></textarea>
                    <label for="pprhEmailAddress"></label><input name="pprh_email" id="pprhEmailAddress" style="margin: 10px 0;" class="input widefat" placeholder="<?php esc_attr_e( 'Email address:' ); ?>"/>
					<br/>
					<input name="pprh_send_email" id="pprhSubmit" type="submit" class="button button-primary" value="<?php esc_attr_e( 'Submit', 'pre-party-browser-hints' ); ?>" />
				</form>

			</div>
        </div>

        <?php

        if (isset( $_POST['pprh_send_email']) && check_admin_referer('pprh_email_nonce_action', 'pprh_email_nonce_nonce')) {
            $debug_info = "\nURL: " . home_url() . "\nPHP Version: " . PHP_VERSION . "\nWP Version: " . get_bloginfo( 'version' );
            wp_mail( 'sam.perrow399@gmail.com', 'Pre Party User Message', 'From: ' . sanitize_email( wp_unslash( $_POST['pprh_email'] ) ) . $debug_info . "\nMessage: " . sanitize_text_field( wp_unslash( $_POST['pprh_text'] ) ) );
        }
	}

}

if ( is_admin() ) {
	new Admin_Tabs();
}
