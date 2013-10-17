<?php

function includeIfExists($file)
{
    if (file_exists($file)) {
        return include $file;
    }
}

if ((!$loader = includeIfExists(__DIR__.'/../vendor/autoload.php')) && (!$loader = includeIfExists(__DIR__.'/../../../autoload.php'))) {
    die('You must set up the project dependencies, run the following commands:'.PHP_EOL.
        'make composer_get'.PHP_EOL.
        'make composer_install'.PHP_EOL);
}
$loader->add('Ale\Tests\\', __DIR__);
