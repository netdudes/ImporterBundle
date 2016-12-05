<?php
namespace Netdudes\ImporterBundle\Importer\Interpreter\Field;

use Netdudes\ImporterBundle\Importer\Interpreter\Exception\InterpreterException;

class FieldError
{
    /**
     * @var string
     */
    private $fieldName = '';

    /**
     * @var InterpreterException
     */
    private $exception;

    /**
     * @param InterpreterException $exception
     */
    public function __construct(InterpreterException $exception)
    {
        $this->exception = $exception;
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

    /**
     * @param string $fieldName
     */
    public function setFieldName(string $fieldName)
    {
        $this->fieldName = $fieldName;
    }
}
