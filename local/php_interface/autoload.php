<?php

spl_autoload_register(function ($class) {
    $libPath = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/';
    $pathFromNamespace = str_replace('\\', DIRECTORY_SEPARATOR, strtolower($class)) . '.php';
    include_once $libPath . $pathFromNamespace;
});
