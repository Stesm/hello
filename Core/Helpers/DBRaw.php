<?php

namespace Core\Helpers;

/**
 * Class DBRaw
 * @package Core\Helpers
 */
class DBRaw
{
    private $sql_string = '';

    /**
     * DBRaw constructor.
     * @param $sql_string
     */
    public function __construct($sql_string)
    {
        $this->sql_string = $sql_string;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->sql_string;
    }
}
