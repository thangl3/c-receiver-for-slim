<?php
namespace LoveCoding\CReceiver;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class UploadHelper
{
	public static function removeFolder($path)
	{
        if (is_dir($path) === true) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                if (in_array($file->getBasename(), array('.', '..')) !== true) {
                    if ($file->isDir() === true) {
                        rmdir($file->getPathName());
                    }
                    else if (($file->isFile() === true) || ($file->isLink() === true)) {
                        unlink($file->getPathname());
                    }
                }
            }
            return rmdir($path);
        } else if ((is_file($path) === true) || (is_link($path) === true)) {
            return unlink($path);
        }
        return false;
    }

    public static function createMultipleFolder($path)
    {
        if( !is_dir($path) ) {
            @mkdir($path, 0775, true);
        }
    }

    public static function checkWritePermissionDirectory($dir) {
        if (!is_writable(dirname($dir))) {
            throw new RuntimeException('Cache\'s root directory must be writable');
        }
    }
}