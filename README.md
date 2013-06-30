log-login
=========
This plugin logs login atempts to [YOURLS](http://yourls.org).
I wrote this to be used with fail2ban.

Configure the plugin by adding setting in your user/config.php

    define( 'BARRE_LOG_LOGIN_FILENAME' ) = '/path/to/filename.log';

BARRE_LOG_LOGIN_FILENAME defines the path to where the the plugin
should log authentication activities. Default value if not set is
plugins/log-login/logins.log

    define( 'BARRE_LOG_LOGIN_SUCCESS' ) = false;

BARRE_LOG_LOGIN_SUCCESS (true or false) determins if successful logins
should be logged or not. Default if not set is false

    define( 'BARRE_LOG_LOGIN_FAILURE' ) = true;

BARRE_LOG_LOGIN_FAILURE (true or false) determins if failed logins
should be logged or not. Default if not set is true

    define( 'BARRE_LOG_LOGIN_LOGOFF' ) = false;

BARRE_LOG_LOGIN_FALSE (true or false) determins if logoffs should be
logged or not. default if not set is false


BARRE_LOG_LOGIN_SUCCESS (true or false) determins if successful logins
should be logged or not.

This file needs the [PHP Pear log package](http://pear.php.net/package/Log/)

    pear install Log-1.12.7

fail2ban
--------
This is an example for a jail defenition for this plugin
change the path so it matches your settings in the log_login_settings.php
*/etc/fail2ban/jail.local*

    [yourls]
    enabled = true
    port    = http,https
    filter  = yourls
    logpath = /path/to/yourls-auth.log
    maxretry = 3
    findtime = 120

and create the filer for the jail.
*/etc/fail2ban/filter/yourls.conf*

    [Definition]
    failregex = .*- <HOST> -.*LOGIN FAILURE.*
    ignoreregexp =




logrotate
---------
An example on a logrotate rule for the log.

    /path/to/yourls-auth.log {
            weekly
            missingok
            rotate 52
            compress
            delaycompress
            notifempty
            create 640 www-data adm
    }
