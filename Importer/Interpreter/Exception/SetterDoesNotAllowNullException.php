<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter\Exception;

use Exception;

class SetterDoesNotAllowNullException extends InterpreterException
{
    /**
     * @var string
     */
    private $entity;

    /**
     * @var int
     */
    private $property;

    public function __construct($entity, $property, $message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->entity = $entity;
        $this->property = $property;
    }

    /**
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return int
     */
    public function getProperty()
    {
        return $this->property;
    }
}