<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter\Field;

use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\Field\FileFieldConfiguration;

class FileFieldInterpreter implements FieldInterpreterInterface
{

    public function interpret(FieldConfigurationInterface $configuration, $value)
    {
        if (!($configuration instanceof FileFieldConfiguration)) {
            throw new \InvalidArgumentException();
        }

        return file_get_contents($value);
    }
}