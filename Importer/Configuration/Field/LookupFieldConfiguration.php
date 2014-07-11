<?php

namespace Netdudes\ImporterBundle\Importer\Configuration\Field;

class LookupFieldConfiguration extends AbstractFieldConfiguration implements FieldConfigurationInterface
{
    protected $class;

    protected $lookupField;

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param mixed $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @return mixed
     */
    public function getLookupField()
    {
        return $this->lookupField;
    }

    /**
     * @param mixed $lookupField
     */
    public function setLookupField($lookupField)
    {
        $this->lookupField = $lookupField;
    }
}
