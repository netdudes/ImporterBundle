<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Netdudes\ImporterBundle\Importer\Interpreter\Error\Handler\InterpreterErrorHandlerInterface;

interface InterpreterInterface
{
    /**
     * @param array $data
     * @param bool  $associative
     * 
     * @return object[]|null
     */
    public function interpret(array $data, $associative = true);

    /**
     * @param InterpreterErrorHandlerInterface $errorHandler
     */
    public function registerErrorHandler(InterpreterErrorHandlerInterface $errorHandler);
}
