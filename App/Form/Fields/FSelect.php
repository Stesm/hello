<?php
namespace App\Form\Fields;

use Core\Core;
use Core\Prototypes\FormField;

class FSelect extends FormField
{
    /** @var string $template */
    protected $template = 'select';

    /** @var array $list */
    protected $list = [];

    /**
     * @param array $values
     * @return FSelect
     */
    public function setListValues(array $values = [])
    {
        $values = array_map(function ($item) {
            return (string) $item;
        }, $values);

        $this->list = $values;

        return $this;
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
            'value' => $this->value,
            'list' => $this->list,
            'error' => $this->error
        ]);
    }

    public function getValue()
    {
        if (is_numeric($this->value)) {
            if (preg_match('/[\.,]/',(string) $this->value ))
                $value = (float) $this->value;
            else
                $value = (int) $this->value;
        } else
            $value = $this->value;

        return $value;
    }
}
