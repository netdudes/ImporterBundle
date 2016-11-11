<?php

namespace Netdudes\ImporterBundle\Importer\Configuration;

class EntityConfiguration implements EntityConfigurationInterface
{
    /**
     * @var array
     */
    private $fields = [];

    /**
     * @var null|string
     */
    private $class = null;

    /**
     * @var null|string
     */
    private $help = null;

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @param string $name
     *
     * @return string
     * @throws \Exception
     */
    public function getField($name)
    {
        if ($this->hasFieldName($name)) {
            return $this->fields[$name];
        }

        throw new \Exception("Unknown column $name found in the imported data.");
    }

    /**
     * @return array
     */
    public function getFieldNames()
    {
        return array_keys($this->fields);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasFieldName($name)
    {
        return array_key_exists($name, $this->fields);
    }

    /**
     * @return null|string
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * @param string $help
     */
    public function setHelp($help)
    {
        $this->help = $help;
    }
}
