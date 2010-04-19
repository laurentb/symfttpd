<?php
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', true);
function handle_exception($e)
{
  echo "Fatal error:\n";
  echo $e."\n";
  exit(1);
}
set_exception_handler('handle_exception');
