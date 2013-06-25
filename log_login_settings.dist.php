<?/*
this is the settings file for the YOURLS plugin - log_login.

*/

// No direct call
if( !defined( 'YOURLS_ABSPATH' ) ) die();

// set som variables
$barre_log_login_log_do_log_success = false;
$barre_log_login_log_do_log_failure = true;

// the web user (www-data) needs write permission to folder  where the log is located
// or just create the log file and give write permission to that file
// for security reasons put the log outside the web root
//example: /var/log/yourls/yourls-auth.log
$barre_log_login_log_file_name = __DIR__ . DIRECTORY_SEPARATOR .'yourls-auth.log';

// Configuration settings for the log file
$barre_login_log_conf = array( 
				'lineFormat' => "%{timestamp}\t - %{message}",
				'timeFormat' => "%b %d %H:%M:%S"
			);
?>
