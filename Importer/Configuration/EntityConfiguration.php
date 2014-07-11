<?php

namespace Netdudes\ImporterBundle\Importer\Configuration;

use Netdudes\ImporterBundle\Importer\Configuration\Exception\UnknownFieldException;

class EntityConfiguration implements EntityConfigurationInterface
{
    private $fields = [];

    private $class = null;

    public function getFields()
    {
        return $this->fields;
    }

    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setClass($class)
    {
        $this->class = $class;
    }

    public function getField($name)
    {
        if (array_key_exists($name, $this->fields)) {
            return $this->fields[$name];
        }

        $exception = new UnknownFieldException("Unknown field \"$name\"");
        $exception->setField($name);
        throw $exception;
    }

    public function getFieldNames()
    {
        return array_keys($this->fields);
    }
}
