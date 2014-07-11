<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter\Exception;

class LookupFieldException extends InterpreterException
{
    protected $fieldConfiguration;

    protected $value;

    /**
     * @param mixed $fieldConfiguration
     */
    public function setFieldConfiguration($fieldConfiguration)
    {
        $this->fieldConfiguration = $fieldConfiguration;
    }

    /**
     * @return mixed
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
