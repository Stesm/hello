<?php

namespace Core\Helpers;

use Core\Prototypes\Model;

/**
 * Class Form
 * @package Core\Prototypes
 */
class CRUDForm extends Form
{
    /** @var null|Model */
    protected $model = null;

    /**
     * @return bool
     */
    public function afterSubmit() : bool
    {
        try {
            $id = $this->getField('id')->getValue();
            $id ?
                $this->model::update($id,$this->getValuesArray()) :
                $this->model::add($this->getValuesArray());
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return false;
        }

        return parent::afterSubmit();
    }
}
