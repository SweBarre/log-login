<?php
/*
Plugin Name: Log Login
Plugin URI: https://github.com/SweBarre/log-login
Description: Plugin to log logon attempts to YOURLS. Refer to <a href="https://github.com/SweBarre/log-login">my page</a> for more details.
Version: 0.2
Author: Jonas Forsberg
Author URI: http://gargamel.nu/
*/

// No direct call
if( !defined( 'YOURLS_ABSPATH' ) ) die();


// Load the Log facility
require_once('Log.php');

// Register log-login admin page
yourls_add_action( 'plugins_loaded', 'barre_log_login_admipage_add_page' );

function barre_log_login_admipage_add_page() {
	yourls_register_plugin_page( 'barre_log_login_adminpage', 'log-login Settings', 'barre_log_login_adminpage_do_page' );
        // parameters: page slug, page title, and function that will display the page itself
}

// Display admin page
function barre_log_login_adminpage_do_page() {

        // Check if a form was submitted
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
        	//if( isset( $_POST['barre_log_login_log_success'] ) ) {
                // Check nonce
                yourls_verify_nonce( 'barre_log_login_adminpage' );
                // Process form
                barre_log_login_adminpage_update_option();
        }

        // Get value from database
        $barre_log_login_log_success = yourls_get_option( 'barre_log_login_log_success' );
        $barre_log_login_log_failure = yourls_get_option( 'barre_log_login_log_failure' );
        $barre_log_login_log_logoff = yourls_get_option( 'barre_log_login_log_logoff' );
        $barre_log_login_log_filename = yourls_get_option( 'barre_log_login_log_filename' );

        // Create nonce
        $nonce = yourls_create_nonce( 'barre_log_login_adminpage' );
        
        echo '<h2>';
	echo yourls_e( 'log-login Plugin Administration Page' ); 
	echo '</h2>';
        echo '<p>';
	echo yourls_e( 'This plugin lets you log login failures, login success and logoff to a file');
	echo '</p>';
        echo '<form method="post">';
        echo '<input type="hidden" name="nonce" value="' . $nonce . '" />';
        echo '<p><label for="barre_log_login_log_success">';
	echo yourls_e( 'Log successful logins' );
	echo '</label>';
        echo '<input type="checkbox" id="barre_log_login_log_success" name="barre_log_login_log_success" value="True"';
        if ( $barre_log_login_log_success == 'True') echo ' checked=checked';
        echo ''.'" /></p>';

        echo '<p><label for="barre_log_login_log_failure">';
	echo  yourls_e( 'Log failed logins' );
	echo '</label>';
        echo '<input type="checkbox" id="barre_log_login_log_failure" name="barre_log_login_log_failure" value="True"';
        if ( $barre_log_login_log_failure == 'True') echo ' checked=checked';
        echo '" /></p>';

        echo '<p><label for="barre_log_login_log_logoff">'; 
	echo yourls_e( 'Log logoffs' ); 
	echo '</label>';
        echo '<input type="checkbox" id="barre_log_login_log_logoff" name="barre_log_login_log_logoff" value="True"';
        if ( $barre_log_login_log_logoff == 'True') echo ' checked=checked';
        echo '" /></p>';
        
	echo '<p><label for="barre_log_login_log_filename">';
	echo yourls_e( 'Enter an integer2' );
	echo '</label> <input type="text" id="barre_log_login_log_filename" name="barre_log_login_log_filename" value="';
	echo $barre_log_login_log_filename;
	echo '" /></p>'; 

        echo '<p><input type="submit" value="';
	echo yourls_e( 'Update value' );
	echo '" /></p>';
        echo '</form>';
}

// Update option in database
function barre_log_login_adminpage_update_option() {
        
	isset( $_POST['barre_log_login_log_success'] ) ? $barre_log_login_log_success = "True" : $barre_log_login_log_success = "False";
        yourls_update_option( 'barre_log_login_log_success', $barre_log_login_log_success );
        
	isset( $_POST['barre_log_login_log_failure'] ) ? $barre_log_login_log_failure = "True" : $barre_log_login_log_failure = "False";
        yourls_update_option( 'barre_log_login_log_failure', $barre_log_login_log_failure );
	
	isset( $_POST['barre_log_login_log_logoff'] ) ? $barre_log_login_log_logoff = "True" : $barre_log_login_log_logoff = "False";
        yourls_update_option( 'barre_log_login_log_logoff', $barre_log_login_log_logoff );

	/*
	 TODO: 
		* Sanitize the path and filename more
		* Check if folder is writeble if log doesn't exist
	*/
        $barre_log_login_log_filename = $_POST['barre_log_login_log_filename'];
        if( !is_writable( $barre_log_login_log_filename) ) {
		$message = yourls_e( 'WARNING: The log file specified is not writeble');
		yourls_add_notice( $message );
	}
                
        yourls_update_option( 'barre_log_login_log_filename', $barre_log_login_log_filename );
}


/* Action login
 *
 * Write login success to the log
 * 
 */

if( yourls_get_option( 'barre_log_login_log_success' ) == "True" ) yourls_add_action( 'login', 'barre_log_login_success' );

/* Action login_fail
 *
 * Write login failure to the log
 * 
 */
if( yourls_get_option( 'barre_log_login_log_failure' ) == "True" ) yourls_add_action( 'login_failed', 'barre_log_login_failure' );

/* Action login_fail
 *
 * Write login failure to the log
 * 
 */
if( yourls_get_option( 'barre_log_login_log_logoff' ) == "True" ) yourls_add_action( 'logout', 'barre_log_login_logoff' );


// The actuall logging function
function barre_log_login_log2file( $barre_log_login_result ) {
        /* set the format of the log file
         * if you change this you probably have to change the
         * fail2ban rexexp in the yourls filter
         */
        $barre_login_log_conf = array( 
                                        'lineFormat' => "%{timestamp}\t - %{message}",
                                        'timeFormat' => "%b %d %H:%M:%S"
                                        );
        //check to see if the logfile is writeable, if not log error
	$barre_log_login_log_filename = yourls_get_option( 'barre_log_login_log_filename' );
	if( is_writable($barre_log_login_log_filename )) {
		// Create a singleton log class
		// TODO: Make the code Strict for the Log class !?!
		$barre_login_log_file = Log::singleton('file', yourls_get_option( 'barre_log_login_log_filename' ), 'BARRE_LOG_LOGIN_LOG', $barre_login_log_conf);
	        //log to the file
		$barre_login_log_file->log( $_SERVER['REMOTE_ADDR'] . " -\t" . $barre_log_login_result );
	} else {
		$message = yourls_e('The logfile is not writable: ') . $barre_log_login_log_filename;
		error_log( $message );
	}
	
}

//Log the successful logins
function barre_log_login_success() {
	barre_log_login_log2file( 'LOGIN SUCCESS ' . YOURLS_USER );
	
}

//log the failed logins
function barre_log_login_failure() {
	barre_log_login_log2file( 'LOGIN FAILURE' );
}

//log the logoffs
function barre_log_login_logoff() {
        barre_log_login_log2file( 'LOGOFF' );
}
