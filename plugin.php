<?php
/*
Plugin Name: Log Login
Plugin URI: https://github.com/SweBarre/log-login
Description: Plugin to log logon attempts to YOURLS. Refer to <a href="https://github.com/SweBarre/log-login">my page</a> for more details.
Version: 0.3
Author: Jonas Forsberg
Author URI: http://gargamel.nu/
*/

// No direct call
if( !defined( 'YOURLS_ABSPATH' ) ) die();


// Load the Log facility
require_once('Log.php');

/* Add default settings

The following definitions can be overrided if they are defined in
the user/config.php
*/


if( !defined( 'BARRE_LOG_LOGIN_SUCCESS' )) define( 'BARRE_LOG_LOGIN_SUCCESS', false );
if( !defined( 'BARRE_LOG_LOGIN_FAILURE' )) define( 'BARRE_LOG_LOGIN_FAILURE', true );
if( !defined( 'BARRE_LOG_LOGIN_LOGOFF' )) define ( 'BARRE_LOG_LOGIN_LOGOFF', false);
if( !defined( 'BARRE_LOG_LOGIN_FILENAME' )) define ( 'BARRE_LOG_LOGIN_FILENAME', dirname( __FILE__) . DIRECTORY_SEPARATOR . 'logins.log');


//register the action functions
if( BARRE_LOG_LOGIN_SUCCESS ) yourls_add_action( 'login', 'barre_log_login_success' );
if( BARRE_LOG_LOGIN_FAILURE ) yourls_add_action( 'login_failed', 'barre_log_login_failure' );
if( BARRE_LOG_LOGIN_LOGOFF ) yourls_add_action( 'logout', 'barre_log_login_logoff' );



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
		if( file_exists( BARRE_LOG_LOGIN_FILENAME )) {
			//The file exists but not writeable, let's log an error and return from function
	                $message = 'The logfile is not writable: ' . BARRE_LOG_LOGIN_FILENAME;
        	        error_log( $message );
			return;
		}
		// The file doesn't exist, let check if the folder is writeable
		if ( is_writeable( dirname( BARRE_LOG_LOGIN_FILENAME ))) {
			// lets create the logfile
			touch( BARRE_LOG_LOGIN_FILENAME );
		} else {
			//The logfile doesn't exist and the folder is not writeable
			// Let's log an error and return from function
                        $message = 'The folder for the logfile destination is not writable: ' . dirname( BARRE_LOG_LOGIN_FILENAME );
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
	$barre_login_log_file = Log::singleton('file', BARRE_LOG_LOGIN_FILENAME, 'BARRE_LOG_LOGIN_LOG', $barre_login_log_conf);
	//log to the file
	$barre_login_log_file->log( $_SERVER['REMOTE_ADDR'] . " -\t" . $barre_log_login_result );
}

//Log the successful logins
function barre_log_login_success() {
	//only log successful logins if the cookie isn't set
	if( !yourls_check_auth_cookie()) barre_log_login_log2file( 'LOGIN SUCCESS ' . YOURLS_USER );
	
}

//log the failed logins
function barre_log_login_failure() {
	if( null !== 'username' ) barre_log_login_log2file( 'LOGIN FAILURE '. $_REQUEST['username'] );
}

//log the logoffs
function barre_log_login_logoff() {
        barre_log_login_log2file( 'LOGOFF' );
}
