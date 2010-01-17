#!/usr/bin/env php
<?php
/**
 * @author Laurent Bachelier <laurent@bachelier.name>
 */

error_reporting(E_ALL|E_STRICT);

$options = array_merge(
  array('default'=>'index', 'allow'=>false),
  getopt('', array('default:', 'only', 'allow:'))
);
$options['only'] = isset($options['only']);
$options['allow'] = $options['allow']
                  ? explode(',', $options['allow'])
                  : array();

// Not using __FILE__ since it resolves symlinks
$path = realpath(dirname($argv[0]).'/../web');
$files = array('dir'=>array(), 'php'=>array(), 'file'=>array());
foreach (new DirectoryIterator($path) as $file)
{
  $name = $file->getFilename();
  if ($name[0] != '.')
  {
    if ($file->isDir())
    {
      $files['dir'][] = $name;
    }
    elseif (!preg_match('/\.php$/', $name))
    {
      $files['file'][] = $name;
    }
    elseif (empty($options['only']))
    {
        $files['php'][] = $name;
    }
  }
}
foreach ($options['allow'] as $name)
{
  $files['php'][] = $name.'.php';
}
?>
server.document-root = "<?php echo $path ?>"

url.rewrite-once = (
<?php foreach ($files['dir'] as $name): ?>
  "^/<?php echo preg_quote($name) ?>/.+" => "$0",
<?php endforeach ?>

<?php foreach ($files['file'] as $name): ?>
  "^/<?php echo preg_quote($name) ?>$" => "$0",
<?php endforeach ?>

<?php foreach ($files['php'] as $name): ?>
  "^/<?php echo preg_quote($name) ?>(/[^\?]*)?(\?.*)?" => "/<?php echo $name ?>$1$2",
<?php endforeach ?>

  "^(/[^\?]*)(\?.*)?" => "/<?php echo $options['default'] ?>.php$1$2"
)
