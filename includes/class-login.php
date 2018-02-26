<?php
/**
 * Our one login authentication function.
 *
 * @package PwnedLoginCheck
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Start our engines.
 */
class PwnedLoginCheck_Login {

	/**
	 * Call our hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'wp_authenticate_user',         array( $this, 'check_pwned_at_login'    ),  20, 2   );
	}

	/**
	 * Run the pwned check when we're handling the authentication.
	 *
	 * @param  object $user      The WP_User() object of the user being edited,
	 *                           or a WP_Error() object if validation has already failed.
	 * @param  string $password  The password the user entered.
	 *
	 * @return object $user      The user. Again.
	 */
	public function check_pwned_at_login( $user, $password ) {

		// If we already failed, just return the user.
		if ( is_wp_error( $user ) ) {
			return $user;
		}

		// Run our password check.
		pwned_login_check()->check_pwned_api_status( $user->ID, $password );

		// Return the user.
		return $user;
	}


	// End our class.
}

// Call our class.
$PwnedLoginCheck_Login = new PwnedLoginCheck_Login();
$PwnedLoginCheck_Login->init();
