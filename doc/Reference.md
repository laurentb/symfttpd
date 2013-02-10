#Reference

## Project configuration

```
// Project options

// Required options
$options['project_type'] = 'php'; // Can be php or symfony
$options['project_version'] = null; // null only if type is php

// Not required options
$options['project_readable_dirs'] = array('uploads'); // readable directories by the server in the web dir.
$options['project_readable_files'] = array('authors.txt', 'robots.txt'); // readable files by the server in the web dir (robots.txt).
$options['project_readable_phpfiles'] = array('index.php'); // executable php files in the web directory
$options['project_readable_restrict'] = true; // if true the server will restrict access to other files than readable php files.
$options['project_nophp'] = array('uploads'); // deny PHP execution in the specified directories.
$options['project_log_dir'] = 'log';
$options['project_cache_dir'] = 'cache';
$options['project_web_dir'] = 'web';
```

## Server configuration

```
// Server options

// Required options
$options['server_type'] = 'lighttpd'; // The server to use, can be lighttpd or nginx

// Not required options
$options['server_cmd'] = '/usr/bin/lighttpd'; // The command used to run the server
$options['server_pidfile'] = 'server_pidfile'; // The pidfile stores the PID of the server process.
$options['server_restartfile'] = 'server_restartfile'; // The file that tells the spawn command to restart the server.
$options['server_log_dir'] = '/var/log/';
$options['server_access_log'] = 'access_log';  // The server access log file of the server.
$options['server_error_log'] = 'error_log';   // The server error log file of the server.
```

## Gateway configuration

The gateway is the CGI interface used by the web server.

```
// Server options

// Required options
$options['gateway_type'] = 'php-fpm'; // The gateway the server will use, can be fastcgi or php-fpm

// Required options
$options['gateway_cmd'] = '/usr/bin/php-fpm'; // The command to run the gateway
$options['gateway_error_log'] = '/var/log/symfttpd-error.log'; // The error.log file used by the gateway
$options['gateway_pidfile'] = '/tmp/symfttpd-pidfile.pid'; // The pidfile of the gateway
$options['gateway_socket'] = '/tmp/symfttpd-socket.sock'; // The socket used by the gateway
```
