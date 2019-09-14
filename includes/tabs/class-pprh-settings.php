<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PPRH_Settings {

	public function __construct() {
		$this->display_settings();
	}

	public function display_settings() {
		?>
			<div id="pprh-settings">
				<form method="post">
					<?php
						$this->save_user_options();
						$this->settings_html();
					?>
				</form>
			</div>
		<?php
	}

	public function save_user_options() {

		if ( isset( $_POST['pprh_preconnects_set'] ) ) {
			update_option( 'pprh_preconnects_set', 'false' );
		}

		if ( isset( $_POST['pprh_save_user_options'] ) && check_admin_referer( 'pprh_save_admin_options', 'pprh_admin_options_nonce' ) ) {
			update_option( 'pprh_autoload_preconnects', wp_unslash( $_POST['pprh_preconnect_status'] ) );
			update_option( 'pprh_disable_wp_hints', wp_unslash( $_POST['pprh_disable_wp_hints_option'] ) );
			update_option( 'pprh_allow_unauth', wp_unslash( $_POST['pprh_pprh_allow_unauth_option'] ) );
		}
	}

	public function preconnects_html() {

		// wp_nonce_field( 'pprh_save_admin_options', 'pprh_admin_options_nonce' );
		?>
		<h2><?php esc_html_e( 'Auto Preconnect Options', 'pprh' ); ?></h2>

		<table class="pprh-settings-table">
			<tbody>

				<?php


				?>
			</tbody>
		</table>

		<?php
	}



	public function settings_html() {

		wp_nonce_field( 'pprh_save_admin_options', 'pprh_admin_options_nonce' );
		?>
		<h2 style="margin-top: 30px;"><?php esc_html_e( 'Settings', 'pprh' ); ?></h2>

		<table class="pprh-settings-table">
			<tbody>

			<?php
			$this->auto_set_hints();
			$this->disable_auto_wp_hints();
			$this->allow_unauth();
			$this->reset_preconnects();
			?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="3">
						<input type="submit" name="pprh_save_user_options" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'pprh' ); ?>" />
					</td>
				</tr>

			</tfoot>

		</table>

		<?php
	}

	public function auto_set_hints() {
		?>
		<tr>
			<th>
				<?php esc_html_e( 'Automatically Set Preconnect Hints?', 'pprh' ); ?>
			</th>

			<td>
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'JavaScript, CSS, and images loaded from external domains will preconnect automatically.', 'pprh' ); ?></span>
				</span>
			</td>

			<td>
				<select name="pprh_preconnect_status">
					<option value="true" <?php $this->get_option_status( 'pprh_autoload_preconnects', 'true' ); ?>>
						<?php esc_html_e( 'Yes', 'pprh' ); ?>
					</option>
					<option value="false" <?php $this->get_option_status( 'pprh_autoload_preconnects', 'false' ); ?>>
						<?php esc_html_e( 'No', 'pprh' ); ?>
					</option>
				</select>
			</td>
		</tr>

		<?php
	}

	public function disable_auto_wp_hints() {
		?>
		<tr>
			<th>
				<?php esc_html_e( 'Disable Automatically Generated WordPress Resource Hints?', 'pprh' ); ?>
			</th>

			<td>
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'This option will remove three resource hints automatically generated by WordPress, as of 4.8.2.', 'pprh' ); ?></span>
				</span>
			</td>

			<td>
				<select name="pprh_disable_wp_hints_option">
					<option value="true" <?php $this->get_option_status( 'pprh_disable_wp_hints', 'true' ); ?>>
						<?php esc_html_e( 'Yes', 'pprh' ); ?>
					</option>
					<option value="false" <?php $this->get_option_status( 'pprh_disable_wp_hints', 'false' ); ?>>
						<?php esc_html_e( 'No', 'pprh' ); ?>
					</option>
				</select>
			</td>
		</tr>

		<?php  
	}

	public function allow_unauth() {
		?>
		<tr>
			<th>
				<?php esc_html_e( 'Allow unauthenticated users to auto-set post/page preconnect hints?', 'pprh' ); ?>
			</th>

			<td>
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'Automatically set preconnect hints used on posts/pages are initially set once by the first user to access that page.', 'pprh' ); ?></span>
				</span>
			</td>

			<td>
				<select name="pprh_pprh_allow_unauth_option">
					<option value="true" <?php $this->get_option_status( 'pprh_allow_unauth', 'true' ); ?>>
						<?php esc_html_e( 'Yes', 'pprh' ); ?>
					</option>
					<option value="false" <?php $this->get_option_status( 'pprh_allow_unauth', 'false' ); ?>>
						<?php esc_html_e( 'No', 'pprh' ); ?>
					</option>
				</select>
			</td>
		</tr>
		<?php
	}

	public function reset_preconnects() {
		?>
		<tr>
			<th>
				<?php esc_html_e( 'Reset automatically created preconnect Links?', 'pprh' ); ?>
			</th>

			<td>
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'This will reset automatically created preconnect hints.', 'pprh' ); ?></span>
				</span>
			</td>

			<td>
				<input type="submit" name="pprh_preconnects_set" id="pprhPreconnectReset" class="button-secondary" value="Reset"/>
			</td>
		</tr>

		<?php
	}

	public function get_option_status( $option_name, $val ) {
		echo esc_html( ( get_option( $option_name ) === $val ? 'selected=selected' : '' ) );
	}

}

if ( is_admin() ) {
	new PPRH_Settings();
}
