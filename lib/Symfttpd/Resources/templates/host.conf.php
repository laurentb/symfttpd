server.document-root = "<?php echo $this->get('document_root') ?>"

url.rewrite-once = (
<?php foreach ($this->get('dirs') as $name): ?>
  "^/<?php echo preg_quote($name) ?>/.+" => "$0",
<?php endforeach ?>

<?php foreach ($this->get('files') as $name): ?>
  "^/<?php echo preg_quote($name) ?>$" => "$0",
<?php endforeach ?>

<?php foreach ($this->get('phps') as $name): ?>
  "^/<?php echo preg_quote($name) ?>(/[^\?]*)?(\?.*)?" => "/<?php echo $name ?>$1$2",
<?php endforeach ?>

  "^(/[^\?]*)(\?.*)?" => "/<?php echo $this->get('default') ?>.php$1$2"
)

<?php foreach ($this->get('nophp') as $name): ?>
<?php if (in_array($name, $this->get('dirs'))): ?>
$HTTP["url"] =~ "^/<?php echo $name ?>/" {
  url.access-deny = (".php")
}
<?php endif ?>
<?php endforeach ?>