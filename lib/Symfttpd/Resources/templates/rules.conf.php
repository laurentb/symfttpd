server.document-root = "<?php echo $this->configuration->get('document_root') ?>"

url.rewrite-once = (
<?php foreach ($this->configuration->get('dirs') as $name): ?>
  "^/<?php echo preg_quote($name) ?>/.+" => "$0",
<?php endforeach ?>

<?php foreach ($this->configuration->get('files') as $name): ?>
  "^/<?php echo preg_quote($name) ?>$" => "$0",
<?php endforeach ?>

<?php foreach ($this->configuration->get('phps') as $name): ?>
  "^/<?php echo preg_quote($name) ?>(/[^\?]*)?(\?.*)?" => "/<?php echo $name ?>$1$2",
<?php endforeach ?>

  "^(/[^\?]*)(\?.*)?" => "/<?php echo $this->configuration->get('default') ?>.php$1$2"
)

<?php foreach ($this->configuration->get('nophp') as $name): ?>
<?php if (in_array($name, $this->configuration->get('dirs'))): ?>
$HTTP["url"] =~ "^/<?php echo $name ?>/" {
  url.access-deny = (".php")
}
<?php endif ?>
<?php endforeach ?>