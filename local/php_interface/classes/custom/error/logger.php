<?php

namespace Custom\Error;


trait Logger
{
    public static function logToFile(string $file, $data)
    {
        $absolutePath = $_SERVER['DOCUMENT_ROOT'] . $file;
        if (!file_exists(dirname($absolutePath))) {
            static::createFileDir($absolutePath);
        }
        file_put_contents($absolutePath, date('d.m.Y H:i:s ') . ': ' . print_r($data, true) . "\n", FILE_APPEND);
    }

    public static function createFileDir(string $filePath)
    {
        $explodedPath = explode('/', $filePath);
        $count = count($explodedPath);

        $path = '';
        for ($i = 0; $i < $count - 1; $i++) {
            $path .= $explodedPath[$i] . '/';
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
        }
    }
}
