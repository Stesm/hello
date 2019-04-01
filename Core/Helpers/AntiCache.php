<?php
namespace Core\Helpers;

/**
 * Class AntiCache
 * @package Helpers
 */
class AntiCache {

    private function __construct(){}

    private static $base_path = null;

    public static function getFileTime(string $rel_path) : int
    {
        if(!self::$base_path)
            self::$base_path = PUBLIC_ROOT;

        $file_path = self::$base_path.'/'.$rel_path;

        return !is_file($file_path) ? 0 : filemtime($file_path);
    }
}
