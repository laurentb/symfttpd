<?php

error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', true);
set_time_limit(0);

/**
 * Display a message on the standard output
 * @param string $message
 * @param boolean $error Treat the message as an error.
 *
 * @author Laurent Bachelier <laurent@bachelier.name>
 */
function log_message($message, $error = false)
{
  if ($error)
  {
    file_put_contents('php://stderr', $message."\n");
  }
  else
  {
    echo $message."\n";
  }
}

/* Needed for some weird reason; PHP will not display anything
 * when there is an uncatched exception. */
function handle_exception($e)
{
  log_message('Fatal error:', true);
  log_message($e, true);
  exit(1);
}
set_exception_handler('handle_exception');
