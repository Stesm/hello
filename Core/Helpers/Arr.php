<?php

namespace Core\Helpers;

class Arr
{
    /**
     * @param string $key
     * @param array $array
     * @param null $default
     * @return mixed|null
     */
    public static function get(string $key, array $array, $default = null)
    {
        if (array_key_exists($key,$array))
            return $array[$key];

        return $default;
    }
}
