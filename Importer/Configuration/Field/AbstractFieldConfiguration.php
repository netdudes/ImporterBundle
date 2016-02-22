<?php

namespace Netdudes\ImporterBundle\Importer\Configuration\Field;

abstract class AbstractFieldConfiguration implements FieldConfigurationInterface
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @var string
     */
    protected $help;

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param string $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @return string
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
