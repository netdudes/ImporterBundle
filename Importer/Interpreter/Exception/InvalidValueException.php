<?php
namespace Netdudes\ImporterBundle\Importer\Interpreter\Exception;

class InvalidValueException extends InterpreterException
{
    /**
     * @var string
     */
    private $actualValue;

    /**
     * @var array
     */
    private $allowedValues;

    /**
     * @var string
     */
    private $expectedFormat;

    /**
     * @param string $actualValue
     */
    public function __construct($actualValue)
    {
        $this->actualValue = $actualValue;
    }

    /**
     * @return string
     */
    public function getActualValue()
    {
        return $this->actualValue;
    }

    /**
     * @return string
     */
    public function getExpectedFormat()
    {
        return $this->expectedFormat;
    }

    /**
     * @return array
     */
    public function getAllowedValues()
    {
        return $this->allowedValues;
    }

    /**
     * @param array $allowedValues
     */
    public function setAllowedValues(array $allowedValues)
    {
        $this->allowedValues = $allowedValues;
    }

    /**
     * @param string $expectedFormat
     */
    public function setExpectedFormat($expectedFormat)
    {
        $this->expectedFormat = $expectedFormat;
    }
}
