<?php
/**
 * @file Bootstrapping File for Test Suite
 */

use Composer\Autoload\ClassLoader;

$loader_path = __DIR__ . '/../vendor/autoload.php';
/* @var $loader ClassLoader */
$loader = include $loader_path;
$loader->add('', __DIR__);

