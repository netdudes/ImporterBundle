<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter\Exception;

use Exception;
use Symfony\Component\Validator\ConstraintViolationList;

class InvalidEntityException extends InterpreterException
{
    /**
     * @var \Symfony\Component\Validator\ConstraintViolationList
     */
    private $violations;

    public function __construct(ConstraintViolationList $violations, $message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->violations = $violations;
    }

    /**
     * @return \Symfony\Component\Validator\ConstraintViolationList
     */
    public function getViolations()
    {
        return $this->violations;
    }


}