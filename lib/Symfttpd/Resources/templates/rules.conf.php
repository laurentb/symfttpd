server.document-root = "<?php echo $this->options->get('document_root') ?>"

url.rewrite-once = (
<?php foreach ($this->options->get('dirs') as $name): ?>
  "^/<?php echo preg_quote($name) ?>/.+" => "$0",
<?php endforeach ?>

<?php foreach ($this->options->get('files') as $name): ?>
  "^/<?php echo preg_quote($name) ?>$" => "$0",
<?php endforeach ?>

<?php foreach ($this->options->get('phps') as $name): ?>
  "^/<?php echo preg_quote($name) ?>(/[^\?]*)?(\?.*)?" => "/<?php echo $name ?>$1$2",
<?php endforeach ?>

  "^(/[^\?]*)(\?.*)?" => "/<?php echo $this->options->get('default') ?>.php$1$2"
)

<?php foreach ($this->options->get('nophp') as $name): ?>
<?php if (in_array($name, $this->options->get('dirs'))): ?>
$HTTP["url"] =~ "^/<?php echo $name ?>/" {
  url.access-deny = (".php")
}
<?php endif ?>
<?php endforeach ?>