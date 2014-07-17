<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Netdudes\ImporterBundle\Importer\Interpreter\Error\Handler\InterpreterErrorHandlerInterface;

interface InterpreterInterface
{
    public function interpret($data, $associative = true);
    public function registerErrorHandler(InterpreterErrorHandlerInterface $errorHandler);
}
