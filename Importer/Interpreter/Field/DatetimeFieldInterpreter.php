<?php
namespace Netdudes\ImporterBundle\Importer\Interpreter\Field;

use Netdudes\ImporterBundle\Importer\Configuration\Field\DateTimeFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\DateTimeFormatException;

class DatetimeFieldInterpreter implements FieldInterpreterInterface
{
    /**
     * @param FieldConfigurationInterface $configuration
     * @param mixed                       $value
     *
     * @throws DateTimeFormatException
     *
     * @return \DateTime|null
     */
    public function interpret(FieldConfigurationInterface $configuration, $value)
    {
        if (!($configuration instanceof DateTimeFieldConfiguration)) {
            throw new \InvalidArgumentException();
        }

        if (empty(trim($value))) {
            return null;
        }

        $dateTime = \DateTime::createFromFormat($configuration->getFormat(), $value);

        if ($dateTime !== false) {
            return $dateTime;
        }

        $dateTime = \DateTime::createFromFormat('Y-m-d', $value);
        if ($dateTime !== false) {
            $dateTime->setTime(0, 0, 0);

            return $dateTime;
        }

        $errors = \DateTime::getLastErrors();
        $exception = new DateTimeFormatException();
        $exception->setValue($value);
        $exception->setFormat($configuration->getFormat());
        $exception->setDateTimeErrors($errors);
        throw $exception;
    }
}
