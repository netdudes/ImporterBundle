<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter\Field;

use Netdudes\ImporterBundle\Importer\Interpreter\Exception\InterpreterException;

class FieldError
{
    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var InterpreterException
     */
    private $exception;

    /**
     * @param InterpreterException $exception
     * @param string               $fieldName
     */
    public function __construct(InterpreterException $exception, $fieldName)
    {
        $this->exception = $exception;
        $this->fieldName = $fieldName;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @return InterpreterException
     */
    public function getException()
    {
        return $this->exception;
    }
}
