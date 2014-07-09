<?php

namespace Netdudes\ImporterBundle\Importer\Configuration;

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

    public function getField($name)
    {
        return $this->fields[$name];
    }

    public function setClass($class)
    {
        $this->class = $class;
    }

    public function getFieldNames()
    {
        return array_keys($this->fields);
    }
}
