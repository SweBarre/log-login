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


if( defined( 'BARRE_LOG_LOGIN_SUCCESS' )) {
	if( BARRE_LOG_LOGIN_SUCCESS == true ) yourls_add_action( 'login', 'barre_log_login_success' );
}

/* Action login_fail
 *
 * Write login failure to the log
 * 
 */
if( defined( 'BARRE_LOG_LOGIN_FAILURE' )) {
	if(  BARRE_LOG_LOGIN_FAILURE == true ) yourls_add_action( 'login', 'barre_log_login_failure' );
}

/* Action login_fail
 *
 * Write login failure to the log
 * 
 */
if( defined( 'BARRE_LOG_LOGIN_LOGOFF' )) {
	if( BARRE_LOG_LOGIN_LOGOFF == true ) yourls_add_action( 'login', 'barre_log_login_logoff' );
}


// The actuall logging function
function barre_log_login_log2file( $barre_log_login_result ) {

	//check to see if the loffilename isn't set
	if( !defined( 'BARRE_LOG_LOGIN_FILENAME' )) {
		error_log( 'logfile name not configured' );
		return;
	}

        //Check to see if file doesn't exist OR if it's not writeble
        if( !is_writeable( BARRE_LOG_LOGIN_FILENAME  ) ) {
		// OK, something is wrong with the logfile
		// let's check if it exists and is not writeable
		if( file_exists( BARRE_LOG_LOGIN_LOGFILENAME )) {
			//The file exists but not writeable, let's log an error and return from function
	                $message = 'The logfile is not writable: ' . BARRE_LOG_LOGIN_LOGFILENAME;
        	        error_log( $message );
			return;
		}
		// The file doesn't exist, let check if the folder is writeable
		if ( is_writeable( dirname( BARRE_LOG_LOGIN_LOGFILENAME ))) {
			// lets create the logfile
			touch( BARRE_LOG_LOGIN_LOGFILENAME );
		} else {
			//The logfile doesn't exist and the folder is not writeable
			// Let's log an error and return from function
                        $message = 'The folder for the logfile destination is not writable: ' . dirname( BARRE_LOG_LOGIN_LOGFILENAME );
                        error_log( $message );
			return;
		}
        } 

        /* set the format of the log file
         * if you change this you probably have to change the
         * fail2ban rexexp in the yourls filter
         */
        $barre_login_log_conf = array(
                                        'lineFormat' => "%{timestamp}\t - %{message}",
                                        'timeFormat' => "%b %d %H:%M:%S"
                                        );

	// Create a singleton log class
	$barre_login_log_file = Log::singleton('file', BARRE_LOG_LOGIN_LOGFILE, 'BARRE_LOG_LOGIN_LOG', $barre_login_log_conf);
	//log to the file
	$barre_login_log_file->log( $_SERVER['REMOTE_ADDR'] . " -\t" . $barre_log_login_result );
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
