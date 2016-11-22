<?php
namespace Netdudes\ImporterBundle\Importer\Interpreter\Exception;

use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationInterface;

class LookupFieldException extends InterpreterException
{
    /**
     * @var FieldConfigurationInterface
     */
    protected $fieldConfiguration;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param FieldConfigurationInterface $fieldConfiguration
     */
    public function setFieldConfiguration($fieldConfiguration)
    {
        $this->fieldConfiguration = $fieldConfiguration;
    }

    /**
     * @return FieldConfigurationInterface
     */
    public function getFieldConfiguration()
    {
        return $this->fieldConfiguration;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
