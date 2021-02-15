<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GeneralSettings {

	public $disable_wp_hints = false;

	public function set_values() {
		$this->disable_wp_hints = \PPRH\Utils::is_option_checked( 'pprh_disable_wp_hints' );
	}

	public function save_options() {
		$options = array(
			'disable_wp_hints' => isset( $_POST['disable_wp_hints'] ) ? 'true' : 'false',
		);

		update_option('pprh_disable_wp_hints', $options['disable_wp_hints']);
		update_option('pprh_html_head', wp_unslash($_POST['html_head']));
	}

	public function markup() {
		$this->set_values();
		?>
		<div class="postbox" id="general">
			<div class="inside">
				<h3><?php esc_html_e( 'General Settings', 'pprh' ); ?></h3>

				<table class="form-table">
					<tbody>

					<tr>
						<th><?php esc_html_e( 'Disable automatically generated WordPress resource hints?', 'pprh' ); ?></th>

						<td>
							<input type="checkbox" name="disable_wp_hints" value="1" <?php echo $this->disable_wp_hints; ?>/>
							<p><?php esc_html_e( 'This option will remove three resource hints automatically generated by WordPress, as of 4.8.2.', 'pprh' ); ?></p>
						</td>
					</tr>

					<tr>
						<th><?php esc_html_e( 'Send resource hints in HTML head or HTTP header?', 'pprh' ); ?></th>

						<td>
							<select id="pprhHintLocation" name="html_head">
								<option value="true" <?php echo Utils::get_option_status( 'pprh_html_head', 'true' ); ?>><?php esc_html_e( 'HTML &lt;head&gt;', 'pprh' ); ?></option>
								<option value="false" <?php echo Utils::get_option_status( 'pprh_html_head', 'false' ); ?>><?php esc_html_e( 'HTTP Header', 'pprh' ); ?></option>
							</select>
							<p><?php esc_html_e( 'Send hints in the HTML &lt;head&gt; or the HTTP header.', 'pprh' ); ?></p>
						</td>
					</tr>

					<?php do_action( 'pprh_general_settings' ); ?>

					</tbody>
				</table>
			</div>
		</div>
		<?php
		apply_filters( 'pprh_pro_settings', 'general' );
	}

}

?>