<?php
namespace Core\Interfaces;

/**
 * Interface FormField
 * @package Core\Interfaces
 */
interface FormField
{
    /**
     * FormField constructor.
     * @param $label
     * @param $name
     * @param string $value
     */
    public function __construct(string $name, $value = null, string $label = null);

    /**
     * @return mixed
     */
    public function render();

    /**
     * @param array ...$params
     * @return mixed
     */
    public function setAttribute(...$params);

    /**
     * @return mixed
     */
    public function getSubmitted();

    /**
     * @param $value
     * @return mixed
     */
    public function setValue($value);

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @return mixed
     */
    public function getName();

    /**
     * @return mixed
     */
    public function getLabel();

    /**
     * @param string $error
     * @return mixed
     */
    public function setError(string $error);

    /**
     * @return null|string
     */
    public function getError() : ?string;
}
