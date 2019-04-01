<?php

namespace Core\Helpers;

use \Core\Interfaces\FormField;

class FieldSet implements \Countable, \Iterator
{
    private $fields = [];

    /**
     * @param $name
     * @return FormField
     * @throws \Exception
     */
    public function __get($name) : FormField
    {
        if (!array_key_exists($name, $this->fields))
            throw new \Exception("Field set not contain field with name {$name}");

        return $this->fields[$name];
    }

    /**
     * @param $name
     * @param $value FormField
     * @throws \Exception
     */
    public function __set($name, FormField $value)
    {
        if (!($value instanceof FormField))
            throw new \Exception('Field set accepts only FormField implemented class');

        $this->fields[$name] = $value;
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return count($this->fields);
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return current($this->fields);
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        next($this->fields);
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return key($this->fields);
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return current($this->fields);
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        reset($this->fields);
    }
}
