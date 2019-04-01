<?php

namespace Core\Helpers;

use Core\Core;
use Core\Interfaces\FormField;

/**
 * Class Form
 * @package Core\Prototypes
 */
class Form
{
    /** @var array */
    private $attributes = [
        'class' => 'form-horizontal'
    ];

    /**@var FieldSet */
    private $fields = null;

    /** @var string $method */
    private $method = 'get';

    /** @var string $action */
    private $action = '';

    /** @var string $target */
    private $target = '';

    /** @var array $errors */
    private $errors = [];

    /** @var array $actions */
    private $actions = [];

    /**
     * Form constructor.
     * @param string $method
     * @param string|null $action
     */
    public function __construct(string $method = 'get', string $action = null)
    {
        if (in_array($method, ['get', 'post']))
            $this->method = $method;

        if (!$action)
            $this->action = Core::$route->path;
        else
            $this->action = $action;

        $this->fields = new FieldSet;
    }

    /**
     * @param FormField $field
     */
    public function setField(FormField $field)
    {
        $this->fields->{$field->getName()} = $field;
    }

    /**
     * @param $field_name
     * @return FormField
     */
    public function getField($field_name) : FormField
    {
        return $this->fields->{$field_name};
    }

    /**
     * @return FieldSet
     */
    public function getFields() : FieldSet
    {
        return $this->fields;
    }

    /**
     * @return bool
     */
    public function submit() : bool
    {
        $request_data = strtolower($this->method) === 'post' ? $_POST : $_GET;

        if (count($request_data) || count($_FILES)) {
            $this->populate();

            $valid = true;

            foreach ($this->fields as $field)
                if (!$this->validate($field))
                    $valid = false;

            if ($valid)
                return $this->afterSubmit();

            return $valid;
        }

        return false;
    }

    private function populate()
    {
        foreach ($this->fields as $field) {
            parse_str($field->getName(), $name_params);
            $root_key = key($name_params);

            if (is_array($name_params[$root_key])) {
                $value = Core::$request->getInput($root_key, null);
                $name = str_replace($root_key, '', $field->getName());

                if (strpos($name, '[]') !== 0) {
                    $name_params = $name_params[$root_key];
                    do {
                        $root_key = key((array) $name_params);
                        $value = Arr::get($root_key,$value, $value);
                        $name_params = $name_params[$root_key];
                    } while (is_array($name_params));
                }
            } else {
                $value = Core::$request->getInput($field->getName(), null);
            }

            if (!is_null($value))
                $field->setValue($value);
        }
    }

    /**
     * stub
     *
     * @param FormField $field
     * @return bool
     */
    public function validate(FormField $field) : bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function afterSubmit() : bool
    {
        return true;
    }

    /**
     * @param string $error
     */
    public function setError(string $error) {
        $this->errors[] = $error;
    }

    /**
     * @return array
     */
    public function getErrors() : array
    {
        $errors = [];
        /** @var FormField $field */
        foreach ($this->fields as $field)
            $errors[] = $field->getError();

        return array_merge($this->errors, $errors);
    }

    /**
     * @return string
     */
    public function render()
    {
        return Core::view('admin/form', [
            'fields' => $this->fields,
            'actions' => $this->actions,
            'target' => $this->target,
            'errors' => $this->errors,
            'form' => $this
        ]);
    }

    /**
     * @param array $values
     * @return Form
     */
    public function loadValues(array $values) : self
    {
        foreach ($values as $name => $value) {
            try {
                $field = $this->getField($name);
                $field->setValue($value);
            } catch (\Throwable $e) {
                // No need actions, set only cross values
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getValuesArray() : array
    {
        $values = [];

        foreach ($this->fields as $field) {
            parse_str($field->getName().'='.urlencode($field->getValue()), $array_value);
            $values = array_merge_recursive($values, $array_value);
        }

        return $values;
    }

    /**
     * @param string $name
     * @param string $route
     * @param string $btn_class (default|danger|success|primary|warning)
     * @return $this
     * @throws \Exception
     */
    public function addAction(string $name, string $route = '', string $btn_class = '') : self
    {
        $this->actions[] = [
            'name' => $name,
            'uri' => $route ? Route::buildUri($route) : '',
            'btn-class' => $btn_class ? $btn_class : 'success'
        ];

        return $this;
    }

    /**
     * @return $this
     */
    public function cleanActions() : self
    {
        $this->actions = [];

        return $this;
    }

    /**
     * @return string
     */
    public function open() : string
    {
        $attrs = array_diff_key($this->attributes, ['method' => null, 'action' => null, 'enctype' => null,]);
        $attrs = array_map(function ($key, $value) {
            return $key.'="'.str_replace('"', '\\"',$value).'"';
        }, array_keys($attrs), array_values($attrs));
        $attrs = implode(' ', $attrs);

        return "<form {$attrs} method='{$this->method}' action='{$this->action}' enctype='multipart/form-data'>";
    }

    /**
     * @return string
     */
    public function close() : string
    {
        return "</form>";
    }

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }
}
