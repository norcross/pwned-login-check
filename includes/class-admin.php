<?php
/**
 * Admin setup.
 *
 * It is all our admin things.
 *
 * @package PwnedLoginCheck
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Start our engines.
 */
class PwnedLoginCheck_Admin {

	/**
	 * Call our hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_notices',                array( $this, 'display_pwned_notice'    )           );
		add_action( 'wp_ajax_hide_pwned_warning',   array( $this, 'hide_pwned_warning'      )           );
		add_action( 'admin_footer',                 array( $this, 'add_js_for_ajax'         )           );
	}

	/**
	 * Display the message based on our pwned result.
	 *
	 * @return void
	 */
	public function display_pwned_notice() {

		// Do our maybe check.
		if ( false === $maybe = pwned_login_check()->maybe_user_pwned( get_current_user_id() ) ) {
			return;
		}

		// Set my message text.
		$msgtxt = apply_filters( 'pwned_login_warning_text', __( 'Your password has been listed on a database of compromised data. You should change it.', 'pwned-login-check' ) );

		// Display the message regarding the pwned warning.
		echo '<div class="notice notice-warning is-dismissible pwned-login-warning">';
			echo '<p>' . wp_kses_post( $msgtxt ) . '</p>';
		echo '</div>';
	}

	/**
	 * Handle our small Ajax call for when the notice is dismissed.
	 *
	 * @return boolean
	 */
	public function hide_pwned_warning() {

		// Only run this on the admin side.
		if ( ! is_admin() ) {
			die();
		}

		// Bail if the nonce check fails.
		if ( false === $nonce = check_ajax_referer( 'pwned_nonce', 'pwned_nonce', false ) ) {
			return false;
		}

		// Check for the specific action.
		if ( empty( $_POST['action'] ) || 'hide_pwned_warning' !== sanitize_key( $_POST['action'] ) ) {
			return false;
		}

		// Check for the two pieces.
		if ( empty( $_POST['dismiss'] ) || empty( $_POST['user_id'] ) ) {
			return false;
		}

		// Update our user meta.
		pwned_login_check()->delete_single_user_meta( absint( $_POST['user_id'] ) );

		// And return true.
		return true;
	}

	/**
	 * Add the required JS for handling the ajax request.
	 *
	 * @return void
	 */
	public function add_js_for_ajax() {

		// Confirm we should load the JS.
		if ( false === $maybe = pwned_login_check()->maybe_user_pwned( get_current_user_id() ) ) {
			return;
		}
		?>
		<script>
		jQuery( '.pwned-login-warning' ).on( 'click', 'button.notice-dismiss', function (event) {

			var data = {
				action:  'hide_pwned_warning',
				user_id: '<?php echo get_current_user_id(); ?>',
				pwned_nonce: '<?php echo wp_create_nonce( 'pwned_nonce' ); ?>',
				dismiss: true,
			};

			jQuery.post( ajaxurl, data );
		});
		</script>
		<?php
	}

	// End our class.
}

// Call our class.
$PwnedLoginCheck_Admin = new PwnedLoginCheck_Admin();
$PwnedLoginCheck_Admin->init();
