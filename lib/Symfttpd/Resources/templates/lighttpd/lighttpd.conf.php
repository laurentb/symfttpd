server.modules = (
    "mod_rewrite",
    "mod_access",
    "mod_accesslog",
    "mod_setenv",
    "mod_fastcgi",
)

server.document-root  = "<?php echo $this->options->get('document_root') ?>"
server.port           = <?php echo $this->options->get('port') ?>

<?php if ($this->options->has('bind')): ?>
server.bind           = "<?php echo $this->options->get('bind') ?>"
<?php endif ?>

fastcgi.server = ( ".php" =>
  ( "localhost" =>
    (
      "socket" => "<?php echo sys_get_temp_dir() ?>/symfttpd-php-" + PID + ".socket",
      "bin-path" => "<?php echo $configuration->get('php_cgi_cmd') ?> -d error_log=/dev/stderr'",
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

setenv.add-response-header = ( "X-Symfttpd" => "1",
    "Expires" => "Sun, 17 Mar 1985 00:42:00 GMT" )

include "<?php echo __DIR__.'/mime-types.conf' ?>"
server.indexfiles     = ("index.php", "index.html",
                        "index.htm", "default.htm")
server.follow-symlink = "enable"
static-file.exclude-extensions = (".php")

# http://redmine.lighttpd.net/issues/406
server.force-lowercase-filenames = "disable"

server.pid-file       = "<?php echo $this->options->get('pidfile') ?>"

server.errorlog       = "<?php echo $this->options->get('log_dir') ?>/error.log"
accesslog.filename    = "<?php echo $this->options->get('log_dir') ?>/access.log"

debug.log-file-not-found = "enable"
debug.log-request-header-on-error = "enable"

<?php if (null !== $this->rules): ?>
include "<?php echo $this->getRulesFile() ?>"
<?php endif ?>