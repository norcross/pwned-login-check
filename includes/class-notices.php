<?php
/**
 * Admin setup.
 *
 * It is all our admin things.
 *
 * @package StorifyStoryImport
 */

/**
 * Start our engines.
 */
class PwnedLoginCheck_Notices {

	/**
	 * Call our hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_notices',                array( $this, 'display_pwned_notice'    )           );
	}

	/**
	 * Display the message based on our pwned result.
	 *
	 * @return void
	 */
	public function display_pwned_notice() {

		// Do our maybe check.
		if ( false !== $count = pwned_login_check()->maybe_user_pwned( get_current_user_id() ) ) {

			// And the actual message.
			echo '<div class="notice notice-warning is-dismissible">';
				echo '<p>' . esc_html__( 'Your password has been listed on a database of compromised data. You should change it.', 'pwned-login-check' ) . '</p>';
			echo '</div>';
		}

		// And bail.
		return;
	}

	// End our class.
}

// Call our class.
$PwnedLoginCheck_Notices = new PwnedLoginCheck_Notices();
$PwnedLoginCheck_Notices->init();
