<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter\Exception;

use Exception;
use Symfony\Component\Validator\ConstraintViolationList;

class InvalidEntityException extends InterpreterException
{
    /**
     * @var ConstraintViolationList
     */
    private $violations;

    /**
     * @param ConstraintViolationList $violations
     * @param string                  $message
     * @param int                     $code
     * @param Exception|null          $previous
     */
    public function __construct(ConstraintViolationList $violations, $message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->violations = $violations;
    }

    /**
     * @return ConstraintViolationList
     */
    public function getViolations()
    {
        return $this->violations;
    }
}
