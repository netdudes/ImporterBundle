<?php

namespace Netdudes\ImporterBundle\Importer\Configuration\Exception;

use Netdudes\ImporterBundle\Importer\Interpreter\Exception\InterpreterException;

class UnknownFieldException extends \Exception
{
    private $field;

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param mixed $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }
}