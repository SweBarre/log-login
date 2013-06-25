<?php
/*
Plugin Name: Log Login
Plugin URI: https://github.com/SweBarre/log-login
Description: Plugin to log logon attempts to YOURLS. Refer to <a href="https://github.com/SweBarre/log-login">my page</a> for more details.
Version: 0.1
Author: Jonas Forsberg
Author URI: http://gargamel.nu/
*/

// No direct call
if( !defined( 'YOURLS_ABSPATH' ) ) die();


// Load the Log facility
require_once('Log.php');

// Load the settings
include('log_login_settings.php');

/* Action login
 *
 * Write login success to the log
 * 
 */

if( $barre_log_login_do_log_success ) {
	yourls_add_action( 'login_succeded', 'barre_log_login_success' );
}
/* Action login_fail
 *
 * Write login failure to the log
 * 
 */
if ( $barre_log_login_do_log_failure ) {
	yourls_add_action( 'login_failed', 'barre_log_login_failure' );
}

function barre_log_login_log2file( $barre_log_login_result ) {
	include('log_login_settings.php');
	$barre_login_log_file = Log::singleton('file', $barre_log_login_log_file_name, 'BARRE_LOG_LOGIN_LOG', $barre_login_log_conf);
	$barre_login_log_file->log( $_SERVER['REMOTE_ADDR'] . " -\t" . $barre_log_login_result );
	
}

function barre_log_login_success() {
	barre_log_login_log2file('SUCCESS');
	
}

function barre_log_login_failure() {
	barre_log_login_log2file('FAIL');
}
