<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter\Field;

use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationInterface;

class LiteralFieldInterpreter implements FieldInterpreterInterface
{
    public function interpret(FieldConfigurationInterface $configuration, $value)
    {
        return $value;
    }
}