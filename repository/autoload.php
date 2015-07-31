<?php

use Phalcon\Loader;

$loader = new Loader();

$loader->registerNamespaces(array(
    'Repository\Src' => __DIR__ . '/src/',
    'Repository\Src\Common' => __DIR__ . '/src/common/',
    'Repository\Interfaces' => __DIR__ . '/interfaces/',
    'Repository\Abstracted' => __DIR__ . '/abstracted/',
));

$loader->register();