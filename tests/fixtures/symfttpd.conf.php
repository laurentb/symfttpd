<?php
/**
 * Default configuration options
 *
 * Do not edit this file!
 * Use the user-level config $HOME/.symfttpd.conf.php
 * and the project-level one at $PROJECT/config/symfttpd.conf.php
 *
 * @author Laurent Bachelier <laurent@bachelier.name>
 */

/**
 * symfony paths
 * You should override this in the user-level config
 * @var array version => path
 */
$options['sf_path'] = array(
    '1.4' => __DIR__.'/Dev/symfony1',
    '2.0' => __DIR__.'/Dev/symfony2',
);