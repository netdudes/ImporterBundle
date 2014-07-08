<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter\Field;

use Netdudes\ImporterBundle\Importer\Configuration\Field\DateTimeFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationInterface;

class DatetimeFieldInterpreter implements FieldInterpreterInterface
{

    public function interpret(FieldConfigurationInterface $configuration, $value)
    {
        if (!($configuration instanceof DateTimeFieldConfiguration)) {
            throw new \InvalidArgumentException();
        }

        return \DateTime::createFromFormat($configuration->getFormat(), $value);
    }
}