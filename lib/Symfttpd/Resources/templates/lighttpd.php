server.document-root = "<?php echo $parameters['path'] ?>"

url.rewrite-once = (
<?php foreach ($parameters['dir'] as $name): ?>
  "^/<?php echo preg_quote($name) ?>/.+" => "$0",
<?php endforeach ?>

<?php foreach ($parameters['file'] as $name): ?>
  "^/<?php echo preg_quote($name) ?>$" => "$0",
<?php endforeach ?>

<?php foreach ($parameters['php'] as $name): ?>
  "^/<?php echo preg_quote($name) ?>(/[^\?]*)?(\?.*)?" => "/<?php echo $name ?>$1$2",
<?php endforeach ?>

  "^(/[^\?]*)(\?.*)?" => "/<?php echo $parameters['default'] ?>.php$1$2"
)

<?php foreach ($parameters['nophp'] as $name): ?>
<?php if (in_array($name, $parameters['dir'])): ?>
$HTTP["url"] =~ "^/<?php echo $name ?>/" {
  url.access-deny = (".php")
}
<?php endif ?>
<?php endforeach ?>