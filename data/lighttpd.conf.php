server.modules = (
    "mod_rewrite",
    "mod_access",
    "mod_accesslog",
    "mod_fastcgi"
)

server.port           = <?php echo $port ?>
#server.bind          = "127.0.0.1"

fastcgi.server = ( ".php" =>
  ( "localhost" =>
    (
      "socket" => "<?php echo sys_get_temp_dir() ?>/symfttpd-php-" + PID + ".socket",
      "bin-path" => "<?php echo $php_cgi_cmd ?> -d error_log=/dev/stderr'",
      "max-procs" => 1,
      "max-load-per-proc" => 1,
      "idle-timeout" => 120,
      "bin-environment" => (
        "PHP_FCGI_CHILDREN" => "3",
        "PHP_FCGI_MAX_REQUESTS" => "100",
        "IN_SYMFTTPD" => "1"
      )
    )
  )
)

include_shell "cat <?php echo escapeshellarg(dirname(__FILE__).'/mime-types.conf') ?>"
server.indexfiles     = ("index.php", "index.html",
                        "index.htm", "default.htm")
server.follow-symlink = "enable"
static-file.exclude-extensions = (".php")

# http://redmine.lighttpd.net/issues/406
server.force-lowercase-filenames = "disable"

server.pid-file       = "<?php echo $config_dir ?>/lighttpd.pid"

server.errorlog       = "<?php echo $log_dir ?>/error.log"
accesslog.filename    = "<?php echo $log_dir ?>/access.log"

debug.log-file-not-found = "enable"
debug.log-request-header-on-error = "enable"

include_shell "<?php echo $php_cmd ?> <?php echo escapeshellarg($project_path.'/'.$genconf) ?>"

