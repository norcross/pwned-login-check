<?php

/**
 * Delete various options when uninstalling the plugin.
 *
 * @return void
 */
function pwned_login_check_uninstall() {

	// This will be the function to delete any individual user meta.
	pwned_login_check()->delete_user_meta();

	// Include our action so that we may add to this later.
	do_action( 'pwned_login_check_uninstall_process' );
}
register_uninstall_hook( PWNED_LOGIN_FILE, 'pwned_login_check_uninstall' );

