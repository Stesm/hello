<?php
namespace App\Form\Fields;

use Core\Prototypes\FormField;

class FInt extends FormField
{
    protected $template = 'string';

    public function getValue()
    {
        return (int) $this->value;
    }
}
