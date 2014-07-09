<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;

abstract class AbstractInterpreter
{
    abstract public function interpret($data, $associative = true);
}