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
function barre_log_login_adminpage_add_page() {
yourls_register_plugin_page( 'barre_log_login_adminpage', 'log-login Admin Page', 'barre_log_login_adminpage_do_page' );
        // parameters: page slug, page title, and function that will display the page itself
}

// Display admin page
function barre_log_login_adminpage_do_page() {

        // Check if a form was submitted
        if( isset( $_POST['barre_log_login_log_success'] ) ) {
                // Check nonce
                yourls_verify_nonce( 'barre_log_login_adminpage' );

                // Process form
                barre_log_login_adminpage_update_option();
        }

        // Get value from database
        $barre_log_login_log_success = yourls_get_option( 'barre_log_login_log_success' );
        $barre_log_login_log_failure = yourls_get_option( 'barre_log_login_log_failure' );
        $barre_log_login_log_logoff = yourls_get_option( 'barre_log_login_log_logoff' );
        $barre_log_login_log_file = yourls_get_option( 'barre_log_login_log_file' );

        // Create nonce
        $nonce = yourls_create_nonce( 'barre_log_login_adminpage' );
        
        echo '<h2>' . yourls_e( 'log-login Plugin Administration Page' ) . '</h2>';
        echo '<p>' . yourls_e( 'This plugin lets you log login failures, login success and logoff to a file') . '</p>';
        echo '<form method="post">';
        echo '<input type="hidden" name="nonce" value="$nonce" />';
        
        echo '<p><label for="barre_log_login_log_success">' . yourls_e( 'Log successful logins' ) .'</label>';
        echo '<input type="checkbox" id="barre_log_login_log_success" name="barre_log_login_log_success" value="True"';
        if ( $barre_log_login_log_success == 'True') echo ' checked=checked';
        echo ''.'" /></p>';

        echo '<p><label for="barre_log_login_log_failure">' . yourls_e( 'Log failed logins' ) .'</label>';
        echo '<input type="checkbox" id="barre_log_login_log_failure" name="barre_log_login_log_failure" value="True"' . $barre_log_login_log_failure .'" /></p>';
        if ( $barre_log_login_log_failure == 'True') echo ' checked=checked';
        echo ''.'" /></p>';

        echo '<p><label for="barre_log_login_log_logoff">' . yourls_e( 'Log logoffs' ) .'</label>';
        echo '<input type="checkbox" id="barre_log_login_log_success" name="barre_log_login_log_logoff" value="True"' . $barre_log_login_log_logoff .'" /></p>';
        if ( $barre_log_login_log_failure == 'True') echo ' checked=checked';
        echo ''.'" /></p>';
        
        
        echo '<p><label for="barre_log_login_log_file>' . yourls_e( 'Enter path to log file' ) .'</label>';
        echo '<input type="text" id="barre_log_login_log_file" name="barre_log_login_log_file" value="' . $barre_log_login_log_file .'" /></p>';
        
        echo '<p><input type="submit" value="' .yourls_e( 'Update value' ) .'" /></p>';
        echo '</form>';
}

// Update option in database
function barre_log_login_adminpage_update_option() {
        $barre_log_login_log_success = $_POST['barre_log_login_log_success'];
        if( !$barre_log_login_log_success == 'True') $barre_log_login_log_success = 'False';
        yourls_update_option( 'barre_log_login_log_success', $barre_log_login_log_success );
        
        $barre_log_login_log_failure = $_POST['barre_log_login_log_failure'];
        if( !$barre_log_login_log_failure == 'True') $barre_log_login_log_failure = 'False';
        yourls_update_option( 'barre_log_login_log_failure', $barre_log_login_log_failure );
        
        $barre_log_login_log_logoff = $_POST['barre_log_login_log_logoff'];
        if( !$barre_log_login_log_logoff == 'True') $barre_log_login_log_logoff = 'False';
        yourls_update_option( 'barre_log_login_log_logoff', $barre_log_login_log_logoff );

        $barre_log_login_log_file = $_POST['barre_log_login_log_success'];
        
        if( !is_writable( $barre_log_login_log_file) ) {
                echo '<p>' . yourls_e( 'WARNING: The file "' ) . $barre_log_login_log_file . yourls_e( '" is not writeable') . '</p';
                $barre_log_login_log_file = "";
        }
                
        yourls_update_option( 'barre_log_login_log_file', $barre_log_login_log_file );
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
        // Create a singleton log class
	$barre_login_log_file = Log::singleton('file', yourls_get_option( 'barre_log_login_log_file' ), 'BARRE_LOG_LOGIN_LOG', $barre_login_log_conf);
        //log to the file
	$barre_login_log_file->log( $_SERVER['REMOTE_ADDR'] . " -\t" . $barre_log_login_result );
	
}

//Log the successful logins
function barre_log_login_success() {
	barre_log_login_log2file( 'SUCCESS' );
	
}

//log the failed logins
function barre_log_login_failure() {
	barre_log_login_log2file( 'FAIL' );
}

//log the logoffs
function barre_log_login_logoff() {
        barre_log_login_log2file( 'LOGOFF' )
}
