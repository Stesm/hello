<?php
namespace Core\Helpers;

/**
 * Class Request
 * @package Core\Helpers
 */
class Request
{
    /**
     * @param $name
     * @param string $default
     * @return mixed
     */
    public function getInput($name, $default = '')
    {
        if(array_key_exists($name, $_REQUEST))
            return $_REQUEST[$name];

        return $default;
    }

    /**
     * @param $filename
     * @param null $default
     * @return null
     */
    public function getFile($filename, $default = null)
    {
        if(array_key_exists($filename, $_FILES))
            return $_FILES[$filename];

        return $default;
    }
}
