<?php
namespace Netdudes\ImporterBundle\Importer\Interpreter\Field;

use Netdudes\ImporterBundle\Importer\Configuration\Field\DateTimeFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\InvalidValueException;

class DatetimeFieldInterpreter implements FieldInterpreterInterface
{
    /**
     * @param FieldConfigurationInterface $configuration
     * @param mixed                       $value
     *
     * @throws InvalidValueException
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

        $exception = new InvalidValueException($value);
        $exception->setExpectedFormat($configuration->getFormat());
        throw $exception;
    }
}
