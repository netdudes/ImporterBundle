<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter\Exception;

class UnknownColumnException extends InterpreterException
{
    private $column;

    /**
     * @return mixed
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @param mixed $column
     */
    public function setColumn($column)
    {
        $this->column = $column;
    }
}
