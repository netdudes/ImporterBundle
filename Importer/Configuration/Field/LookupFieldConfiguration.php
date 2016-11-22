<?php
namespace Netdudes\ImporterBundle\Importer\Configuration\Field;

class LookupFieldConfiguration extends AbstractFieldConfiguration implements FieldConfigurationInterface
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @var string 
     */
    protected $lookupField;

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
     * @return string
     */
    public function getLookupField()
    {
        return $this->lookupField;
    }

    /**
     * @param string $lookupField
     */
    public function setLookupField($lookupField)
    {
        $this->lookupField = $lookupField;
    }
}
