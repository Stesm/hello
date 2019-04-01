<?php

namespace Core\Prototypes;

use Core\Core;
use Core\Interfaces\FormField as Proto;

/**
 * Class FormField
 * @package Core\Prototypes
 */
abstract class FormField implements Proto
{
    protected $value = null;
    protected $name = null;
    protected $label = null;
    protected $error = null;
    protected $template_prefix = 'admin/fields';
    protected $template = '';
    protected $attributes = [];

    /**
     * FormField constructor.
     * @param string $name
     * @param mixed $value
     * @param string $label
     */
    public function __construct(string $name, $value = null, string $label = '')
    {
        $this->label = $label;
        $this->name = $name;
        $this->value = $value ?? null;
    }

    public function render()
    {
        if(!$this->template){
            echo "No template for input: {$this->name}";
            return;
        }

        echo Core::view("{$this->template_prefix}/{$this->template}", [
            'label' => $this->label,
            'name' => $this->name,
            'value' => $this->getValue(),
            'error' => $this->error
        ]);
    }

    /**
     * @param array ...$params
     */
    public function setAttribute(...$params)
    {
        if(count($params) == 2){
            $this->attributes[] = [
                'name' => $params[0],
                'value' => $params[1]
            ];
        }elseif (count($params) == 1 && is_array($params[0])){
            if(count(array_intersect(['value', 'name'], array_keys($params[0]))) == 2)
                $this->attributes[] = $params[0];
        }
    }

    /**
     * @return mixed
     */
    public function getSubmitted()
    {
        return Core::$request->getInput($this->name);
    }

    /**
     * @param $value
     * @return mixed|void
     */
    public function setValue($value)
    {
        $this->value = $value ?? null;
    }

    /**
     * @return null|string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return null
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $error
     */
    public function setError(string $error)
    {
        $this->error = $error;
    }

    /**
     * @return string|null
     */
    public function getError() : ?string
    {
        return (string) $this->error;
    }
}
