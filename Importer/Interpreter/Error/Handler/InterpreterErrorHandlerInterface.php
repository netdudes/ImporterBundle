<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter\Error\Handler;

use Netdudes\ImporterBundle\Importer\Interpreter\Exception\InterpreterException;

interface InterpreterErrorHandlerInterface
{
    /**
     * @param InterpreterException $exception
     * @param int                  $index
     * @param array                $rowData
     */
    public function handle(InterpreterException $exception, $index, $rowData);
}
