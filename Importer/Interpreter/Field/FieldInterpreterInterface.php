<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter\Field;

use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationInterface;

interface FieldInterpreterInterface
{
    public function interpret(FieldConfigurationInterface $configuration, $value);
}