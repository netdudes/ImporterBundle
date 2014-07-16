<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter\Error\Handler;

interface InterpreterErrorHandlerInterface
{
    public function handle($exception, $index, $rowData);
}