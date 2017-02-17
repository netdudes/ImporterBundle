<?php
namespace Netdudes\ImporterBundle\Importer\Interpreter\Field;

use Netdudes\ImporterBundle\Importer\Configuration\Field\BooleanFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\InvalidValueException;

class BooleanFieldInterpreter implements FieldInterpreterInterface
{
    /**
     * @var array
     */
    private $falseRepresentations = [
        'false',
        false,
        '0',
        0,
        'no'
    ];

    /**
     * @var array
     */
    private $trueRepresentations = [
        'true',
        true,
        '1',
        1,
        'yes'
    ];

    /**
     * @param FieldConfigurationInterface $configuration
     * @param mixed                       $value
     *
     * @throws InvalidValueException
     *
     * @return bool|null
     */
    public function interpret(FieldConfigurationInterface $configuration, $value)
    {
        if (!($configuration instanceof BooleanFieldConfiguration)) {
            throw new \InvalidArgumentException();
        }

        if (null === $value || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $value = strtolower($value);
        }

        if (in_array($value, $this->falseRepresentations, true)) {
            return false;
        }

        if (in_array($value, $this->trueRepresentations, true)) {
            return true;
        }

        $allowedValues = [
            'false',
            '0',
            'no',
            'true',
            '1',
            'yes'
        ];

        $exception = new InvalidValueException($value);
        $exception->setAllowedValues($allowedValues);
        throw $exception;
    }
}
