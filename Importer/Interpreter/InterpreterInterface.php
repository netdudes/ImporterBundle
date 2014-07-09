<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter;

interface InterpreterInterface
{
    public function interpret($data, $associative = true);
}
