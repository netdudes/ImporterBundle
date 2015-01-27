<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter\Error\Handler;

use Netdudes\ImporterBundle\Importer\Interpreter\Exception\InterpreterException;

interface InterpreterErrorHandlerInterface
{
    public function handle(InterpreterException $exception, $index, $rowData);
}
