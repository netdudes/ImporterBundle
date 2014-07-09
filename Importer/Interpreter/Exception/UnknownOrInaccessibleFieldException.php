<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter\Exception;

class UnknownOrInaccessibleFieldException extends \Exception
{
    protected $field;

    /**
     * @param mixed $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }
}
