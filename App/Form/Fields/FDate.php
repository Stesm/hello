<?php
namespace App\Form\Fields;

use Core\Prototypes\FormField;

class FDate extends FormField
{
    protected $template = 'date';

    public function getValue()
    {
        return (new \DateTime($this->value))->format('Y-m-d');
    }
}
