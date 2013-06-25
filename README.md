log-login
=========
This plugin logs login atempts to [YOURLS](http://yourls.org).
I wrote this to be used with fail2ban.

Before you enable the plugin make sure you copy or rename 
the log_login_settings.php.dist to log_login_settings.php and 
update the file log_login_settings.php for reflect your local settings.

This file needs the [PHP Pear log package](http://pear.php.net/package/Log/)
To install in debian wheezy, just do a 

    apt-get install php-log

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
    failregex = .*- <HOST> -.*FAIL.*
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

