<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;

interface InterpreterInterface
{
    public function interpret($data, $associative = true);
}