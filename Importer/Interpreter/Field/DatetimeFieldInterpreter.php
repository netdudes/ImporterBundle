<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter\Field;

use Netdudes\ImporterBundle\Importer\Configuration\Field\DateTimeFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\DateTimeFormatException;

class DatetimeFieldInterpreter implements FieldInterpreterInterface
{

    public function interpret(FieldConfigurationInterface $configuration, $value)
    {
        if (!($configuration instanceof DateTimeFieldConfiguration)) {
            throw new \InvalidArgumentException();
        }

        if (empty(trim($value))) {
            return null;
        }

        $dateTime = \DateTime::createFromFormat($configuration->getFormat(), $value);

        if ($dateTime === false) {
            $errors = \DateTime::getLastErrors();
            $exception = new DateTimeFormatException();
            $exception->setValue($value);
            $exception->setFormat($configuration->getFormat());
            $exception->setDateTimeErrors($errors);
            throw $exception;
        }

        return $dateTime;
    }
}