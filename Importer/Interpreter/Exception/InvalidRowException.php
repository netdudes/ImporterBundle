<?php
namespace Netdudes\ImporterBundle\Importer\Interpreter\Exception;

use Netdudes\ImporterBundle\Importer\Interpreter\Field\FieldError;

class InvalidRowException extends InterpreterException
{
    /**
     * @var FieldError[]
     */
    private $errors = [];

    /**
     * @param FieldError[] $errors
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    /**
     * @return FieldError[]
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
