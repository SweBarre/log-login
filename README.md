log-login
=========
This plugin logs login atempts to [YOURLS](http://yourls.org).
I wrote this to be used with fail2ban.

update the file log__login__settings.php for local settings.

fail2ban
--------
This is an example for a jail defenition for this plugin
change the path so it matches your settings in the log__login__settings.php
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

