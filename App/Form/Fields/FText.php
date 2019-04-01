<?php
namespace App\Form\Fields;

use Core\Core;
use Core\Prototypes\FormField;

class FText extends FormField
{
    protected $template = 'text';

    protected $rows = 5;

    public function setRows(int $rows)
    {
        $this->rows = $rows < 0 ? 0 : ($rows > 20 ? 20 : $rows);
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
            'rows' => $this->rows,
            'error' => $this->error
        ]);
    }
}
