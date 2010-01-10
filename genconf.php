#!/usr/bin/env php
<?php
error_reporting(E_ALL|E_STRICT);

/**
 * @author Laurent Bachelier <laurent@bachelier.name>
 * @license MIT
 */

// Not using __FILE__ since it resolves symlinks
$path = realpath(dirname($argv[0]).'/../web');
$files = array('dir'=>array(), 'php'=>array(), 'file'=>array());
$default = 'index.php';
foreach (new DirectoryIterator($path) as $file)
{
  $name = $file->getFilename();
  if ($name[0] != '.')
  {
    if ($file->isDir())
    {
      $files['dir'][] = $name;
    }
    elseif (preg_match('/\.php$/', $file))
    {
      $files['php'][] = $name;
    }
    else
    {
      $files['file'][] = $name;
    }
  }
}
?>
url.rewrite-once = (
<?php foreach ($files['dir'] as $name): ?>
  "^/<?php echo preg_quote($name) ?>/.+" => "$0",
<?php endforeach ?>

<?php foreach ($files['file'] as $name): ?>
  "^/<?php echo preg_quote($name) ?>$" => "$0",
<?php endforeach ?>

<?php foreach ($files['php'] as $name): ?>
  "^/<?php echo preg_quote($name) ?>(/[^\?]*)?(\?.*)?" => "/<?php echo $name ?>$2$3",
<?php endforeach ?>

  "^(/[^\?]*)(\?.*)?" => "/<?php echo $default ?>/$2$3"
)
