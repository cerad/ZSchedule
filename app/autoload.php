<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = require __DIR__.'/../vendor/autoload.php';

$loader->add('Cerad',   __DIR__  . '/../../cerad/src');

// Keep for now as an exmple
//$loader->add(null, __DIR__ . '/../vendor/CodePlex/PHPExcel');

// intl
if (!function_exists('intl_get_error_code')) {
    require_once __DIR__.'/../vendor/symfony/symfony/src/Symfony/Component/Locale/Resources/stubs/functions.php';
}

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;
