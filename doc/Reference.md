#Reference

## Project configuration

```
<?php
// Project options
// Required options
$options['project_type'] = 'php'; // Can be php or symfony
$options['project_version'] = null; // null only if type is php

// Not required options
$options['project_readable_dirs'] = array('uploads'); // readable directories by the server in the web dir.
$options['project_readable_files'] = array('authors.txt', 'robots.txt'); // readable files by the server in the web dir (robots.txt).
$options['project_readable_phpfiles'] = array('index.php'); // executable php files in the web directory
$options['project_readable_restrict'] = true; // if true the server will restrict access to other files than readable php files.

```