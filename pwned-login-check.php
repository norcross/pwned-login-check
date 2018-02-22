<?php
/**
 * Plugin Name: Pwned Login Check
 * Plugin URI:  https://github.com/norcross/pwned-login-check
 * Description: Checks the user password against the pwnedpasswords.com API
 * Version:     0.0.1
 * Author:      Andrew Norcross
 * Author URI:  http://andrewnorcross.com
 * Text Domain: pwned-login-check
 * Domain Path: /languages
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 *
 * @package PwnedLoginCheck
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Call our class.
 */
final class PwnedLoginCheck_Core {

	/**
	 * PwnedLoginCheck_Core instance.
	 *
	 * @access private
	 * @since  1.0
	 * @var    PwnedLoginCheck_Core The one true PwnedLoginCheck_Core
	 */
	private static $instance;

	/**
	 * The version number of PwnedLoginCheck_Core.
	 *
	 * @access private
	 * @since  1.0
	 * @var    string
	 */
	private $version = '0.0.1';

	/**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * retuns it.
	 *
	 * @return $instance
	 */
	public static function instance() {

		// Run the check to see if we have the instance yet.
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof PwnedLoginCheck_Core ) ) {

			// Set our instance.
			self::$instance = new PwnedLoginCheck_Core;

			// Set my plugin constants.
			self::$instance->setup_constants();

			// Run our version compare.
			if ( version_compare( PHP_VERSION, '5.6', '<' ) ) {

				// Deactivate the plugin.
				deactivate_plugins( PWNED_LOGIN_BASE );

				// And display the notice.
				wp_die( sprintf( __( 'Your current version of PHP is below the minimum version required by the plugin. Please contact your host and request that your version be upgraded to 5.6 or later. <a href="%s">Click here</a> to return to the plugins page.', 'pwned-login-check' ), admin_url( '/plugins.php' ) ) );
			}

			// Set my file includes.
			self::$instance->includes();

			// Load our textdomain.
			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
		}

		// And return the instance.
		return self::$instance;
	}

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 1.0
	 * @access protected
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'pwned-login-check' ), '0.0.1' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @since 1.0
	 * @access protected
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'pwned-login-check' ), '0.0.1' );
	}

	/**
	 * Setup plugin constants
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function setup_constants() {

		// Define our file base.
		if ( ! defined( 'PWNED_LOGIN_BASE' ) ) {
			define( 'PWNED_LOGIN_BASE', plugin_basename( __FILE__ ) );
		}

		// Set our base directory constant.
		if ( ! defined( 'PWNED_LOGIN_DIR' ) ) {
			define( 'PWNED_LOGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL.
		if ( ! defined( 'PWNED_LOGIN_URL' ) ) {
			define( 'PWNED_LOGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin root file.
		if( ! defined( 'PWNED_LOGIN_FILE' ) ) {
			define( 'PWNED_LOGIN_FILE', __FILE__ );
		}

		// Set our includes directory constant.
		if ( ! defined( 'PWNED_LOGIN_INCLS' ) ) {
			define( 'PWNED_LOGIN_INCLS', __DIR__ . '/includes' );
		}

		// Set the API domain URL.
		if ( ! defined( 'PWNED_LOGIN_API_URL' ) ) {
			define( 'PWNED_LOGIN_API_URL', 'https://api.pwnedpasswords.com/pwnedpassword' );
		}

		// Set what our user meta meta key will be.
		if ( ! defined( 'PWNED_LOGIN_USERKEY' ) ) {
			define( 'PWNED_LOGIN_USERKEY', '_pwned_login_flag' );
		}

		// Set our version constant.
		if ( ! defined( 'PWNED_LOGIN_VERS' ) ) {
			define( 'PWNED_LOGIN_VERS', $this->version );
		}
	}

	/**
	 * Load our actual files in the places they belong.
	 *
	 * @return void
	 */
	public function includes() {

		// Include the admin functionality.
		if ( is_admin() ) {
			require_once PWNED_LOGIN_INCLS . '/class-notices.php';
		}

		// Include the login functionality.
		if ( is_admin() || $GLOBALS['pagenow'] == 'wp-login.php' ) {
			require_once PWNED_LOGIN_INCLS . '/class-login.php';
		}

		// And our uninstall script.
		require_once PWNED_LOGIN_INCLS . '/uninstall.php';
	}

	/**
	 * Loads the plugin language files
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function load_textdomain() {

		// Set filter for plugin's languages directory.
		$lang_dir = dirname( plugin_basename( PWNED_LOGIN_FILE ) ) . '/languages/';

		/**
		 * Filters the languages directory path to use for LiquidWebKB.
		 *
		 * @param string $lang_dir The languages directory path.
		 */
		$lang_dir = apply_filters( 'pwned_login_languages_dir', $lang_dir );

		// Traditional WordPress plugin locale filter.
		global $wp_version;

		$get_locale = $wp_version >= 4.7 ? get_user_locale() : get_locale();

		/**
		 * Defines the plugin language locale used in LiquidWebKB.
		 *
		 * @var $get_locale The locale to use. Uses get_user_locale()` in WordPress 4.7 or greater,
		 *                  otherwise uses `get_locale()`.
		 */
		$locale = apply_filters( 'plugin_locale', $get_locale, 'pwned-login-check' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'pwned-login-check', $locale );

		// Setup paths to current locale file.
		$mofile_local  = $lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/pwned-login-check/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/pwned-login-check/ folder
			load_textdomain( 'pwned-login-check', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/pwned-login-check/languages/ folder
			load_textdomain( 'pwned-login-check', $mofile_local );
		} else {
			// Load the default language files.
			load_plugin_textdomain( 'pwned-login-check', false, $lang_dir );
		}
	}

	/**
	 * Pass the password through to the API check.
	 *
	 * @param  integer $user_id   The user ID being checked.
	 * @param  string  $password  Our password.
	 *
	 * @return boolean
	 */
	public function check_pwned_api_status( $user_id = 0, $password = '' ) {

		// Bail without a password or a user ID.
		if ( empty( $user_id ) || empty( $password ) ) {
			return;
		}

		// Do our before check.
		do_action( 'pwned_login_check_before', $user_id );

		// Set the API url.
		$domain = PWNED_LOGIN_API_URL . '/' . sha1( $password );

		// Run the actual pwned check.
		$pwned  = wp_remote_get( $domain, array( 'sslverify' => false ) );

		// Bail if the API couldn't be reached.
		if ( empty( $pwned ) || is_wp_error( $pwned ) ) {

			// Delete the key.
			$this->delete_single_user_meta( $user_id );

			// And return.
			return;
		}

		// Get my response codes.
		$code   = wp_remote_retrieve_response_code( $call );

		// Bail if no code came back.
		if ( empty( absint( $code ) ) || ! in_array( absint( $code ), array( 200, 404 ) ) ) {

			// Delete the key.
			$this->delete_single_user_meta( $user_id );

			// Run the action for not getting a good code.
			do_action( 'pwned_login_check_after', 'unknown', $user_id, 0 );

			// And return.
			return;
		}

		// Check for the 200 code.
		if ( 200 === absint( $code ) ) {

			// Determine how many times it was used.
			$count  = wp_remote_retrieve_body( $pwned );

			// Update the meta.
			$this->update_single_user_meta( $user_id, $count );

			// Run the action for being on the list.
			do_action( 'pwned_login_check_after', 'listed', $user_id, $count );

			// And return.
			return;
		}

		// Check for the 404 code.
		if ( 404 === absint( $code ) ) {

			// Delete the key.
			$this->delete_single_user_meta( $user_id );

			// Run the action for not being on the list.
			do_action( 'pwned_login_check_after', 'absent', $user_id, 0 );

			// And return.
			return;
		}

		// @@todo something else afterwards?
	}

	/**
	 * Check the user meta related to the login check.
	 *
	 * @param integer $user_id  The user ID being checked.
	 *
	 * @return void
	 */
	public function maybe_user_pwned( $user_id = 0 ) {

		// Bail without a user ID.
		if ( empty( $user_id ) ) {
			return;
		}

		// Get the count. Could be null.
		$count  = get_user_meta( $user_id, PWNED_LOGIN_USERKEY, true );

		// Return the result.
		return ! empty( $count ) ? $count : false;
	}

	/**
	 * Add the user meta related to the login check.
	 *
	 * @param integer $user_id  The user ID being checked.
	 * @param integer $count    How many instances .
	 *
	 * @return void
	 */
	public function update_single_user_meta( $user_id = 0, $count = 1 ) {

		// Bail without a user ID.
		if ( empty( $user_id ) ) {
			return;
		}

		// Update the user meta.
		update_user_meta( $user_id, PWNED_LOGIN_USERKEY, $count );
	}

	/**
	 * Delete the user meta related to the login check.
	 *
	 * @param integer $user_id  The user ID being checked.
	 *
	 * @return void
	 */
	public function delete_single_user_meta( $user_id = 0 ) {

		// Bail without a user ID.
		if ( empty( $user_id ) ) {
			return;
		}

		// Delete the user meta.
		delete_user_meta( $user_id, PWNED_LOGIN_USERKEY );
	}

	/**
	 * Delete any existing user meta.
	 *
	 * @return void
	 */
	public function delete_user_meta() {

		// Call global DB class.
		global $wpdb;

		// Set our table.
		$table  = $wpdb->usermeta;

		// Confirm the table exists before running any updates.
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) !== $table ) {
			return false;
		}

		// Prepare my query.
		$setup  = $wpdb->prepare("
			DELETE FROM $table
			WHERE meta_key = %s",
			esc_sql( PWNED_LOGIN_USERKEY )
		);

		// Run SQL query.
		$query = $wpdb->query( $setup );

		// And be done.
		return;
	}

	// End our class.
}

/**
 * The main function responsible for returning the one true PwnedLoginCheck_Core
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $pwned_login_check = pwned_login_check(); ?>
 *
 * @since 1.0
 * @return PwnedLoginCheck_Core The one true PwnedLoginCheck_Core Instance
 */
function pwned_login_check() {
	return PwnedLoginCheck_Core::instance();
}
pwned_login_check();
