<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter\Exception;

class UnknownColumnException extends InterpreterException
{
    /**
     * @var string
     */
    private $column;

    /**
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @param string $column
     */
    public function setColumn($column)
    {
        $this->column = $column;
    }
}
